<?php

namespace App\Validation;

use App\Models\LocalidadModel;

/**
 * Reglas de validación de negocio y coherencia real para registro y perfil de empresas.
 */
class EmpresaRules
{
    private const ALLOWED_CUIT_PREFIXES = ['20', '23', '24', '27', '30', '33', '34'];

    private const BLOCKED_SEQUENCES = [
        '01234567', '12345678', '23456789', '87654321', '76543210',
        '11223344', '98765432', '00000000',
    ];

    private const BLOCKED_PHONE_SEQS = [
        '012345', '123456', '234567', '345678', '456789',
        '987654', '876543', '765432', '654321', '543210',
        '112233', '121212', '000000', '111111', '222222',
        '333333', '444444', '555555', '666666', '777777', '888888', '999999',
    ];

    public static function normalizeWhitespace(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    public static function formatCuit(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($digits) !== 11) {
            return trim($value);
        }
        return substr($digits, 0, 2) . '-' . substr($digits, 2, 8) . '-' . $digits[10];
    }

    public function valid_razon_social(mixed $value, ?string &$error = null): bool
    {
        $v = self::normalizeWhitespace((string) $value);
        $len = mb_strlen($v, 'UTF-8');
        if ($len < 3 || $len > 100) {
            $error = 'La razón social o nombre debe tener entre 3 y 100 caracteres.';
            return false;
        }
        if (preg_match('/https?:\/\/|www\.|@/i', $v)) {
            $error = 'La razón social no puede contener enlaces web ni correos electrónicos.';
            return false;
        }

        $letterCount = preg_match_all('/\p{L}/u', $v, $matches);
        if ($letterCount === false || $letterCount < 2) {
            $error = 'Ingresá una razón social o nombre comercial real con al menos 2 letras.';
            return false;
        }

        $compact = preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($v, 'UTF-8')) ?? '';
        if (preg_match('/(.)\1{3,}/u', $compact)) {
            $error = 'El nombre ingresado contiene caracteres repetidos sin sentido.';
            return false;
        }

        $lettersOnly = mb_strtolower(implode('', $matches[0]), 'UTF-8');
        if (count(array_unique(preg_split('//u', $lettersOnly, -1, PREG_SPLIT_NO_EMPTY) ?: [])) < 2) {
            $error = 'Ingresá una razón social válida (no repitas una misma letra).';
            return false;
        }

        return true;
    }

    public function valid_cuit(mixed $value, ?string &$error = null): bool
    {
        $raw = trim((string) $value);
        if ($raw === '' || !preg_match('/^[0-9.\-\s]+$/', $raw)) {
            $error = 'El CUIT/CUIL solo puede contener dígitos y guiones (ej: 30-71234567-9).';
            return false;
        }

        $d = preg_replace('/\D/', '', $raw) ?? '';
        if (strlen($d) !== 11) {
            $error = 'El CUIT/CUIL debe tener exactamente 11 dígitos.';
            return false;
        }

        $prefix = substr($d, 0, 2);
        if (!in_array($prefix, self::ALLOWED_CUIT_PREFIXES, true)) {
            $error = 'El prefijo del CUIT/CUIL debe iniciar con 20, 23, 24, 27, 30, 33 o 34.';
            return false;
        }

        $core = substr($d, 2, 8);
        if (preg_match('/^(\d)\1{7}$/', $core) || in_array($core, self::BLOCKED_SEQUENCES, true) || (int) $core < 1000000) {
            $error = 'El número de documento/sociedad dentro del CUIT no corresponde a un identificador válido.';
            return false;
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $d[$i] * $weights[$i];
        }
        $dv = 11 - ($sum % 11);
        if ($dv === 11) {
            $dv = 0;
        }

        if ($dv === 10 || $dv !== (int) $d[10]) {
            $error = 'El CUIT/CUIL no supera la verificación oficial (dígito verificador inválido).';
            return false;
        }

        return true;
    }

    public function valid_telefono(mixed $value, ?string &$error = null): bool
    {
        $raw = trim((string) $value);
        if ($raw === '' || !preg_match('/^\+?[0-9\-\s()]+$/', $raw)) {
            $error = 'El teléfono solo puede contener números, espacios, guiones, paréntesis o + inicial.';
            return false;
        }

        $digits = preg_replace('/\D/', '', $raw) ?? '';
        $isIntlNonAr = str_starts_with($raw, '+') && !str_starts_with($digits, '54');

        if ($isIntlNonAr) {
            if (strlen($digits) < 9 || strlen($digits) > 15) {
                $error = 'Ingresá un teléfono internacional completo válido (entre 9 y 15 dígitos).';
                return false;
            }
            $sig = $digits;
        } else {
            $sig = $digits;
            if (str_starts_with($raw, '+54') || (str_starts_with($sig, '54') && strlen($sig) >= 12)) {
                $sig = substr($sig, 2);
                if (str_starts_with($sig, '9') && strlen($sig) === 11) {
                    $sig = substr($sig, 1);
                }
            }
            if (str_starts_with($sig, '0')) {
                $sig = ltrim($sig, '0');
            }
            if (strlen($sig) < 10 || strlen($sig) > 12) {
                $error = 'Ingresá un teléfono argentino completo con código de área (10 dígitos sin el 0, ej: 3571 421500 o +54 9 351 5551234).';
                return false;
            }
        }

        if (count(array_unique(str_split($sig))) < 3) {
            $error = 'El número de teléfono ingresado no parece real (dígitos repetidos).';
            return false;
        }

        $tail6 = substr($sig, -6);
        $tail7 = substr($sig, -7);
        if (in_array($tail6, self::BLOCKED_PHONE_SEQS, true) || str_contains($tail7, '123456') || str_contains($tail7, '654321')) {
            $error = 'Ingresá un número de teléfono real, no secuencias ni dígitos repetidos.';
            return false;
        }

        return true;
    }

    public function valid_direccion(mixed $value, ?string &$error = null): bool
    {
        $v = self::normalizeWhitespace((string) $value);
        $len = mb_strlen($v, 'UTF-8');
        if ($len < 5 || $len > 150) {
            $error = 'El domicilio fiscal o de planta debe tener entre 5 y 150 caracteres.';
            return false;
        }

        $letterCount = preg_match_all('/\p{L}/u', $v, $matches);
        if ($letterCount === false || $letterCount < 3) {
            $error = 'Ingresá un domicilio real que incluya calle, ruta o parque industrial.';
            return false;
        }

        $compact = preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($v, 'UTF-8')) ?? '';
        if (preg_match('/(.)\1{3,}/u', $compact)) {
            $error = 'El domicilio ingresado contiene caracteres repetidos no válidos.';
            return false;
        }

        $lettersOnly = mb_strtolower(implode('', $matches[0]), 'UTF-8');
        if (count(array_unique(preg_split('//u', $lettersOnly, -1, PREG_SPLIT_NO_EMPTY) ?: [])) < 3) {
            $error = 'Ingresá una dirección postal coherente.';
            return false;
        }

        return true;
    }

    public function valid_email_corporativo(mixed $value, ?string &$error = null): bool
    {
        $v = strtolower(trim((string) $value));
        if ($v === '' || strlen($v) > 150 || !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresá un correo electrónico válido.';
            return false;
        }
        [$local, $domain] = explode('@', $v, 2);
        if (strlen($local) < 2 || !preg_match('/^[a-z0-9.-]{2,}\.[a-z]{2,12}$/', $domain)) {
            $error = 'El dominio del correo electrónico debe incluir una extensión real (ej: .com, .com.ar).';
            return false;
        }
        return true;
    }

    public function unique_email_empresa(mixed $value, ?string &$error = null): bool
    {
        $email = strtolower(trim((string) $value));
        if ($email === '') {
            return true;
        }
        $existing = (new \App\Models\UserModel(\Config\Database::connect('default')))
            ->where('LOWER(email)', $email)
            ->first();
        if ($existing !== null) {
            $error = 'Este correo electrónico ya se encuentra registrado en MateriaX.';
            return false;
        }
        return true;
    }

    public function valid_password_fuerte(mixed $value, ?string &$error = null): bool
    {
        $p = (string) $value;
        if (strlen($p) < 8 || strlen($p) > 100) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
            return false;
        }
        if (!preg_match('/\p{L}/u', $p) || !preg_match('/\d/', $p)) {
            $error = 'La contraseña debe combinar al menos una letra y un número.';
            return false;
        }
        $lower = strtolower($p);
        $weak = ['12345678', '123456789', '1234567890', 'password', 'password1', 'qwerty12', 'qwerty123', 'abcd1234', 'admin123', 'materiax1'];
        if (in_array($lower, $weak, true) || preg_match('/^(.)\1+$/', $p)) {
            $error = 'Elegí una contraseña menos predecible.';
            return false;
        }
        return true;
    }

    public function valid_provincia(mixed $value, ?string &$error = null): bool
    {
        $prov = trim((string) $value);
        if ($prov === '' || (new LocalidadModel())->provinciaExiste($prov) === null) {
            $error = 'Seleccioná una provincia válida de la lista.';
            return false;
        }
        return true;
    }

    public function valid_ciudad_provincia(mixed $value, string $provinciaField, array $data, ?string &$error = null): bool
    {
        $ciudad = trim((string) $value);
        $prov   = trim((string) ($data[$provinciaField] ?? ''));
        if ($ciudad === '') {
            $error = 'La ciudad o localidad es obligatoria.';
            return false;
        }
        if ($prov === '') {
            $error = 'Primero seleccioná la provincia para validar tu localidad.';
            return false;
        }
        if ((new LocalidadModel())->resolverEnProvincia($prov, $ciudad) === null) {
            $error = 'La localidad ingresada no coincide con ninguna ciudad registrada en ' . $prov . '. Seleccioná una del listado.';
            return false;
        }
        return true;
    }
}
