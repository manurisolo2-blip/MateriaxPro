<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'materiax_db',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 5000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Helper para resolver variables de entorno en cualquier contexto (getenv, $_ENV, $_SERVER)
        $resolveEnv = static function (array $keys): ?string {
            foreach ($keys as $key) {
                $val = getenv($key);
                if ($val !== false && $val !== null && trim((string)$val) !== '') {
                    return trim((string)$val);
                }
                if (!empty($_ENV[$key]) && trim((string)$_ENV[$key]) !== '') {
                    return trim((string)$_ENV[$key]);
                }
                if (!empty($_SERVER[$key]) && trim((string)$_SERVER[$key]) !== '') {
                    return trim((string)$_SERVER[$key]);
                }
            }
            return null;
        };

        $tmp          = sys_get_temp_dir();
        $baseWritable = (!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp';
        $targetDir    = rtrim($baseWritable, '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        // 1. Detección prioritaria de Supabase / PostgreSQL (con verificación de disponibilidad y fallback automático)
        $pgUrl  = $resolveEnv(['POSTGRES_URL_NON_POOLING', 'POSTGRES_URL', 'SUPABASE_DB_URL', 'DATABASE_URL']);
        $pgHost = $resolveEnv(['POSTGRES_HOST', 'SUPABASE_HOST', 'PGHOST']);
        $pgUser = $resolveEnv(['POSTGRES_USER', 'SUPABASE_USER', 'PGUSER']);
        $pgPass = $resolveEnv(['POSTGRES_PASSWORD', 'SUPABASE_PASSWORD', 'PGPASSWORD']);
        $pgName = $resolveEnv(['POSTGRES_DATABASE', 'SUPABASE_DATABASE', 'PGDATABASE']);
        $pgPort = (int) ($resolveEnv(['POSTGRES_PORT', 'SUPABASE_PORT', 'PGPORT']) ?: 0);

        $isPostgresUrl = !empty($pgUrl) && (
            str_starts_with($pgUrl, 'postgres://')
            || str_starts_with($pgUrl, 'postgresql://')
            || str_starts_with($pgUrl, 'pgsql://')
        );

        if ($isPostgresUrl) {
            $parsed = parse_url($pgUrl);
            $pgHost = $pgHost ?: ($parsed['host'] ?? null);
            $pgUser = $pgUser ?: (isset($parsed['user']) ? urldecode($parsed['user']) : null);
            $pgPass = $pgPass ?: (isset($parsed['pass']) ? urldecode($parsed['pass']) : null);
            $pgPort = $pgPort ?: (int) ($parsed['port'] ?? 5432);
            $pgName = $pgName ?: (!empty($parsed['path']) ? ltrim($parsed['path'], '/') : 'postgres');
        }

        if (!empty($pgHost) && $resolveEnv(['MATERIAX_PG_UNAVAILABLE']) !== '1') {
            if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $pgHost, $m)) {
                $projectRef = $m[1];
                $pgHost     = $resolveEnv(['SUPABASE_POOLER_HOST']) ?: 'aws-1-sa-east-1.pooler.supabase.com';
                $pgPort     = 5432;
                if (empty($pgUser) || $pgUser === 'postgres') {
                    $pgUser = 'postgres.' . $projectRef;
                }
            } elseif (str_contains($pgHost, 'pooler.supabase.com') && $pgPort === 6543) {
                $pgPort = 5432;
            }

            $pgUserFinal = $pgUser ?: 'postgres';
            $pgPassFinal = $pgPass ?? '';
            $pgNameFinal = $pgName ?: 'postgres';
            $pgPortFinal = $pgPort ?: 5432;

            $pgHash       = md5($pgHost . '|' . $pgUserFinal . '|' . $pgNameFinal);
            $pgInitMarker = $targetDir . DIRECTORY_SEPARATOR . '.pg_init_' . $pgHash;
            $pgDownMarker = $targetDir . DIRECTORY_SEPARATOR . '.pg_down_' . $pgHash;

            $isRecentlyDown = file_exists($pgDownMarker) && ((time() - (int) @file_get_contents($pgDownMarker)) < 60);
            $isRecentlyOk   = !$isRecentlyDown && file_exists($pgInitMarker) && ((time() - (int) @file_get_contents($pgInitMarker)) < 120);

            if (!$isRecentlyDown && !$isRecentlyOk) {
                try {
                    $testPdo = new \PDO(
                        "pgsql:host={$pgHost};port={$pgPortFinal};dbname={$pgNameFinal};sslmode=require",
                        $pgUserFinal,
                        $pgPassFinal,
                        [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_TIMEOUT => 3,
                        ]
                    );
                    $testPdo = null;
                    @file_put_contents($pgInitMarker, (string) time());
                    @unlink($pgDownMarker);
                    $isRecentlyOk = true;
                } catch (\Throwable $e) {
                    @file_put_contents($pgDownMarker, (string) time());
                    @unlink($pgInitMarker);
                    putenv('MATERIAX_PG_UNAVAILABLE=1');
                    $_ENV['MATERIAX_PG_UNAVAILABLE']    = '1';
                    $_SERVER['MATERIAX_PG_UNAVAILABLE'] = '1';
                    putenv('MATERIAX_PG_LAST_ERROR=' . substr($e->getMessage(), 0, 220));
                    $_ENV['MATERIAX_PG_LAST_ERROR']    = substr($e->getMessage(), 0, 220);
                    $_SERVER['MATERIAX_PG_LAST_ERROR'] = substr($e->getMessage(), 0, 220);
                    $isRecentlyDown = true;
                }
            }

            if ($isRecentlyOk && !$isRecentlyDown) {
                $this->default = [
                    'DSN'             => '',
                    'hostname'        => $pgHost,
                    'username'        => $pgUserFinal,
                    'password'        => $pgPassFinal,
                    'database'        => $pgNameFinal,
                    'schema'          => 'public',
                    'DBDriver'        => 'Postgre',
                    'DBPrefix'        => '',
                    'pConnect'        => false,
                    'DBDebug'         => true,
                    'charset'         => 'utf8',
                    'swapPre'         => '',
                    'failover'        => [],
                    'port'            => $pgPortFinal,
                    'connect_timeout' => '10',
                    'sslmode'         => 'require',
                    'dateFormat'      => [
                        'date'     => 'Y-m-d',
                        'datetime' => 'Y-m-d H:i:s',
                        'time'     => 'H:i:s',
                    ],
                ];
                return;
            }
        }

        // 2. Detección de base de datos MySQL en la nube (TiDB Cloud, Aiven, Railway o VPS)
        $dbUrl     = $resolveEnv(['DATABASE_URL', 'TIDB_URL', 'MYSQL_URL', 'JAWSDB_URL', 'CLEARDB_DATABASE_URL']);
        $cloudHost = $resolveEnv(['TIDB_HOST', 'DB_HOST', 'MYSQL_HOST', 'MYSQLHOST', 'DATABASE_HOST']);
        $cloudUser = $resolveEnv(['TIDB_USER', 'DB_USER', 'MYSQL_USER', 'MYSQLUSER', 'DATABASE_USER']);
        $cloudPass = $resolveEnv(['TIDB_PASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD', 'DATABASE_PASSWORD']);
        $cloudName = $resolveEnv(['TIDB_DATABASE', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE', 'DATABASE_NAME']);
        $cloudPort = (int) ($resolveEnv(['TIDB_PORT', 'DB_PORT', 'MYSQL_PORT', 'MYSQLPORT']) ?: 0);

        if (!empty($dbUrl) && (str_starts_with($dbUrl, 'mysql://') || str_starts_with($dbUrl, 'mysqli://'))) {
            $parsed = parse_url($dbUrl);
            $cloudHost = $cloudHost ?: ($parsed['host'] ?? null);
            $cloudUser = $cloudUser ?: (isset($parsed['user']) ? urldecode($parsed['user']) : null);
            $cloudPass = $cloudPass ?: (isset($parsed['pass']) ? urldecode($parsed['pass']) : null);
            $cloudPort = $cloudPort ?: (int) ($parsed['port'] ?? 4000);
            $cloudName = $cloudName ?: (!empty($parsed['path']) ? ltrim($parsed['path'], '/') : null);
        }

        if (!empty($cloudHost) && $resolveEnv(['MATERIAX_CLOUD_MYSQL_UNAVAILABLE']) !== '1') {
            $this->default['hostname'] = $cloudHost;
            $this->default['username'] = $cloudUser ?: 'root';
            $this->default['password'] = $cloudPass ?: '';
            $this->default['database'] = $cloudName ?: ($resolveEnv(['TIDB_HOST']) ? 'test' : 'materiax_db');
            $this->default['port']     = $cloudPort ?: 4000;
            $this->default['DBDriver'] = 'MySQLi';

            $needsSsl = $resolveEnv(['DB_SSL']) || $resolveEnv(['TIDB_HOST']) || (!empty($dbUrl) && str_contains($dbUrl, 'tidbcloud.com')) || (!empty($cloudHost) && str_contains($cloudHost, 'tidbcloud.com'));
            if ($needsSsl) {
                $caFile = realpath(__DIR__ . '/../../writable/cacert.pem');
                $this->default['encrypt'] = [
                    'ssl_ca'     => ($caFile && file_exists($caFile)) ? $caFile : null,
                    'ssl_verify' => false,
                ];
            }
        } elseif ((getenv('VERCEL') || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL'])) && empty(getenv('database.default.hostname'))) {
            // 3. Fallback automático SQLite en Vercel si no hay base externa o si Supabase/Cloud está pausado
            $dbPath = $targetDir . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';
            $seedDb = realpath(__DIR__ . '/../../writable/materiax_db.sqlite');
            if ((!file_exists($dbPath) || filesize($dbPath) === 0) && $seedDb && file_exists($seedDb)) {
                @copy($seedDb, $dbPath);
            }

            $this->default = [
                'DSN'         => '',
                'hostname'    => '',
                'username'    => '',
                'password'    => '',
                'database'    => $dbPath,
                'DBDriver'    => 'SQLite3',
                'DBPrefix'    => '',
                'pConnect'    => false,
                'DBDebug'     => true,
                'swapPre'     => '',
                'failover'    => [],
                'foreignKeys' => true,
                'busyTimeout' => 5000,
                'synchronous' => null,
                'dateFormat'  => [
                    'date'     => 'Y-m-d',
                    'datetime' => 'Y-m-d H:i:s',
                    'time'     => 'H:i:s',
                ],
            ];
        }
    }
}
