<?php

namespace Config;

class Paths
{
    public string $systemDirectory   = __DIR__ . '/../../vendor/codeigniter4/framework/system';
    public string $appDirectory      = __DIR__ . '/..';
    public string $writableDirectory = __DIR__ . '/../../writable';
    public string $testsDirectory    = __DIR__ . '/../../tests';
    public string $viewDirectory     = __DIR__ . '/../Views';
    public string $envDirectory      = __DIR__ . '/../../';

    public function __construct()
    {
        if (getenv('VERCEL')) {
            $tmp       = sys_get_temp_dir();
            $targetDir = rtrim((!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp', '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable';
            foreach (['', 'cache', 'logs', 'session', 'uploads', 'debugbar'] as $sub) {
                $dir = $sub === '' ? $targetDir : $targetDir . DIRECTORY_SEPARATOR . $sub;
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
            }
            $this->writableDirectory = $targetDir;
        }
    }
}
