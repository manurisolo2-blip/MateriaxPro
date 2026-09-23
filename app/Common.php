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
    /**
     * Devuelve la clase CSS de color específica según el tipo de resina polimérica.
     * Codificación técnica industrial conforme a estándares internacionales de reciclaje.
     */
    function badge_polimero_class(string $tipo): string
    {
        $t = mb_strtolower($tipo, 'UTF-8');
        if (str_contains($t, 'pe') || str_contains($t, 'polietileno')) {
            return 'badge-polimero-pe';
        }
        if (str_contains($t, 'pp') || str_contains($t, 'polipropileno')) {
            return 'badge-polimero-pp';
        }
        if (str_contains($t, 'pvc')) {
            return 'badge-polimero-pvc';
        }
        if (str_contains($t, 'abs')) {
            return 'badge-polimero-abs';
        }
        if (str_contains($t, 'nylon') || str_contains($t, 'pa')) {
            return 'badge-polimero-nylon';
        }
        if (str_contains($t, 'pet')) {
            return 'badge-polimero-pet';
        }
        if (str_contains($t, 'pallet') || str_contains($t, 'equipamiento')) {
            return 'badge-polimero-equipamiento';
        }
        return 'badge-polimero-default';
    }
}
