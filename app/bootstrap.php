<?php

$GLOBALS['config'] = require dirname(__DIR__) . '/config/config.php';

if (PHP_SAPI !== 'cli') {
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $detectedBase = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($detectedBase === '/' || $detectedBase === '.' || $detectedBase === '\\') {
        $detectedBase = '';
    }
    // Vercel runs PHP from /api/index.php — do not prefix URLs with /api.
    if (getenv('VERCEL') === '1' || str_contains($script, '/api/')) {
        $detectedBase = '';
    }
    if (!getenv('ETAB_BASE_PATH')) {
        $GLOBALS['config']['base_path'] = $detectedBase;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
    if (!getenv('ETAB_APP_URL')) {
        $GLOBALS['config']['app_url'] = $scheme . '://' . $host . $detectedBase;
    }
    if (!getenv('ETAB_UPLOAD_URL')) {
        $GLOBALS['config']['upload_url'] = $detectedBase . '/public/uploads';
    }
}

date_default_timezone_set($GLOBALS['config']['timezone'] ?? 'UTC');

spl_autoload_register(function ($class) {
    $map = [
        'Database' => '/app/Database.php',
        'Auth' => '/app/Auth.php',
        'Audit' => '/app/Audit.php',
        'View' => '/app/View.php',
        'Router' => '/app/Router.php',
        'ScoringEngine' => '/app/Services/ScoringEngine.php',
        'ResultsService' => '/app/Services/ResultsService.php',
        'User' => '/app/Models/User.php',
        'Event' => '/app/Models/Event.php',
        'Contestant' => '/app/Models/Contestant.php',
        'Criteria' => '/app/Models/Criteria.php',
        'CriteriaTemplate' => '/app/Models/Criteria.php',
        'Score' => '/app/Models/Score.php',
        'AuthController' => '/app/Controllers/AuthController.php',
        'AdminDashboardController' => '/app/Controllers/AdminDashboardController.php',
        'EventController' => '/app/Controllers/EventController.php',
        'ContestantController' => '/app/Controllers/ContestantController.php',
        'JudgeAdminController' => '/app/Controllers/JudgeAdminController.php',
        'CriteriaController' => '/app/Controllers/CriteriaController.php',
        'ResultsController' => '/app/Controllers/ResultsController.php',
        'JudgeController' => '/app/Controllers/JudgeController.php',
        'CookieSessionHandler' => '/app/CookieSessionHandler.php',
    ];
    if (isset($map[$class])) {
        require_once dirname(__DIR__) . $map[$class];
    }
});

require_once dirname(__DIR__) . '/app/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($GLOBALS['config']['session_name'] ?? 'etab_session');
    $cookiePath = $GLOBALS['config']['base_path'] ?: '/';
    $onVercel = (($_SERVER['VERCEL'] ?? '') === '1')
        || str_contains(strtolower((string) ($_SERVER['HTTP_HOST'] ?? '')), 'vercel.app');
    if ($onVercel) {
        $cookiePath = '/';
        session_set_save_handler(new CookieSessionHandler(), true);
    }
    session_set_cookie_params([
        'lifetime' => $onVercel ? 86400 * 7 : 0,
        'path' => $cookiePath,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function etab_routes(Router $r): void
{
    $r->get('/', fn () => Auth::check() ? (is_admin() ? redirect('admin') : redirect('judge')) : redirect('login'));
    $r->get('/login', 'AuthController@showLogin');
    $r->post('/login', 'AuthController@login');
    $r->post('/logout', 'AuthController@logout');
    $r->get('/logout', 'AuthController@logout');
    $r->get('/forgot-password', 'AuthController@showForgot');
    $r->post('/forgot-password', 'AuthController@forgot');
    $r->get('/reset-password', 'AuthController@showReset');
    $r->post('/reset-password', 'AuthController@reset');

    $r->get('/admin', 'AdminDashboardController@index');
    $r->get('/admin/analytics', 'AdminDashboardController@analytics');
    $r->get('/admin/profile', 'JudgeController@profile');
    $r->post('/admin/profile', 'JudgeController@updateProfile');

    $r->get('/admin/events', 'EventController@index');
    $r->get('/admin/events/create', 'EventController@create');
    $r->post('/admin/events', 'EventController@store');
    $r->get('/admin/events/{id}', 'EventController@show');
    $r->get('/admin/events/{id}/edit', 'EventController@edit');
    $r->post('/admin/events/{id}', 'EventController@update');
    $r->post('/admin/events/{id}/delete', 'EventController@destroy');
    $r->post('/admin/events/{id}/publish', 'EventController@publish');
    $r->post('/admin/events/{id}/assign-contestant', 'EventController@assignContestant');
    $r->post('/admin/events/{id}/unassign-contestant', 'EventController@unassignContestant');
    $r->get('/admin/events/{id}/scoresheet', 'ResultsController@scoreSheet');

    $r->post('/admin/events/{id}/criteria', 'CriteriaController@store');
    $r->post('/admin/criteria/{cid}', 'CriteriaController@update');
    $r->post('/admin/criteria/{cid}/delete', 'CriteriaController@destroy');
    $r->post('/admin/events/{id}/criteria/template', 'CriteriaController@applyTemplate');
    $r->post('/admin/events/{id}/criteria/save-template', 'CriteriaController@saveTemplate');
    $r->get('/admin/templates', 'CriteriaController@templates');
    $r->post('/admin/templates', 'CriteriaController@storeTemplate');
    $r->post('/admin/templates/{id}/delete', 'CriteriaController@destroyTemplate');

    $r->get('/admin/contestants', 'ContestantController@index');
    $r->get('/admin/contestants/create', 'ContestantController@create');
    $r->post('/admin/contestants', 'ContestantController@store');
    $r->get('/admin/contestants/export', 'ContestantController@exportCsv');
    $r->post('/admin/contestants/import', 'ContestantController@importCsv');
    $r->get('/admin/contestants/{id}/edit', 'ContestantController@edit');
    $r->post('/admin/contestants/{id}', 'ContestantController@update');
    $r->post('/admin/contestants/{id}/archive', 'ContestantController@archive');
    $r->post('/admin/contestants/{id}/delete', 'ContestantController@destroy');

    $r->get('/admin/judges', 'JudgeAdminController@index');
    $r->get('/admin/judges/create', 'JudgeAdminController@create');
    $r->post('/admin/judges', 'JudgeAdminController@store');
    $r->get('/admin/judges/{id}/edit', 'JudgeAdminController@edit');
    $r->post('/admin/judges/{id}', 'JudgeAdminController@update');
    $r->post('/admin/judges/{id}/delete', 'JudgeAdminController@destroy');

    $r->get('/admin/results', 'ResultsController@index');
    $r->get('/admin/results/export.csv', 'ResultsController@exportCsv');
    $r->get('/admin/results/export.xls', 'ResultsController@exportExcel');
    $r->get('/admin/results/print', 'ResultsController@printReport');
    $r->get('/admin/results/certificates', 'ResultsController@certificates');
    $r->get('/events/{id}/live', 'ResultsController@live');
    $r->get('/api/events/{id}/standings', 'ResultsController@apiStandings');

    $r->get('/judge', 'JudgeController@dashboard');
    $r->get('/judge/profile', 'JudgeController@profile');
    $r->post('/judge/profile', 'JudgeController@updateProfile');
    $r->get('/judge/scores', 'JudgeController@myScores');
    $r->get('/judge/events/{id}', 'JudgeController@event');
    $r->get('/judge/events/{id}/contestants/{cid}', 'JudgeController@scoreForm');
    $r->post('/judge/events/{id}/contestants/{cid}', 'JudgeController@submitScore');
    $r->post('/judge/events/{id}/contestants/{cid}/draft', 'JudgeController@saveDraft');
}
