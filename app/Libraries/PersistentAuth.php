<?php

namespace App\Libraries;

/**
 * Autenticación persistente firmada con HMAC-SHA256.
 *
 * Garantiza que, una vez iniciada la sesión, el usuario permanezca autenticado
 * indefinidamente (incluso tras reiniciar el navegador, limpiar archivos temporales
 * o balancear peticiones entre contenedores serverless como Vercel) hasta que
 * presione explícitamente "Cerrar Sesión" (/logout).
 */
class PersistentAuth
{
    public const COOKIE_NAME         = 'materiax_auth';
    public const COOKIE_LIFETIME     = 34560000; // 400 días (máximo permitido por RFC 6265bis / Chrome)
    public const RENEW_AFTER_SECONDS = 86400;    // Renovación deslizante automática cada 24 horas

    /**
     * Genera y envía la cookie persistente firmada a partir de los datos del usuario.
     */
    public static function persistUser(array $userData): void
    {
        $payload = [
            'v'         => 1,
            'iat'       => time(),
            'user_id'   => (int) ($userData['user_id'] ?? $userData['id'] ?? 0),
            'nombre'    => (string) ($userData['nombre'] ?? ''),
            'email'     => (string) ($userData['email'] ?? ''),
            'cuit'      => (string) ($userData['cuit'] ?? ''),
            'telefono'  => (string) ($userData['telefono'] ?? ''),
            'rubro'     => (string) ($userData['rubro'] ?? ''),
            'ciudad'    => (string) ($userData['ciudad'] ?? ''),
            'provincia' => (string) ($userData['provincia'] ?? ''),
            'direccion' => (string) ($userData['direccion'] ?? ''),
            'rol'       => (string) ($userData['rol'] ?? 'empresa'),
        ];

        if ($payload['user_id'] <= 0 || $payload['email'] === '') {
            return;
        }

        $token = self::encodeToken($payload);
        self::sendCookie($token, time() + self::COOKIE_LIFETIME);
    }

    /**
     * Sincroniza la cookie persistente con los datos actuales almacenados en la sesión PHP.
     */
    public static function syncFromSession(): void
    {
        $session = session();
        if (!$session->get('isLoggedIn') || (int) $session->get('user_id') <= 0) {
            return;
        }

        self::persistUser([
            'user_id'   => (int) $session->get('user_id'),
            'nombre'    => (string) $session->get('nombre'),
            'email'     => (string) $session->get('email'),
            'cuit'      => (string) $session->get('cuit'),
            'telefono'  => (string) $session->get('telefono'),
            'rubro'     => (string) $session->get('rubro'),
            'ciudad'    => (string) $session->get('ciudad'),
            'provincia' => (string) $session->get('provincia'),
            'direccion' => (string) $session->get('direccion'),
            'rol'       => (string) ($session->get('rol') ?: 'empresa'),
        ]);
    }

    /**
     * Asegura que la sesión esté activa:
     * - Si la sesión PHP ya está iniciada, garantiza que exista la cookie persistente y la renueva si corresponde.
     * - Si la sesión PHP se perdió (por ejemplo, otro contenedor serverless o limpieza de /tmp),
     *   la restaura de forma transparente a partir de la cookie firmada HMAC-SHA256.
     */
    public static function ensureSession(): bool
    {
        $session = session();

        if ($session->get('isLoggedIn') && (int) $session->get('user_id') > 0) {
            $existingPayload = self::readValidPayload();
            if ($existingPayload === null || ((int) ($existingPayload['iat'] ?? 0) < (time() - self::RENEW_AFTER_SECONDS))) {
                self::syncFromSession();
            }
            return true;
        }

        $payload = self::readValidPayload();
        if ($payload === null) {
            return false;
        }

        $session->set([
            'user_id'    => (int) $payload['user_id'],
            'nombre'     => (string) ($payload['nombre'] ?? ''),
            'email'      => (string) ($payload['email'] ?? ''),
            'cuit'       => (string) ($payload['cuit'] ?? ''),
            'telefono'   => (string) ($payload['telefono'] ?? ''),
            'rubro'      => (string) ($payload['rubro'] ?? ''),
            'ciudad'     => (string) ($payload['ciudad'] ?? ''),
            'provincia'  => (string) ($payload['provincia'] ?? ''),
            'direccion'  => (string) ($payload['direccion'] ?? ''),
            'rol'        => (string) ($payload['rol'] ?? 'empresa'),
            'isLoggedIn' => true,
        ]);

        // Renovación deslizante para mantener la cookie siempre vigente
        if (((int) ($payload['iat'] ?? 0)) < (time() - self::RENEW_AFTER_SECONDS)) {
            self::persistUser($payload);
        }

        return true;
    }

    /**
     * Lee y verifica criptográficamente el contenido de la cookie persistente sin tocar sesión ni BD.
     */
    public static function readValidPayload(): ?array
    {
        $rawToken = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (!is_string($rawToken) || $rawToken === '' || !str_contains($rawToken, '.')) {
            return null;
        }

        [$payloadB64, $sigB64] = explode('.', $rawToken, 2);
        if ($payloadB64 === '' || $sigB64 === '') {
            return null;
        }

        $expectedSig = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, self::getSecretKey(), true));
        if (!hash_equals($expectedSig, $sigB64)) {
            return null;
        }

        $json = self::base64UrlDecode($payloadB64);
        if ($json === false || $json === '') {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['user_id']) || (int) $data['user_id'] <= 0 || empty($data['email'])) {
            return null;
        }

        return $data;
    }

    /**
     * Elimina definitivamente la cookie persistente y destruye la sesión activa (solo al cerrar sesión).
     */
    public static function clear(): void
    {
        unset($_COOKIE[self::COOKIE_NAME]);
        self::sendCookie('', time() - 3600);

        $session = session();
        $session->remove([
            'user_id', 'nombre', 'email', 'cuit', 'telefono',
            'rubro', 'ciudad', 'provincia', 'direccion', 'rol', 'isLoggedIn',
        ]);
        $session->destroy();
    }

    private static function encodeToken(array $payload): string
    {
        $json       = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payloadB64 = self::base64UrlEncode((string) $json);
        $sigB64     = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, self::getSecretKey(), true));

        return $payloadB64 . '.' . $sigB64;
    }

    private static function sendCookie(string $value, int $expires): void
    {
        if ($value !== '') {
            $_COOKIE[self::COOKIE_NAME] = $value;
        }

        if (is_cli() || headers_sent()) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);

        setcookie(self::COOKIE_NAME, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function getSecretKey(): string
    {
        $resolve = static function (string $key): string {
            $v = getenv($key);
            if ($v !== false && $v !== '') {
                return (string) $v;
            }
            return (string) ($_ENV[$key] ?? $_SERVER[$key] ?? '');
        };

        $seed = implode('|', [
            $resolve('APP_AUTH_SECRET'),
            $resolve('encryption.key'),
            $resolve('POSTGRES_URL'),
            $resolve('SUPABASE_DB_URL'),
            $resolve('DATABASE_URL'),
            $resolve('TIDB_HOST'),
            'MateriaX_B2B_PermanentSession_Key_v2_9f4c8a1e7b2d',
        ]);

        return hash('sha256', $seed, true);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
