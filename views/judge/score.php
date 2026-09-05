<?php
$weights = [];
foreach ($criteria as $c) { $weights[$c['id']] = (float)$c['weight']; }
$readonly = !empty($submitted);
$vals = $values;
if (!$readonly && !empty($draft['scores'])) {
  $vals = $draft['scores'] + $vals;
  $comments = $draft['comments'] ?? $comments;
}
?>
<div class="card" style="max-width:760px">
  <h2><?= e($contestant['name']) ?></h2>
  <p class="muted"><?= e($contestant['category']) ?> · <?= e($event['name']) ?> · Round <?= (int)$round ?></p>
  <?php if ($readonly): ?><div class="alert alert-success">Submitted — read only. You cannot see other judges’ scores.</div><?php endif; ?>
  <p><strong>Live total: <span data-live-total>0.00</span></strong> <span class="muted" data-draft-status></span></p>
  <form method="post" action="<?= e(url('judge/events/'.$event['id'].'/contestants/'.$contestant['id'])) ?>"
        data-scoring-form
        data-min="<?= e($event['score_min']) ?>"
        data-max="<?= e($event['score_max']) ?>"
        data-round="<?= (int)$round ?>"
        data-readonly="<?= $readonly ? '1' : '0' ?>"
        data-weights='<?= e(json_encode($weights)) ?>'
        data-draft-url="<?= e(url('judge/events/'.$event['id'].'/contestants/'.$contestant['id'].'/draft')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="round" value="<?= (int)$round ?>">
    <?php
      $lastSection = null;
      foreach ($criteria as $c):
        $section = $c['section'] ?? '';
        $cmax = $c['max_score'] ?? $event['score_max'];
        if ($section !== '' && $section !== $lastSection):
          $lastSection = $section;
    ?>
      <h3><?= e($section) ?></h3>
    <?php endif; ?>
      <div class="form-group">
        <label for="c<?= (int)$c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['weight']) ?>%) · Max <?= e((string)$cmax) ?>
          <?php if ($c['description']): ?><span class="help"><?= e($c['description']) ?></span><?php endif; ?></label>
        <input id="c<?= (int)$c['id'] ?>" type="number" step="0.1" min="<?= e($event['score_min']) ?>" max="<?= e((string)$cmax) ?>"
               name="scores[<?= (int)$c['id'] ?>]" data-criteria-id="<?= (int)$c['id'] ?>"
               value="<?= e((string)($vals[$c['id']] ?? '')) ?>" <?= $readonly?'readonly':'' ?> required>
      </div>
    <?php endforeach; ?>
    <div class="form-group">
      <label for="comments">Comments / feedback</label>
      <textarea id="comments" name="comments" <?= $readonly?'readonly':'' ?>><?= e($comments) ?></textarea>
    </div>
    <?php if (!$readonly): ?>
      <label><input type="checkbox" name="confirm" value="1" required> I confirm these scores are final</label>
      <p><button class="btn" type="submit">Submit scores</button></p>
    <?php endif; ?>
    <a href="<?= e(url('judge/events/'.$event['id'].'?round='.$round)) ?>">Back to list</a>
  </form>
</div>
<script src="<?= e(asset('js/scoring.js')) ?>"></script>
