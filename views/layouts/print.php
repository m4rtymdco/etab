<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($title ?? 'Print') ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
  <style>body{background:#fff;color:#111;padding:1.5rem;} table{font-size:12px;}</style>
</head>
<body>
  <p class="no-print"><button onclick="window.print()">Print / Save as PDF</button> · <a href="javascript:history.back()">Back</a></p>
  <?= $content ?>
</body>
</html>
