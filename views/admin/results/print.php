<h1><?= e($standings['event']['name'] ?? 'Results') ?> — Round <?= (int)$round ?></h1>
<p>Generated <?= e(date('M j, Y g:i A')) ?></p>
<?php foreach (($standings['groups'] ?? []) as $group):
    if (!$group['rows']) {
        continue;
    }
?>
  <h2><?= e($group['label']) ?></h2>
  <table border="1" cellpadding="6" cellspacing="0" width="100%">
    <thead>
      <tr>
        <th>Place</th>
        <th>Contestant</th>
        <th>Category</th>
        <th>Total</th>
        <th>%</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($group['rows'] as $r):
      $scored = (int) $r['judge_count'] > 0;
    ?>
      <tr>
        <td><?= e((string)($r['rank'] ?? '')) ?></td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['category'] ?? $group['label']) ?></td>
        <td><?= $scored ? format_score($r['score_sum'] ?? 0) : '' ?></td>
        <td><?= $scored ? format_pct($r['average'], 1) : '' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>
