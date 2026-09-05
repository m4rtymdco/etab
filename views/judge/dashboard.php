<div class="grid grid-3">
  <div class="card stat"><i class="fa-solid fa-calendar"></i><div><div class="n"><?= count($events) ?></div><div class="l">Assigned events</div></div></div>
  <div class="card stat"><i class="fa-solid fa-hourglass"></i><div><div class="n"><?= (int)$pending ?></div><div class="l">Pending sheets</div></div></div>
  <div class="card stat"><i class="fa-solid fa-check"></i><div><div class="n"><?= (int)$completed ?></div><div class="l">Completed sheets</div></div></div>
</div>
<div class="card table-wrap" style="margin-top:1rem">
  <h2>Upcoming judging tasks</h2>
  <table>
    <thead><tr><th>Event</th><th>Status</th><th>Progress</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($tasks as $t): $e=$t['event']; ?>
      <tr>
        <td><?= e($e['name']) ?></td>
        <td><span class="badge badge-<?= e($e['status']) ?>"><?= e(status_label($e['status'])) ?></span></td>
        <td><?= (int)$t['done'] ?> / <?= (int)$t['total'] ?></td>
        <td><a class="btn btn-sm" href="<?= e(url('judge/events/'.$e['id'])) ?>">Open</a>
          <?php if ((int)$e['results_published']): ?><a class="btn btn-sm btn-outline" href="<?= e(url('events/'.$e['id'].'/live')) ?>">Standings</a><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$tasks): ?><tr><td colspan="4">No events assigned yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
