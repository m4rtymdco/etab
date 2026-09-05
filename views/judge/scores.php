<?php foreach ($groups as $block): ?>
  <div class="card" style="margin-bottom:1rem">
    <h2><?= e($block['event']['name']) ?></h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Contestant</th>
            <?php foreach ($block['criteria'] as $c): ?><th><?= e($c['name']) ?></th><?php endforeach; ?>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($block['rows'] as $row): ?>
          <tr>
            <td><?= e($row['contestant']['name']) ?></td>
            <?php
              $map=[];
              foreach ($row['scores'] as $s) { $map[$s['criteria_id']]=$s['score_value']; }
              foreach ($block['criteria'] as $c): ?>
              <td><?= e((string)($map[$c['id']] ?? '')) ?></td>
            <?php endforeach; ?>
            <td><strong><?= format_pct($row['total']) ?></strong></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$block['rows']): ?><tr><td colspan="10">No submitted scores for this event.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$groups): ?><p>No assigned events.</p><?php endif; ?>
