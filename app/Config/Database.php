<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath    = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN' => '', 'hostname' => 'localhost', 'username' => 'root', 'password' => '',
        'database' => 'materiax_db', 'DBDriver' => 'MySQLi', 'DBPrefix' => '', 'pConnect' => false,
        'DBDebug' => true, 'charset' => 'utf8mb4', 'DBCollat' => 'utf8mb4_unicode_ci',
        'swapPre' => '', 'encrypt' => false, 'compress' => false, 'strictOn' => false,
        'failover' => [], 'port' => 3306, 'numberNative' => false, 'foundRows' => false,
        'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
    ];

    public array $tests = [
        'DSN' => '', 'hostname' => '127.0.0.1', 'username' => '', 'password' => '',
        'database' => ':memory:', 'DBDriver' => 'SQLite3', 'DBPrefix' => 'db_', 'pConnect' => false,
        'DBDebug' => true, 'charset' => 'utf8', 'DBCollat' => '', 'swapPre' => '', 'encrypt' => false,
        'compress' => false, 'strictOn' => true, 'failover' => [], 'port' => 3306,
        'foreignKeys' => true, 'busyTimeout' => 5000, 'synchronous' => null,
        'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
    ];

    /** Resuelve el primer valor no vacío en getenv, $_ENV o $_SERVER. */
    public static function resolveEnv(array $keys): ?string
    {
        foreach ($keys as $key) {
            foreach ([getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null] as $val) {
                if ($val !== false && $val !== null && trim((string) $val) !== '') {
                    return trim((string) $val);
                }
            }
        }
        return null;
    }

    private static function setRuntimeEnv(string $key, string $val): void
    {
        putenv("{$key}={$val}");
        $_ENV[$key] = $_SERVER[$key] = $val;
    }

    public function __construct()
    {
        parent::__construct();
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // 1. Supabase / PostgreSQL
        $pgHost = null;
        if (self::resolveEnv(['MATERIAX_PG_UNAVAILABLE']) !== '1') {
            $pgUrl  = self::resolveEnv(['POSTGRES_URL_NON_POOLING', 'POSTGRES_URL', 'SUPABASE_DB_URL', 'DATABASE_URL']);
            $pgHost = self::resolveEnv(['POSTGRES_HOST', 'SUPABASE_HOST', 'PGHOST']);
            $pgUser = self::resolveEnv(['POSTGRES_USER', 'SUPABASE_USER', 'PGUSER']);
            $pgPass = self::resolveEnv(['POSTGRES_PASSWORD', 'SUPABASE_PASSWORD', 'PGPASSWORD']);
            $pgName = self::resolveEnv(['POSTGRES_DATABASE', 'SUPABASE_DATABASE', 'PGDATABASE']);
            $pgPort = (int) (self::resolveEnv(['POSTGRES_PORT', 'SUPABASE_PORT', 'PGPORT']) ?: 0);

            if (!empty($pgUrl) && preg_match('/^postgre(s|sql):\/\/|^pgsql:\/\//i', $pgUrl) && ($p = parse_url($pgUrl))) {
                $pgHost = $pgHost ?: ($p['host'] ?? null);
                $pgUser = $pgUser ?: (isset($p['user']) ? urldecode($p['user']) : null);
                $pgPass = $pgPass ?: (isset($p['pass']) ? urldecode($p['pass']) : null);
                $pgPort = $pgPort ?: (int) ($p['port'] ?? 5432);
                $pgName = $pgName ?: (!empty($p['path']) ? ltrim($p['path'], '/') : 'postgres');
            }
        }

        if (!empty($pgHost)) {
            if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $pgHost, $m)) {
                $pgHost = self::resolveEnv(['SUPABASE_POOLER_HOST']) ?: 'aws-1-sa-east-1.pooler.supabase.com';
                $pgPort = 5432;
                $pgUser = (empty($pgUser) || $pgUser === 'postgres') ? 'postgres.' . $m[1] : $pgUser;
            } elseif (str_contains($pgHost, 'pooler.supabase.com') && $pgPort === 6543) {
                $pgPort = 5432;
            }

            if (self::resolveEnv(['MATERIAX_PG_VERIFIED']) !== '1') {
                try {
                    new \PDO(
                        'pgsql:host=' . $pgHost . ';port=' . ($pgPort ?: 5432) . ';dbname=' . ($pgName ?: 'postgres') . ';sslmode=require',
                        $pgUser ?: 'postgres',
                        $pgPass ?? '',
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 3]
                    );
                    self::setRuntimeEnv('MATERIAX_PG_VERIFIED', '1');
                } catch (\Throwable $e) {
                    error_log('PostgreSQL fallback triggered in Database.php: ' . $e->getMessage());
                    self::setRuntimeEnv('MATERIAX_PG_UNAVAILABLE', '1');
                    self::setRuntimeEnv('MATERIAX_PG_ERROR', $e->getMessage());
                    $pgHost = null;
                }
            }
        }

        if (!empty($pgHost)) {
            $this->default = [
                'DSN' => '', 'hostname' => $pgHost, 'username' => $pgUser ?: 'postgres', 'password' => $pgPass ?? '',
                'database' => $pgName ?: 'postgres', 'schema' => 'public', 'DBDriver' => 'Postgre', 'DBPrefix' => '',
                'pConnect' => false, 'DBDebug' => true, 'charset' => 'utf8', 'swapPre' => '', 'failover' => [],
                'port' => $pgPort ?: 5432, 'connect_timeout' => '10', 'sslmode' => 'require',
                'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
            ];
            return;
        }

        // 2. Cloud MySQL / TiDB
        $cloudHost = $dbUrl = null;
        if (self::resolveEnv(['MATERIAX_CLOUD_UNAVAILABLE']) !== '1') {
            $dbUrl     = self::resolveEnv(['DATABASE_URL', 'TIDB_URL', 'MYSQL_URL', 'JAWSDB_URL', 'CLEARDB_DATABASE_URL']);
            $cloudHost = self::resolveEnv(['TIDB_HOST', 'DB_HOST', 'MYSQL_HOST', 'MYSQLHOST', 'DATABASE_HOST']);
            $cloudUser = self::resolveEnv(['TIDB_USER', 'DB_USER', 'MYSQL_USER', 'MYSQLUSER', 'DATABASE_USER']);
            $cloudPass = self::resolveEnv(['TIDB_PASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD', 'DATABASE_PASSWORD']);
            $cloudName = self::resolveEnv(['TIDB_DATABASE', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE', 'DATABASE_NAME']);
            $cloudPort = (int) (self::resolveEnv(['TIDB_PORT', 'DB_PORT', 'MYSQL_PORT', 'MYSQLPORT']) ?: 0);

            if (!empty($dbUrl) && preg_match('/^mysqli?:\/\//i', $dbUrl) && ($p = parse_url($dbUrl))) {
                $cloudHost = $cloudHost ?: ($p['host'] ?? null);
                $cloudUser = $cloudUser ?: (isset($p['user']) ? urldecode($p['user']) : null);
                $cloudPass = $cloudPass ?: (isset($p['pass']) ? urldecode($p['pass']) : null);
                $cloudPort = $cloudPort ?: (int) ($p['port'] ?? 4000);
                $cloudName = $cloudName ?: (!empty($p['path']) ? ltrim($p['path'], '/') : null);
            }
        }

        $cloudSsl = !empty($cloudHost) && (self::resolveEnv(['DB_SSL']) || self::resolveEnv(['TIDB_HOST']) || str_contains(($dbUrl ?? '') . $cloudHost, 'tidbcloud.com'));
        $caFile   = $cloudSsl ? realpath(__DIR__ . '/../../writable/cacert.pem') : false;

        if (!empty($cloudHost) && self::resolveEnv(['MATERIAX_CLOUD_VERIFIED']) !== '1') {
            try {
                $opts = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 3];
                if ($cloudSsl) {
                    if ($caFile && file_exists($caFile)) {
                        $opts[\PDO::MYSQL_ATTR_SSL_CA] = $caFile;
                    }
                    $opts[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }
                new \PDO(
                    'mysql:host=' . $cloudHost . ';port=' . ($cloudPort ?: 4000) . ';dbname=' . ($cloudName ?: (self::resolveEnv(['TIDB_HOST']) ? 'test' : 'materiax_db')) . ';charset=utf8mb4',
                    $cloudUser ?: 'root',
                    $cloudPass ?: '',
                    $opts
                );
                self::setRuntimeEnv('MATERIAX_CLOUD_VERIFIED', '1');
            } catch (\Throwable $e) {
                error_log('Cloud MySQL fallback triggered in Database.php: ' . $e->getMessage());
                self::setRuntimeEnv('MATERIAX_CLOUD_UNAVAILABLE', '1');
                self::setRuntimeEnv('MATERIAX_CLOUD_ERROR', $e->getMessage());
                $cloudHost = null;
            }
        }

        if (!empty($cloudHost)) {
            $this->default['hostname'] = $cloudHost;
            $this->default['username'] = $cloudUser ?: 'root';
            $this->default['password'] = $cloudPass ?: '';
            $this->default['database'] = $cloudName ?: (self::resolveEnv(['TIDB_HOST']) ? 'test' : 'materiax_db');
            $this->default['port']     = $cloudPort ?: 4000;
            $this->default['DBDriver'] = 'MySQLi';

            if ($cloudSsl) {
                $this->default['encrypt'] = ['ssl_ca' => ($caFile && file_exists($caFile)) ? $caFile : null, 'ssl_verify' => false];
            }
            return;
        }

        // 3. Fallback SQLite en Vercel o local si MySQL no responde
        $sqliteConfig = static fn (string $path): array => [
            'DSN' => '', 'hostname' => '', 'username' => '', 'password' => '',
            'database' => $path, 'DBDriver' => 'SQLite3', 'DBPrefix' => '', 'pConnect' => false,
            'DBDebug' => true, 'swapPre' => '', 'failover' => [], 'foreignKeys' => true,
            'busyTimeout' => 5000, 'synchronous' => null,
            'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
        ];

        if (self::resolveEnv(['VERCEL']) && empty(self::resolveEnv(['database.default.hostname']))) {
            $tmp  = sys_get_temp_dir();
            $base = (!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp';
            $this->default = $sqliteConfig(rtrim($base, '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable' . DIRECTORY_SEPARATOR . 'materiax_db.sqlite');
        } elseif (ENVIRONMENT !== 'testing' && extension_loaded('sqlite3') && is_file(WRITEPATH . 'materiax_db.sqlite')) {
            try {
                new \PDO(
                    'mysql:host=' . ($this->default['hostname'] ?: 'localhost') . ';port=' . ($this->default['port'] ?: 3306) . ';dbname=' . $this->default['database'] . ';charset=utf8mb4',
                    $this->default['username'] ?: 'root',
                    $this->default['password'] ?? '',
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 1]
                );
            } catch (\Throwable) {
                $this->default = $sqliteConfig(WRITEPATH . 'materiax_db.sqlite');
            }
        }
    }
}
