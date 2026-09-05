<?php $e = $event ?? []; ?>
<div class="card" style="max-width:820px">
  <form method="post" action="<?= e($event ? url('admin/events/'.$event['id']) : url('admin/events')) ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label for="name">Event name</label>
      <input id="name" name="name" required value="<?= e($e['name'] ?? '') ?>"></div>
    <div class="form-group"><label for="description">Description</label>
      <textarea id="description" name="description" rows="3"><?= e($e['description'] ?? '') ?></textarea></div>
    <div class="grid grid-2">
      <div class="form-group"><label for="event_date">Date</label>
        <input id="event_date" type="date" name="event_date" value="<?= e($e['event_date'] ?? '') ?>"></div>
      <div class="form-group"><label for="event_time">Time</label>
        <input id="event_time" type="time" name="event_time" value="<?= e(substr((string)($e['event_time'] ?? ''),0,5)) ?>"></div>
    </div>
    <div class="form-group"><label for="venue">Venue</label>
      <input id="venue" name="venue" value="<?= e($e['venue'] ?? '') ?>"></div>
    <div class="grid grid-3">
      <div class="form-group"><label for="status">Status</label>
        <select id="status" name="status">
          <?php foreach (['upcoming','ongoing','completed'] as $st): ?>
            <option value="<?= $st ?>" <?= (($e['status'] ?? '')===$st)?'selected':'' ?>><?= status_label($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label for="score_min">Min score</label>
        <input id="score_min" type="number" step="0.1" name="score_min" value="<?= e($e['score_min'] ?? '1') ?>"></div>
      <div class="form-group"><label for="score_max">Max score</label>
        <input id="score_max" type="number" step="0.1" name="score_max" value="<?= e($e['score_max'] ?? '100') ?>"></div>
    </div>
    <div class="grid grid-2">
      <div class="form-group"><label for="rounds">Rounds</label>
        <input id="rounds" type="number" min="1" name="rounds" value="<?= e($e['rounds'] ?? '1') ?>"></div>
      <div class="form-group"><label><input type="checkbox" name="drop_high_low" value="1" <?= !empty($e['drop_high_low'])?'checked':'' ?>> Drop highest & lowest judge totals</label>
        <label><input type="checkbox" name="results_published" value="1" <?= !empty($e['results_published'])?'checked':'' ?>> Publish results</label></div>
    </div>
    <div class="form-group">
      <label>Assign judges</label>
      <div class="check-list">
        <?php foreach ($judges as $j): ?>
          <label><input type="checkbox" name="judge_ids[]" value="<?= (int)$j['id'] ?>" <?= in_array((int)$j['id'], $assigned, true)?'checked':'' ?>> <?= e($j['name']) ?> (<?= e($j['email']) ?>)</label>
        <?php endforeach; ?>
        <?php if (!$judges): ?><span class="muted">No judges yet. Add judges first.</span><?php endif; ?>
      </div>
    </div>
    <button class="btn" type="submit">Save event</button>
    <a class="btn btn-ghost" href="<?= e(url('admin/events')) ?>">Cancel</a>
  </form>
</div>
