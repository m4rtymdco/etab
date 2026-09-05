<?php
$c = $contestant ?? [];
$div = Contestant::divisionKey($c['category'] ?? 'open');
?>
<div class="card" style="max-width:720px">
  <form method="post" enctype="multipart/form-data" action="<?= e($contestant ? url('admin/contestants/'.$contestant['id']) : url('admin/contestants')) ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label for="name">Name</label>
      <input id="name" name="name" required value="<?= e($c['name'] ?? '') ?>"></div>
    <div class="form-group">
      <span>Category</span>
      <div class="choice-row" role="radiogroup" aria-label="Category">
        <label class="<?= $div === 'exclusive' ? 'is-selected' : '' ?>">
          <input type="radio" name="category" value="exclusive" <?= $div === 'exclusive' ? 'checked' : '' ?> required>
          Exclusive
        </label>
        <label class="<?= $div === 'open' ? 'is-selected' : '' ?>">
          <input type="radio" name="category" value="open" <?= $div === 'open' ? 'checked' : '' ?> required>
          Open
        </label>
      </div>
    </div>
    <div class="form-group"><label for="status">Status</label>
      <select id="status" name="status">
        <option value="active" <?= (($c['status'] ?? 'active')==='active')?'selected':'' ?>>Active</option>
        <option value="archived" <?= (($c['status'] ?? '')==='archived')?'selected':'' ?>>Archived</option>
      </select>
    </div>
    <div class="form-group"><label for="notes">Notes</label>
      <textarea id="notes" name="notes"><?= e($c['notes'] ?? '') ?></textarea></div>
    <div class="form-group"><label for="photo">Photo</label>
      <input id="photo" type="file" name="photo" accept="image/*">
      <?php if (!empty($c['photo_url'])): ?><p><img src="<?= e($c['photo_url']) ?>" alt="" width="80"></p><?php endif; ?>
    </div>
    <div class="form-group">
      <label>Events</label>
      <div class="check-list">
        <?php foreach ($events as $ev): ?>
          <label><input type="checkbox" name="event_ids[]" value="<?= (int)$ev['id'] ?>" <?= in_array((int)$ev['id'], $assigned_events, true)?'checked':'' ?>> <?= e($ev['name']) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="btn" type="submit">Save</button>
    <a href="<?= e(url('admin/contestants')) ?>">Cancel</a>
  </form>
</div>
