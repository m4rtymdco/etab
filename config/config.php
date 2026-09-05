<?php
/**
 * Application configuration.
 * Local XAMPP defaults stay in place. Hostinger / Vercel use the remote DB.
 */
if (!function_exists('etab_env')) {
    function etab_env(string $key, ?string $default = null): ?string
    {
        foreach ([$_SERVER[$key] ?? null, $_ENV[$key] ?? null] as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }
        $g = getenv($key);
        if (is_string($g) && $g !== '') {
            return $g;
        }
        return $default;
    }
}

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$onVercel = etab_env('VERCEL') === '1' || str_contains($host, 'vercel.app');
$onHostinger = etab_env('ETAB_ENV') === 'hostinger'
    || str_contains($host, 'digoscity.gov.ph')
    || $onVercel;

$local = [
    'app_name' => etab_env('ETAB_APP_NAME', 'eTab'),
    'app_url' => etab_env('ETAB_APP_URL', 'http://localhost:8080/eTab'),
    'base_path' => etab_env('ETAB_BASE_PATH', '/eTab'),
    'debug' => (etab_env('ETAB_DEBUG', '1') === '1'),
    'timezone' => 'Asia/Manila',
    'session_name' => 'etab_session',
    'csrf_key' => 'etab_csrf',
    'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    'upload_url' => '/eTab/public/uploads',
    'poll_interval_ms' => 4000,
    'db' => [
        'host' => etab_env('ETAB_DB_HOST', '127.0.0.1'),
        'port' => etab_env('ETAB_DB_PORT', '3306'),
        'name' => etab_env('ETAB_DB_NAME', 'etab'),
        'user' => etab_env('ETAB_DB_USER', 'root'),
        'pass' => etab_env('ETAB_DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled' => false,
        'from' => 'noreply@etab.local',
    ],
];

if (!$onHostinger) {
    return $local;
}

$dbHost = etab_env('ETAB_DB_HOST');
if ($onVercel && ($dbHost === null || $dbHost === '' || in_array($dbHost, ['localhost', '127.0.0.1'], true))) {
    $dbHost = '';
}

return [
    'app_name' => etab_env('ETAB_APP_NAME', 'eTab'),
    'app_url' => etab_env('ETAB_APP_URL') ?: ($onVercel
        ? ('https://' . (etab_env('VERCEL_URL') ?: 'etab.digoscity.gov.ph'))
        : 'https://etab.digoscity.gov.ph'),
    'base_path' => etab_env('ETAB_BASE_PATH', ''),
    'debug' => etab_env('ETAB_DEBUG', '0') === '1',
    'timezone' => 'Asia/Manila',
    'session_name' => 'etab_session',
    'csrf_key' => 'etab_csrf',
    'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    'upload_url' => '/public/uploads',
    'poll_interval_ms' => 4000,
    'db' => [
        'host' => $dbHost !== null && $dbHost !== '' ? $dbHost : ($onVercel ? '' : 'localhost'),
        'port' => etab_env('ETAB_DB_PORT', '3306'),
        'name' => etab_env('ETAB_DB_NAME', 'u934483906_etab'),
        'user' => etab_env('ETAB_DB_USER', 'u934483906_etab'),
        'pass' => etab_env('ETAB_DB_PASS', 'eTab1234'),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled' => false,
        'from' => 'noreply@etab.digoscity.gov.ph',
    ],
];
