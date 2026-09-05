<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = rawurldecode(str_replace('\\', '/', $uri));
if (str_starts_with($uri, '/api/')) {
    $uri = substr($uri, 4) ?: '/';
}
if (preg_match('#^/public/(css|js|img|uploads)/#', $uri)) {
    $full = realpath(__DIR__ . $uri);
    $root = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'public');
    if ($full && $root && str_starts_with($full, $root) && is_file($full)) {
        $ext = strtolower((string) pathinfo($full, PATHINFO_EXTENSION));
        $types = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        readfile($full);
        exit;
    }
}

require __DIR__ . '/app/bootstrap.php';

$router = new Router();
etab_routes($router);
$router->dispatch();
