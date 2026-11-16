<?php

namespace App\Libraries;

/**
 * Autenticación persistente firmada con HMAC-SHA256.
 * Mantiene la sesión activa entre contenedores serverless hasta /logout.
 */
class PersistentAuth
{
    public const COOKIE_NAME         = 'materiax_auth';
    public const COOKIE_LIFETIME     = 34560000; // 400 días (máximo RFC 6265bis)
    public const RENEW_AFTER_SECONDS = 86400;

    private const SESSION_KEYS = [
        'user_id', 'nombre', 'email', 'cuit', 'telefono',
        'rubro', 'ciudad', 'provincia', 'direccion', 'rol', 'isLoggedIn',
    ];

    public static function persistUser(array $userData): void
    {
        $userId = (int) ($userData['user_id'] ?? $userData['id'] ?? 0);
        $email  = (string) ($userData['email'] ?? '');
        if ($userId <= 0 || $email === '') {
            return;
        }

        $payload = [
            'v'         => 1,
            'iat'       => time(),
            'user_id'   => $userId,
            'nombre'    => (string) ($userData['nombre'] ?? ''),
            'email'     => $email,
            'cuit'      => (string) ($userData['cuit'] ?? ''),
            'telefono'  => (string) ($userData['telefono'] ?? ''),
            'rubro'     => (string) ($userData['rubro'] ?? ''),
            'ciudad'    => (string) ($userData['ciudad'] ?? ''),
            'provincia' => (string) ($userData['provincia'] ?? ''),
            'direccion' => (string) ($userData['direccion'] ?? ''),
            'rol'       => (string) ($userData['rol'] ?: 'empresa'),
        ];

        self::sendCookie(self::encodeToken($payload), time() + self::COOKIE_LIFETIME);
    }

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

    public static function ensureSession(): bool
    {
        $session = session();
        if ($session->get('isLoggedIn') && (int) $session->get('user_id') > 0) {
            $existing = self::readValidPayload();
            if ($existing === null || ((int) ($existing['iat'] ?? 0) < (time() - self::RENEW_AFTER_SECONDS))) {
                self::syncFromSession();
            }
            return true;
        }

        if (($payload = self::readValidPayload()) === null) {
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

        if (((int) ($payload['iat'] ?? 0)) < (time() - self::RENEW_AFTER_SECONDS)) {
            self::persistUser($payload);
        }
        return true;
    }

    public static function readValidPayload(): ?array
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (!is_string($raw) || !str_contains($raw, '.')) {
            return null;
        }

        [$payloadB64, $sigB64] = explode('.', $raw, 2);
        if ($payloadB64 === '' || $sigB64 === '') {
            return null;
        }

        $expected = self::b64UrlEncode(hash_hmac('sha256', $payloadB64, self::getSecretKey(), true));
        if (!hash_equals($expected, $sigB64)) {
            return null;
        }

        $json = self::b64UrlDecode($payloadB64);
        $data = $json !== false ? json_decode($json, true) : null;
        return (is_array($data) && (int) ($data['user_id'] ?? 0) > 0 && !empty($data['email'])) ? $data : null;
    }

    public static function clear(): void
    {
        unset($_COOKIE[self::COOKIE_NAME]);
        self::sendCookie('', time() - 3600);
        $session = session();
        $session->remove(self::SESSION_KEYS);
        $session->destroy();
    }

    private static function encodeToken(array $payload): string
    {
        $body = self::b64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $body . '.' . self::b64UrlEncode(hash_hmac('sha256', $body, self::getSecretKey(), true));
    }

    private static function sendCookie(string $value, int $expires): void
    {
        if ($value !== '') {
            $_COOKIE[self::COOKIE_NAME] = $value;
        }
        if (is_cli() || headers_sent()) {
            return;
        }

        $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
            || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);

        setcookie(self::COOKIE_NAME, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function getSecretKey(): string
    {
        $val = static fn (string $k): string => (string) (getenv($k) ?: ($_ENV[$k] ?? $_SERVER[$k] ?? ''));
        return hash('sha256', implode('|', [
            $val('APP_AUTH_SECRET'),
            $val('encryption.key'),
            $val('POSTGRES_URL'),
            $val('SUPABASE_DB_URL'),
            $val('DATABASE_URL'),
            $val('TIDB_HOST'),
            'MateriaX_B2B_PermanentSession_Key_v2_9f4c8a1e7b2d',
        ]), true);
    }

    private static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string|false
    {
        $pad = strlen($data) % 4;
        if ($pad > 0) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
