<?php
$weights = [];
foreach ($criteria as $c) {
    $weights[$c['id']] = (float) $c['weight'];
}
$readonly = !empty($locked);
$vals = $values ?? [];
if (!$readonly && !empty($draft['scores'])) {
    $vals = $draft['scores'] + $vals;
}
$selectedId = (int) ($contestant['id'] ?? 0);
$eventMax = (float) ($event['score_max'] ?? 100) ?: 100;
$grouped = ['exclusive' => [], 'open' => []];
foreach ($rows as $r) {
    $grouped[Contestant::divisionKey($r['contestant']['category'] ?? '')][] = $r;
}
$divLabel = $contestant ? Contestant::normalizeDivision($contestant['category'] ?? '') : '';
?>
<div class="score-board">
  <aside class="part-pane">
    <?php foreach (['exclusive' => 'Exclusive', 'open' => 'Open'] as $key => $label): ?>
      <div class="part-group">
        <div class="part-head"><?= e($label) ?> (<?= count($grouped[$key]) ?>)</div>
        <?php foreach ($grouped[$key] as $r):
          $c = $r['contestant'];
          $active = (int) $c['id'] === $selectedId;
          $sub = trim((string) ($c['notes'] ?? ''));
        ?>
          <a class="part-card<?= $active ? ' is-active' : '' ?>" href="<?= e(url('judge/events/'.$event['id'].'?cid='.$c['id'].'&round='.$round)) ?>">
            <span>
              <strong><?= e($c['name']) ?></strong>
              <?php if ($sub !== ''): ?><small><?= e($sub) ?></small><?php endif; ?>
            </span>
            <?php if ($r['submitted']): ?><i class="fa-solid fa-circle-check" aria-label="Scored"></i><?php endif; ?>
          </a>
        <?php endforeach; ?>
        <?php if (!$grouped[$key]): ?><p class="muted">None</p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </aside>

  <section class="score-pane">
    <?php if (!$contestant): ?>
      <p class="muted">Select a participant to begin scoring.</p>
    <?php elseif (!$criteria): ?>
      <h2><?= e($contestant['name']) ?></h2>
      <div class="alert alert-error">This event has no scoring criteria yet. Ask an admin to add criteria or apply a template.</div>
    <?php else: ?>
      <form method="post" action="<?= e(url('judge/events/'.$event['id'].'/contestants/'.$contestant['id'])) ?>"
            data-scoring-form
            data-round="<?= (int) $round ?>"
            data-readonly="<?= $readonly ? '1' : '0' ?>"
            data-submitted="<?= !empty($submitted) ? '1' : '0' ?>"
            data-draft-url="<?= e(url('judge/events/'.$event['id'].'/contestants/'.$contestant['id'].'/draft')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="round" value="<?= (int) $round ?>">
        <input type="hidden" name="comments" value="<?= e($comments ?? '') ?>">
        <script type="application/json" id="score-weights"><?= json_encode($weights) ?></script>

        <header class="score-head">
          <div>
            <h2><?= e($contestant['name']) ?></h2>
            <p><?= e($divLabel) ?><?= !empty($contestant['notes']) ? ' · '.e($contestant['notes']) : '' ?></p>
          </div>
          <div class="score-stats">
            <div class="pct-box">
              <span>Total</span>
              <strong data-live-pct>0%</strong>
            </div>
          </div>
        </header>
        <?php if (!empty($submitted) && $readonly): ?>
          <p class="score-locked-note"><i class="fa-solid fa-lock"></i> Scores saved and locked</p>
        <?php endif; ?>

        <?php foreach ($criteria as $c):
          $cmax = (float) ($c['max_score'] ?? 0);
          if ($cmax <= 0) {
              $cmax = $eventMax;
          }
          $cur = $vals[$c['id']] ?? $vals[(string) $c['id']] ?? '0';
        ?>
          <div class="crit-row">
            <div class="crit-copy">
              <div class="crit-tags">
                <?php if (!empty($c['section'])): ?><span class="tag-section"><?= e($c['section']) ?></span><?php endif; ?>
                <span class="tag-weight">Weight: <?= e(rtrim(rtrim((string) $c['weight'], '0'), '.')) ?>%</span>
              </div>
              <strong><?= e($c['name']) ?></strong>
              <?php if (!empty($c['description'])): ?><p><?= e($c['description']) ?></p><?php endif; ?>
            </div>
            <div class="stepper">
              <button type="button" class="step-btn" data-step="-1" <?= $readonly ? 'disabled' : '' ?> aria-label="Decrease">−</button>
              <input type="number" step="1" min="0" max="<?= e((string) $cmax) ?>"
                     name="scores[<?= (int) $c['id'] ?>]" data-criteria-id="<?= (int) $c['id'] ?>"
                     inputmode="numeric" value="<?= e((string) $cur) ?>" <?= $readonly ? 'readonly' : '' ?> required>
              <button type="button" class="step-btn" data-step="1" <?= $readonly ? 'disabled' : '' ?> aria-label="Increase">+</button>
              <span class="max-label">/ <?= e(rtrim(rtrim((string) $cmax, '0'), '.')) ?></span>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="score-actions">
          <?php if ($readonly): ?>
            <a class="btn btn-outline edit-scores" href="<?= e(url('judge/events/'.$event['id'].'?cid='.$contestant['id'].'&round='.$round.'&edit=1')) ?>">
              <i class="fa-solid fa-pen"></i> Edit
            </a>
          <?php else: ?>
            <button class="save-scores" type="submit">
              <i class="fa-solid fa-floppy-disk"></i> <?= !empty($submitted) ? 'Save updates' : 'Save '.e($contestant['name'])."'s scores" ?>
            </button>
          <?php endif; ?>
        </div>
      </form>
      <?php if (!empty($submitted)): ?>
      <div class="finalize-modal" data-finalize-modal hidden>
        <div class="finalize-dialog" role="dialog" aria-modal="true" aria-labelledby="finalize-title">
          <h3 id="finalize-title">Ready to finalize?</h3>
          <p>Your updates will be published immediately</p>
          <div class="finalize-actions">
            <button type="button" class="btn btn-outline" data-finalize-cancel>Cancel</button>
            <button type="button" class="btn" data-finalize-confirm>Save</button>
          </div>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
<script src="<?= e(asset('js/scoring.js')) ?>"></script>
