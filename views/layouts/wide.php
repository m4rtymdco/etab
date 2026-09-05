<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title ?? 'Live standings') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div class="festival-strip" aria-hidden="true"></div>
<header class="topbar">
  <h1><?= e($title ?? '') ?></h1>
  <div class="topbar-actions">
    <button class="icon-btn" type="button" data-theme-toggle aria-label="Toggle theme"><i class="fa-solid fa-moon"></i></button>
    <a class="btn btn-sm" href="<?= e(url(is_admin() ? 'admin/results' : 'judge')) ?>">Back</a>
  </div>
</header>
<main class="content live-board"><?= $content ?></main>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<script src="<?= e(asset('js/live.js')) ?>"></script>
</body>
</html>
