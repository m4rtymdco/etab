<?php foreach ($winners as $w): ?>
  <div class="cert">
    <p>eTab Official Certificate</p>
    <h1>Award of Merit</h1>
    <p>This certifies that</p>
    <h2><?= e($w['name']) ?></h2>
    <p>placed <strong>#<?= (int)$w['rank'] ?></strong> in</p>
    <h3><?= e($standings['event']['name'] ?? '') ?></h3>
    <p>with a total of <strong><?= format_pct($w['average']) ?></strong></p>
    <p><?= e($w['category'] ?? '') ?> · <?= e(format_date($standings['event']['event_date'] ?? null)) ?></p>
  </div>
<?php endforeach; ?>
<?php if (!$winners): ?><p>No ranked winners yet.</p><?php endif; ?>
