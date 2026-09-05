<form class="filters" method="get" action="<?= e(url('admin/results')) ?>">
  <select name="event_id" onchange="this.form.submit()">
    <?php foreach ($events as $ev): ?>
      <option value="<?= (int)$ev['id'] ?>" <?= $event_id==(int)$ev['id']?'selected':'' ?>><?= e($ev['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="round">
    <?php $rounds = (int)(($standings['event']['rounds'] ?? 1)); for ($i=1;$i<=$rounds;$i++): ?>
      <option value="<?= $i ?>" <?= $round===$i?'selected':'' ?>>Round <?= $i ?></option>
    <?php endfor; ?>
  </select>
  <select name="category">
    <option value="">All categories</option>
    <option value="exclusive" <?= $category==='exclusive'?'selected':'' ?>>Exclusive</option>
    <option value="open" <?= $category==='open'?'selected':'' ?>>Open</option>
  </select>
  <button class="btn" type="submit">Filter</button>
</form>

<?php if ($progress): ?>
  <p>Progress <?= e($progress['percent']) ?>% · <?= (int)($standings['event']['results_published'] ?? 0) ? 'Published' : 'Hidden from judges' ?></p>
<?php endif; ?>

<div class="actions" style="margin-bottom:1rem">
  <a class="btn btn-outline" href="<?= e(url('admin/results/export.csv?event_id='.$event_id.'&round='.$round)) ?>">CSV</a>
  <a class="btn btn-outline" href="<?= e(url('admin/results/export.xls?event_id='.$event_id.'&round='.$round)) ?>">Excel</a>
  <a class="btn btn-outline" href="<?= e(url('admin/results/print?event_id='.$event_id.'&round='.$round)) ?>">PDF / Print</a>
  <a class="btn btn-outline" href="<?= e(url('admin/results/certificates?event_id='.$event_id.'&round='.$round)) ?>">Certificates</a>
  <?php if ($event_id): ?><a class="btn" href="<?= e(url('events/'.$event_id.'/live?round='.$round)) ?>">Live board</a><?php endif; ?>
</div>

<?php
$event_judges = $event_judges ?? [];
$groups = $standings['groups'] ?? [];
if (!$groups) {
    echo '<p class="muted">Select an event to view results.</p>';
}
foreach ($groups as $group):
    if (!$group['rows']) {
        continue;
    }
?>
  <h2 class="results-group-title"><?= e($group['label']) ?></h2>
  <div class="card table-wrap" style="margin-bottom:1.25rem">
    <table class="leaderboard">
      <thead>
        <tr>
          <th>Place</th>
          <th>Contestant</th>
          <th class="num">Total</th>
          <th class="num">%</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($group['rows'] as $r):
        $scored = (int) $r['judge_count'] > 0;
        $rank = $r['rank'] !== null ? (int) $r['rank'] : null;
      ?>
        <tr class="<?= ($rank && $rank <= 3 && $scored) ? 'rank-'.$rank : '' ?>">
          <td><?= place_markup($rank, $scored) ?></td>
          <td>
            <strong class="contestant-name"><?= e($r['name']) ?></strong>
            <small class="contestant-cat"><?= e($r['category'] ?? $group['label']) ?></small>
          </td>
          <td class="num"><strong><?= $scored ? format_score($r['score_sum'] ?? 0) : '—' ?></strong></td>
          <td class="num"><?php if ($scored): ?><span class="pct-pill"><?= format_pct($r['average'], 1) ?></span><?php else: ?>—<?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>

<?php
$criteria = $standings['criteria'] ?? [];
if ($groups && $event_judges && $criteria):
?>
  <h2 class="results-group-title">Judge scores</h2>
  <?php foreach ($groups as $group):
      if (!$group['rows']) {
          continue;
      }
  ?>
    <h3 class="judge-break-heading"><?= e($group['label']) ?></h3>
    <?php foreach ($group['rows'] as $r): ?>
      <div class="card table-wrap" style="margin-bottom:1rem">
        <div class="judge-break-head">
          <strong><?= e($r['name']) ?></strong>
          <span class="muted"><?= e($r['category'] ?? $group['label']) ?></span>
        </div>
        <table class="judge-break-table">
          <thead>
            <tr>
              <th>Criteria</th>
              <?php foreach ($event_judges as $j): ?>
                <th class="num"><?= e($j['name']) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($criteria as $c): ?>
              <tr>
                <td>
                  <?= e($c['name']) ?>
                  <small class="contestant-cat">Weight <?= e(rtrim(rtrim((string)$c['weight'], '0'), '.')) ?>%</small>
                </td>
                <?php foreach ($event_judges as $j):
                    $jid = (int) $j['id'];
                    $crid = (int) $c['id'];
                    $val = $r['judge_criteria'][$jid][$crid] ?? $r['judge_criteria'][(string)$jid][$crid] ?? null;
                ?>
                  <td class="num"><?= $val !== null ? format_score($val) : '—' ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
<?php endif; ?>
