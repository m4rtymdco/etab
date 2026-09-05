<div class="page-head">
  <h2>Judges</h2>
  <a class="btn" href="<?= e(url('admin/judges/create')) ?>">Add judge</a>
</div>
<div class="card table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Events</th><th>Sheets</th><th>Avg score</th><th>Active</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($judges as $j): $m = $metrics[$j['id']]; ?>
      <tr>
        <td><?= e($j['name']) ?></td>
        <td><?= e($j['email']) ?></td>
        <td><?= (int)$m['assigned_events'] ?></td>
        <td><?= (int)$m['sheets'] ?></td>
        <td><?= $m['avg_score'] !== null ? e((string)$m['avg_score']) : '—' ?></td>
        <td><?= (int)$j['is_active'] ? 'Yes' : 'No' ?></td>
        <td class="actions">
          <a class="btn btn-sm btn-outline" href="<?= e(url('admin/judges/'.$j['id'].'/edit')) ?>">Edit</a>
          <form method="post" action="<?= e(url('admin/judges/'.$j['id'].'/delete')) ?>" data-confirm="Remove this judge?">
            <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
