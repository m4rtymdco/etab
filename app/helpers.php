<?php

function config(string $key = null, $default = null)
{
    $cfg = $GLOBALS['config'] ?? [];
    if ($key === null) {
        return $cfg;
    }
    foreach (explode('.', $key) as $part) {
        if (!is_array($cfg) || !array_key_exists($part, $cfg)) {
            return $default;
        }
        $cfg = $cfg[$part];
    }
    return $cfg;
}

function base_path(): string
{
    $p = rtrim((string) config('base_path', ''), '/');
    return $p === '' ? '' : $p;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $base = base_path();
    return ($base === '' ? '' : $base) . '/' . $path;
}

function asset(string $path): string
{
    return url('public/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, $value = null)
{
    if ($value === null) {
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }
    $_SESSION['_flash'][$key] = $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
        http_response_code(419);
        echo 'Invalid CSRF token.';
        exit;
    }
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e($method) . '">';
}

function json_response($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function current_user(): ?array
{
    return Auth::user();
}

function is_admin(): bool
{
    return Auth::check() && Auth::user()['role'] === 'admin';
}

function is_judge(): bool
{
    return Auth::check() && Auth::user()['role'] === 'judge';
}

function require_auth(): void
{
    if (!Auth::check()) {
        flash('error', 'Please sign in to continue.');
        redirect('login');
    }
}

function require_admin(): void
{
    require_auth();
    if (!is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function require_judge(): void
{
    require_auth();
    if (!is_judge() && !is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function status_label(string $status): string
{
    return [
        'upcoming' => 'Upcoming',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'active' => 'Active',
        'archived' => 'Archived',
    ][$status] ?? ucfirst($status);
}

function format_dt(?string $dt): string
{
    if (!$dt) {
        return '—';
    }
    $t = strtotime($dt);
    return $t ? date('M j, Y g:i A', $t) : $dt;
}

function format_pct($value, int $decimals = 2): string
{
    return number_format((float) $value, $decimals) . '%';
}

function format_score($value, int $decimals = 2): string
{
    $n = (float) $value;
    if (abs($n - round($n)) < 0.005) {
        return (string) (int) round($n);
    }
    return number_format($n, $decimals);
}

function place_markup(?int $rank, bool $scored = true): string
{
    if (!$scored || !$rank) {
        return '<span class="place-num">—</span>';
    }
    if ($rank <= 3) {
        return '<span class="place-medal place-' . (int) $rank . '">' . (int) $rank . '</span>';
    }
    return '<span class="place-num">' . (int) $rank . '</span>';
}

function format_date(?string $d): string
{
    if (!$d) {
        return '—';
    }
    $t = strtotime($d);
    return $t ? date('M j, Y', $t) : $d;
}

function pagination(int $total, int $page, int $perPage = 25): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => $pages,
        'offset' => ($page - 1) * $perPage,
    ];
}
