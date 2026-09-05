<?php $j = $judge ?? []; ?>
<div class="card" style="max-width:720px">
  <?php if (!empty($metrics)): ?>
    <p class="muted">Performance: <?= (int)$metrics['sheets'] ?> sheets · avg <?= e((string)($metrics['avg_score'] ?? '—')) ?> · range <?= e((string)($metrics['min_score'] ?? '—')) ?>–<?= e((string)($metrics['max_score'] ?? '—')) ?></p>
  <?php endif; ?>
  <form method="post" action="<?= e($judge ? url('admin/judges/'.$judge['id']) : url('admin/judges')) ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label for="name">Name</label>
      <input id="name" name="name" required value="<?= e($j['name'] ?? '') ?>"></div>
    <div class="form-group"><label for="email">Email</label>
      <input id="email" type="email" name="email" required value="<?= e($j['email'] ?? '') ?>"></div>
    <div class="form-group"><label for="password">Password <?= $judge ? '(leave blank to keep)' : '' ?></label>
      <input id="password" type="password" name="password" <?= $judge ? '' : 'required minlength="8"' ?>></div>
    <div class="form-group"><label for="phone">Phone</label>
      <input id="phone" name="phone" value="<?= e($j['phone'] ?? '') ?>"></div>
    <div class="form-group"><label for="bio">Bio</label>
      <textarea id="bio" name="bio"><?= e($j['bio'] ?? '') ?></textarea></div>
    <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?= empty($j) || !empty($j['is_active']) ? 'checked' : '' ?>> Active</label></div>
    <div class="form-group">
      <label>Assign to events</label>
      <div class="check-list">
        <?php foreach ($events as $ev): ?>
          <label><input type="checkbox" name="event_ids[]" value="<?= (int)$ev['id'] ?>" <?= in_array((int)$ev['id'], $assigned, true)?'checked':'' ?>> <?= e($ev['name']) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="btn" type="submit">Save</button>
  </form>
</div>
