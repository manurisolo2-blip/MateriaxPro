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

        // 1. Detección prioritaria de Supabase / PostgreSQL (Integración nativa de Vercel o URI manual)
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

        if (!empty($pgHost)) {
            // Si el usuario pegó el host Direct Connection de Supabase (db.<ref>.supabase.co, que es solo IPv6 y falla en Vercel),
            // redirigir automáticamente al Session Pooler IPv4 (aws-1-sa-east-1.pooler.supabase.com:5432) con usuario postgres.<ref>
            if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $pgHost, $m)) {
                $projectRef = $m[1];
                $pgHost     = $resolveEnv(['SUPABASE_POOLER_HOST']) ?: 'aws-1-sa-east-1.pooler.supabase.com';
                $pgPort     = 5432;
                if (empty($pgUser) || $pgUser === 'postgres') {
                    $pgUser = 'postgres.' . $projectRef;
                }
            } elseif (str_contains($pgHost, 'pooler.supabase.com') && $pgPort === 6543) {
                // En Supabase Pooler (pooler.supabase.com), el puerto 5432 corresponde a Session Mode (compatible con LASTVAL/secuencias de CI4)
                $pgPort = 5432;
            }

            $this->default = [
                'DSN'             => '',
                'hostname'        => $pgHost,
                'username'        => $pgUser ?: 'postgres',
                'password'        => $pgPass ?? '',
                'database'        => $pgName ?: 'postgres',
                'schema'          => 'public',
                'DBDriver'        => 'Postgre',
                'DBPrefix'        => '',
                'pConnect'        => false,
                'DBDebug'         => true,
                'charset'         => 'utf8',
                'swapPre'         => '',
                'failover'        => [],
                'port'            => $pgPort ?: 5432,
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

        if (!empty($cloudHost)) {
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
        } elseif (getenv('VERCEL') && empty(getenv('database.default.hostname'))) {
            // 3. Fallback SQLite en Vercel si aún no se configuró base de datos externa
            $tmp = sys_get_temp_dir();
            $baseWritable = (!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp';
            $dbPath = rtrim($baseWritable, '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable' . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';

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
