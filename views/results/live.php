<div data-live-board data-url="<?= e($poll_url) ?>" data-interval="<?= (int)$interval ?>">
  <p class="muted">Auto-refreshing · last update <span data-updated>—</span></p>
  <?php foreach (($standings['groups'] ?? []) as $group): ?>
    <h2 class="results-group-title"><?= e($group['label']) ?></h2>
    <div class="card table-wrap" style="margin-bottom:1.25rem">
      <table class="leaderboard">
        <thead>
          <tr>
            <th>Place</th>
            <th>Contestant</th>
            <th class="num">Total</th>
            <th class="num">%</th>
          </tr>
        </thead>
        <tbody data-group="<?= e($group['key']) ?>">
        <?php foreach ($group['rows'] as $r):
          $scored = (int) $r['judge_count'] > 0;
          $rank = $r['rank'] !== null ? (int) $r['rank'] : null;
        ?>
          <tr class="<?= ($rank && $rank <= 3 && $scored) ? 'rank-'.$rank : '' ?>">
            <td><?= place_markup($rank, $scored) ?></td>
            <td>
              <strong class="contestant-name"><?= e($r['name']) ?></strong>
              <small class="contestant-cat"><?= e($r['category'] ?? $group['label']) ?></small>
            </td>
            <td class="num"><strong><?= $scored ? format_score($r['score_sum'] ?? 0) : '—' ?></strong></td>
            <td class="num"><?php if ($scored): ?><span class="pct-pill"><?= format_pct($r['average'], 1) ?></span><?php else: ?>—<?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$group['rows']): ?>
          <tr><td colspan="4" class="muted">No contestants in this category.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
</div>
