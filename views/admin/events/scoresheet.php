<h1><?= e($event['name']) ?> — Judge score sheet</h1>
<p>Venue: <?= e($event['venue']) ?> · Date: <?= e(format_date($event['event_date'])) ?> · Range: <?= e($event['score_min']) ?>–<?= e($event['score_max']) ?></p>
<p>Judge: ______________________ &nbsp; Round: ______</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
  <thead>
    <tr>
      <th>#</th><th>Contestant</th><th>Category</th>
      <?php foreach ($criteria as $c): ?><th><?= e($c['name']) ?> (<?= e($c['weight']) ?>%)</th><?php endforeach; ?>
      <th>Total</th><th>Comments</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($contestants as $ct): ?>
      <tr>
        <td><?= e($ct['entry_number']) ?></td>
        <td><?= e($ct['name']) ?></td>
        <td><?= e(Contestant::normalizeDivision($ct['category'] ?? '')) ?></td>
        <?php foreach ($criteria as $c): ?><td style="height:36px"></td><?php endforeach; ?>
        <td></td><td></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p>Weighted total = sum(score × weight / 100). Sign: ________________</p>
