<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('badge_polimero_class')) {
    function badge_polimero_class(string $tipo): string
    {
        $t = mb_strtolower($tipo, 'UTF-8');
        return match (true) {
            str_contains($t, 'pe') || str_contains($t, 'polietileno') => 'badge-polimero-pe',
            str_contains($t, 'pp') || str_contains($t, 'polipropileno') => 'badge-polimero-pp',
            str_contains($t, 'pvc') => 'badge-polimero-pvc',
            str_contains($t, 'abs') => 'badge-polimero-abs',
            str_contains($t, 'nylon') || str_contains($t, 'pa') => 'badge-polimero-nylon',
            str_contains($t, 'pet') => 'badge-polimero-pet',
            str_contains($t, 'pallet') || str_contains($t, 'equipamiento') => 'badge-polimero-equipamiento',
            default => 'badge-polimero-default',
        };
    }
}

if (! function_exists('badge_estado_class')) {
    function badge_estado_class(string $estado): string
    {
        return match ($estado) {
            'Reservado' => 'badge-reservado',
            'Vendido'   => 'badge-vendido',
            default     => 'badge-disponible',
        };
    }
}

if (! function_exists('format_kg')) {
    function format_kg(float|int|string|null $kg): string
    {
        return number_format((float) $kg, 0, ',', '.') . ' kg';
    }
}

if (! function_exists('format_precio')) {
    function format_precio(float|int|string|null $precio): string
    {
        return '$' . number_format((float) $precio, 2, ',', '.');
    }
}

if (! function_exists('nav_attr')) {
    function nav_attr(bool $isActive): string
    {
        return $isActive ? 'class="active" aria-current="page"' : '';
    }
}

