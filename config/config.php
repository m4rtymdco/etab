<?php
/**
 * Application configuration.
 * Local XAMPP defaults stay in place. Hostinger (etab.digoscity.gov.ph) is auto-detected.
 */
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$onVercel = getenv('VERCEL') === '1' || str_contains($host, 'vercel.app');
$onHostinger = getenv('ETAB_ENV') === 'hostinger'
    || str_contains($host, 'digoscity.gov.ph')
    || $onVercel;

$local = [
    'app_name' => getenv('ETAB_APP_NAME') ?: 'eTab',
    'app_url' => getenv('ETAB_APP_URL') ?: 'http://localhost:8080/eTab',
    'base_path' => getenv('ETAB_BASE_PATH') ?: '/eTab',
    'debug' => (getenv('ETAB_DEBUG') ?: '1') === '1',
    'timezone' => 'Asia/Manila',
    'session_name' => 'etab_session',
    'csrf_key' => 'etab_csrf',
    'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    'upload_url' => '/eTab/public/uploads',
    'poll_interval_ms' => 4000,
    'db' => [
        'host' => getenv('ETAB_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('ETAB_DB_PORT') ?: '3306',
        'name' => getenv('ETAB_DB_NAME') ?: 'etab',
        'user' => getenv('ETAB_DB_USER') ?: 'root',
        'pass' => getenv('ETAB_DB_PASS') !== false ? getenv('ETAB_DB_PASS') : '',
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

return [
    'app_name' => getenv('ETAB_APP_NAME') ?: 'eTab',
    'app_url' => getenv('ETAB_APP_URL') ?: ($onVercel
        ? ((getenv('VERCEL_URL') ? ('https://' . getenv('VERCEL_URL')) : 'https://etab.digoscity.gov.ph'))
        : 'https://etab.digoscity.gov.ph'),
    'base_path' => getenv('ETAB_BASE_PATH') !== false ? getenv('ETAB_BASE_PATH') : '',
    'debug' => (getenv('ETAB_DEBUG') ?: '0') === '1',
    'timezone' => 'Asia/Manila',
    'session_name' => 'etab_session',
    'csrf_key' => 'etab_csrf',
    'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    'upload_url' => '/public/uploads',
    'poll_interval_ms' => 4000,
    'db' => [
        'host' => getenv('ETAB_DB_HOST') ?: 'localhost',
        'port' => getenv('ETAB_DB_PORT') ?: '3306',
        'name' => getenv('ETAB_DB_NAME') ?: 'u934483906_etab',
        'user' => getenv('ETAB_DB_USER') ?: 'u934483906_etab',
        'pass' => getenv('ETAB_DB_PASS') !== false ? getenv('ETAB_DB_PASS') : 'eTab1234',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled' => false,
        'from' => 'noreply@etab.digoscity.gov.ph',
    ],
];
