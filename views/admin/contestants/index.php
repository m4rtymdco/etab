<div class="page-head">
  <h2>Contestants</h2>
  <div class="actions">
    <a class="btn" href="<?= e(url('admin/contestants/create')) ?>">Add</a>
    <a class="btn btn-outline" href="<?= e(url('admin/contestants/export')) ?>">Export CSV</a>
  </div>
</div>
<div class="card" style="margin-bottom:1rem">
  <form method="post" action="<?= e(url('admin/contestants/import')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label>Import CSV (columns: name, category, status, notes, event_id, entry_number). Category must be Exclusive or Open.</label>
    <div class="actions">
      <input type="file" name="csv" accept=".csv,text/csv" required>
      <select name="event_id">
        <option value="0">No default event</option>
        <?php foreach ($events as $ev): ?><option value="<?= (int)$ev['id'] ?>"><?= e($ev['name']) ?></option><?php endforeach; ?>
      </select>
      <button class="btn" type="submit">Import</button>
    </div>
  </form>
</div>
<div class="card table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Category</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($contestants as $c): ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= e(Contestant::normalizeDivision($c['category'] ?? '')) ?></td>
        <td><span class="badge badge-<?= e($c['status']) ?>"><?= e(status_label($c['status'])) ?></span></td>
        <td class="actions">
          <a class="btn btn-sm btn-outline" href="<?= e(url('admin/contestants/'.$c['id'].'/edit')) ?>">Edit</a>
          <?php if ($c['status']==='active'): ?>
          <form method="post" action="<?= e(url('admin/contestants/'.$c['id'].'/archive')) ?>"><?= csrf_field() ?><button class="btn btn-sm" type="submit">Archive</button></form>
          <?php endif; ?>
          <form method="post" action="<?= e(url('admin/contestants/'.$c['id'].'/delete')) ?>" data-confirm="Permanently delete?"><?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
