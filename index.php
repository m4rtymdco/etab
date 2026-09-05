<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = new Router();
etab_routes($router);
$router->dispatch();
