<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\{Cors, CSRF, DebugToolbar, ForceHTTPS, Honeypot, InvalidChars, PageCache, PerformanceMetrics, SecureHeaders};

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'keepalive'     => \App\Filters\SessionKeepAliveFilter::class,
        'auth'          => \App\Filters\AuthFilter::class,
        'admin'         => \App\Filters\AdminFilter::class,
    ];

    public array $required = [
        'before' => ['forcehttps', 'pagecache'],
        'after'  => ['pagecache', 'performance'], // toolbar desactivada para evitar JS inyectado
    ];

    public array $globals = [
        'before' => ['keepalive'],
        'after'  => [],
    ];

    public array $methods = [];
    public array $filters = [];
}
