<?php

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, $handler): void
    {
        $this->routes[] = [$method, $this->normalize($path), $handler];
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = $this->normalize($path);

        // Front controller: /index.php/login or ?r=
        if (isset($_GET['r'])) {
            $path = $this->normalize((string) $_GET['r']);
        }

        foreach ($this->routes as [$m, $pattern, $handler]) {
            $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
            if ($m === $method && preg_match('#^' . $regex . '$#', $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->invoke($handler, $params);
                return;
            }
        }

        http_response_code(404);
        if (file_exists(dirname(__DIR__) . '/views/errors/404.php')) {
            View::render('errors/404', ['title' => 'Not found'], 'layouts/guest');
        } else {
            echo '404 Not Found';
        }
    }

    private function invoke($handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = new $class();
            $controller->$method($params);
            return;
        }
        throw new RuntimeException('Invalid route handler');
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        if (str_ends_with($path, '/index.php')) {
            $path = substr($path, 0, -10) ?: '/';
        }
        return $path === '' ? '/' : $path;
    }
}
