<?php $user = current_user(); $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title ?? 'eTab') ?> · eTab</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="festival-strip" aria-hidden="true"></div>
    <div class="brand">
      <img src="<?= e(asset('img/digos-seal.png')) ?>" alt="City of Digos seal">
      eTab
    </div>
    <nav class="nav" aria-label="Primary">
      <?php if (is_admin()): ?>
        <a class="<?= str_contains($path, '/admin') && !str_contains($path, '/admin/') ? 'active' : '' ?>" href="<?= e(url('admin')) ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="<?= str_contains($path, '/admin/events') ? 'active' : '' ?>" href="<?= e(url('admin/events')) ?>"><i class="fa-solid fa-calendar"></i> Events</a>
        <a class="<?= str_contains($path, '/admin/contestants') ? 'active' : '' ?>" href="<?= e(url('admin/contestants')) ?>"><i class="fa-solid fa-users"></i> Contestants</a>
        <a class="<?= str_contains($path, '/admin/judges') ? 'active' : '' ?>" href="<?= e(url('admin/judges')) ?>"><i class="fa-solid fa-gavel"></i> Judges</a>
        <a class="<?= str_contains($path, '/admin/templates') ? 'active' : '' ?>" href="<?= e(url('admin/templates')) ?>"><i class="fa-solid fa-list-check"></i> Criteria templates</a>
        <a class="<?= str_contains($path, '/admin/results') ? 'active' : '' ?>" href="<?= e(url('admin/results')) ?>"><i class="fa-solid fa-ranking-star"></i> Results</a>
        <a class="<?= str_contains($path, '/admin/analytics') ? 'active' : '' ?>" href="<?= e(url('admin/analytics')) ?>"><i class="fa-solid fa-chart-column"></i> Analytics</a>
        <a class="<?= str_contains($path, '/profile') ? 'active' : '' ?>" href="<?= e(url('admin/profile')) ?>"><i class="fa-solid fa-user"></i> Profile</a>
      <?php else: ?>
        <a class="<?= preg_match('#/judge$#', $path) ? 'active' : '' ?>" href="<?= e(url('judge')) ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="<?= str_contains($path, '/judge/scores') ? 'active' : '' ?>" href="<?= e(url('judge/scores')) ?>"><i class="fa-solid fa-star"></i> My scores</a>
        <a class="<?= str_contains($path, '/profile') ? 'active' : '' ?>" href="<?= e(url('judge/profile')) ?>"><i class="fa-solid fa-user"></i> Profile</a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-foot">Signed in as <?= e($user['name'] ?? '') ?><br><?= e(ucfirst($user['role'] ?? '')) ?></div>
    <div class="festival-strip" aria-hidden="true"></div>
  </aside>
  <div class="main">
    <header class="topbar">
      <div class="topbar-actions">
        <button class="icon-btn mobile-toggle" type="button" data-sidebar-toggle aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        <h1><?= e($title ?? 'eTab') ?></h1>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" type="button" data-theme-toggle aria-label="Toggle color theme"><i class="fa-solid fa-moon"></i></button>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?>
          <button class="btn btn-outline btn-sm" type="submit">Log out</button>
        </form>
      </div>
    </header>
    <main id="main" class="content <?= e($content_class ?? '') ?>">
      <?php if ($msg = flash('error')): ?><div class="alert alert-error" role="alert"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('success')): ?><div class="alert alert-success" role="status"><?= e($msg) ?></div><?php endif; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<div class="toast-wrap" aria-live="polite">
  <?php if ($msg = flash('toast')): ?><div class="toast"><?= e($msg) ?></div><?php endif; ?>
</div>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php if (!empty($scripts)): foreach ($scripts as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
