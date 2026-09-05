<div class="page-head">
  <h2>Events</h2>
  <a class="btn" href="<?= e(url('admin/events/create')) ?>"><i class="fa-solid fa-plus"></i> New event</a>
</div>
<div class="card table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Date</th><th>Venue</th><th>Status</th><th>Results</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($events as $e): ?>
      <tr>
        <td><a href="<?= e(url('admin/events/'.$e['id'])) ?>"><?= e($e['name']) ?></a></td>
        <td><?= e(format_date($e['event_date'])) ?> <?= e(substr((string)$e['event_time'],0,5)) ?></td>
        <td><?= e($e['venue']) ?></td>
        <td><span class="badge badge-<?= e($e['status']) ?>"><?= e(status_label($e['status'])) ?></span></td>
        <td><?= (int)$e['results_published'] ? 'Published' : 'Hidden' ?></td>
        <td class="actions">
          <a class="btn btn-sm btn-outline" href="<?= e(url('admin/events/'.$e['id'].'/edit')) ?>">Edit</a>
          <form method="post" action="<?= e(url('admin/events/'.$e['id'].'/delete')) ?>" data-confirm="Delete this event and all related scores?">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$events): ?><tr><td colspan="6">No events.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
