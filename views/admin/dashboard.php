<div class="grid grid-4">
  <div class="card stat"><i class="fa-solid fa-calendar"></i><div><div class="n"><?= (int)$stats['events'] ?></div><div class="l">Events</div></div></div>
  <div class="card stat"><i class="fa-solid fa-users"></i><div><div class="n"><?= (int)$stats['contestants'] ?></div><div class="l">Contestants</div></div></div>
  <div class="card stat"><i class="fa-solid fa-gavel"></i><div><div class="n"><?= (int)$stats['judges'] ?></div><div class="l">Active judges</div></div></div>
  <div class="card stat"><i class="fa-solid fa-star"></i><div><div class="n"><?= (int)$stats['scores'] ?></div><div class="l">Score rows</div></div></div>
</div>

<div class="grid grid-2" style="margin-top:1rem">
  <div class="card">
    <h2>Events</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Status</th><th>Progress</th></tr></thead>
        <tbody>
        <?php foreach ($events as $e): $p = $progress[$e['id']] ?? null; ?>
          <tr>
            <td><a href="<?= e(url('admin/events/'.$e['id'])) ?>"><?= e($e['name']) ?></a></td>
            <td><span class="badge badge-<?= e($e['status']) ?>"><?= e(status_label($e['status'])) ?></span></td>
            <td>
              <?php if ($p): ?>
                <div class="progress" title="<?= e($p['completed_sheets'].'/'.$p['expected_sheets']) ?>"><span style="width:<?= (float)$p['percent'] ?>%"></span></div>
                <span class="muted"><?= e($p['percent']) ?>%</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$events): ?><tr><td colspan="3">No events yet. <a href="<?= e(url('admin/events/create')) ?>">Create one</a>.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <h2>Recent scores</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>When</th><th>Judge</th><th>Contestant</th><th>Score</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $s): ?>
          <tr>
            <td><?= e(format_dt($s['updated_at'])) ?></td>
            <td><?= e($s['judge_name']) ?></td>
            <td><?= e($s['contestant_name']) ?></td>
            <td><?= e($s['score_value']) ?> <span class="muted"><?= e($s['criteria_name']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="4">No scores yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
