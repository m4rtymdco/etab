<div class="page-head">
  <div>
    <h2><?= e($event['name']) ?></h2>
    <p class="muted"><?= e(format_date($event['event_date'])) ?> · <?= e($event['venue']) ?>
      · <span class="badge badge-<?= e($event['status']) ?>"><?= e(status_label($event['status'])) ?></span>
      · Results <?= (int)$event['results_published'] ? 'published' : 'hidden' ?>
    </p>
  </div>
  <div class="actions">
    <a class="btn btn-outline" href="<?= e(url('admin/events/'.$event['id'].'/edit')) ?>">Edit</a>
    <a class="btn btn-outline" href="<?= e(url('admin/events/'.$event['id'].'/scoresheet')) ?>">Print score sheets</a>
    <a class="btn" href="<?= e(url('events/'.$event['id'].'/live')) ?>">Live board</a>
  </div>
</div>

<div class="card" style="margin-bottom:1rem">
  <p>Scoring progress: <strong><?= e($progress['completed_sheets']) ?></strong> / <?= e($progress['expected_sheets']) ?> sheets (<?= e($progress['percent']) ?>%)</p>
  <div class="progress"><span style="width:<?= (float)$progress['percent'] ?>%"></span></div>
  <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/publish')) ?>" style="margin-top:1rem">
    <?= csrf_field() ?>
    <?php if ((int)$event['results_published']): ?>
      <button class="btn btn-outline" type="submit">Unpublish results</button>
    <?php else: ?>
      <input type="hidden" name="published" value="1">
      <button class="btn btn-success" type="submit">Publish results</button>
    <?php endif; ?>
  </form>
</div>

<div class="grid grid-2">
  <div class="card">
    <h3>Criteria <span class="muted">(weights: <?= e($weights_total) ?>% <?= $weights_total==100?'✓':'— must total 100%' ?>)</span></h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>%</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($criteria as $c): ?>
          <tr>
            <td>
              <form method="post" action="<?= e(url('admin/criteria/'.$c['id'])) ?>" class="grid grid-2">
                <?= csrf_field() ?>
                <input name="name" value="<?= e($c['name']) ?>" aria-label="Criterion name">
                <input name="section" value="<?= e($c['section'] ?? '') ?>" placeholder="Section" aria-label="Section">
                <input name="weight" type="number" step="0.01" value="<?= e($c['weight']) ?>" aria-label="Weight">
                <input name="max_score" type="number" step="0.1" value="<?= e($c['max_score'] ?? '100') ?>" placeholder="Max score" aria-label="Max score">
                <input name="description" value="<?= e($c['description']) ?>" placeholder="Description">
                <input name="sort_order" type="number" value="<?= e($c['sort_order']) ?>" aria-label="Display order">
                <button class="btn btn-sm" type="submit">Save</button>
              </form>
            </td>
            <td><?= e($c['weight']) ?></td>
            <td>
              <form method="post" action="<?= e(url('admin/criteria/'.$c['id'].'/delete')) ?>" data-confirm="Delete this criterion?">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/criteria')) ?>" style="margin-top:1rem">
      <?= csrf_field() ?>
      <div class="grid grid-2">
        <input name="section" placeholder="Section">
        <input name="name" placeholder="Criterion name" required>
        <input name="description" placeholder="Description">
        <input name="max_score" type="number" step="0.1" placeholder="Max score" value="100">
        <input name="weight" type="number" step="0.01" placeholder="Weight %" required>
        <input name="sort_order" type="number" placeholder="Display order" value="1">
        <button class="btn" type="submit">Add New Criterion</button>
      </div>
    </form>
    <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/criteria/template')) ?>" style="margin-top:.8rem">
      <?= csrf_field() ?>
      <label>Apply template</label>
      <div class="actions">
        <select name="template_id" required>
          <option value="">Select…</option>
          <?php foreach ($templates as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit">Apply</button>
      </div>
    </form>
    <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/criteria/save-template')) ?>">
      <?= csrf_field() ?>
      <div class="actions" style="margin-top:.5rem">
        <input name="template_name" placeholder="Save current as template name">
        <button class="btn btn-ghost" type="submit">Save as template</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h3>Judges</h3>
    <ul>
      <?php foreach ($judges as $j): ?><li><?= e($j['name']) ?> — <?= e($j['email']) ?></li><?php endforeach; ?>
      <?php if (!$judges): ?><li class="muted">None assigned. Edit the event to assign judges.</li><?php endif; ?>
    </ul>
    <h3>Contestants</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Category</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($contestants as $c): ?>
          <tr>
            <td><?= e($c['entry_number']) ?></td>
            <td><?= e($c['name']) ?></td>
            <td><?= e(Contestant::normalizeDivision($c['category'] ?? '')) ?></td>
            <td>
              <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/unassign-contestant')) ?>" data-confirm="Remove from this event?">
                <?= csrf_field() ?>
                <input type="hidden" name="contestant_id" value="<?= (int)$c['id'] ?>">
                <button class="btn btn-sm btn-outline" type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <form method="post" action="<?= e(url('admin/events/'.$event['id'].'/assign-contestant')) ?>">
      <?= csrf_field() ?>
      <div class="grid grid-3" style="margin-top:.8rem">
        <select name="contestant_id" required>
          <option value="">Assign existing…</option>
          <?php foreach ($all_contestants as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?> (<?= e(Contestant::normalizeDivision($c['category'] ?? '')) ?>)</option>
          <?php endforeach; ?>
        </select>
        <input name="entry_number" placeholder="Entry #">
        <button class="btn" type="submit">Assign</button>
      </div>
    </form>
  </div>
</div>

<form method="post" action="<?= e(url('admin/events/'.$event['id'].'/delete')) ?>" data-confirm="Delete this event and all related scores?" style="margin-top:1rem">
  <?= csrf_field() ?>
  <button class="btn btn-danger" type="submit">Delete event</button>
</form>
