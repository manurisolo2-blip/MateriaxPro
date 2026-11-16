<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost:8080/';
    public array $allowedHostnames = [];
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';
    public string $defaultLocale = 'en';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['en'];
    public string $appTimezone = 'UTC';
    public string $charset = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;
    public array $proxyIPs = [];
    public bool $CSPEnabled = false;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
            $host = $_SERVER['HTTP_HOST'];

            if (getenv('VERCEL') || str_contains($host, ':8080')) {
                $this->baseURL = ($isHttps ? 'https://' : 'http://') . $host . '/';
            } else {
                $scriptDir     = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                $subfolder     = trim((string) preg_replace('#/public$#', '', $scriptDir), '/');
                $this->baseURL = ($isHttps ? 'https://' : 'http://') . $host . ($subfolder !== '' ? "/{$subfolder}" : '') . '/';
            }
        }
    }
}
