<form class="filters" method="get">
  <select name="event_id" onchange="this.form.submit()">
    <?php foreach ($events as $ev): ?>
      <option value="<?= (int)$ev['id'] ?>" <?= $event_id==(int)$ev['id']?'selected':'' ?>><?= e($ev['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>
<div class="grid grid-2">
  <div class="card">
    <h2>Score distribution</h2>
    <canvas id="distChart" height="180"></canvas>
  </div>
  <div class="card">
    <h2>Judge scoring patterns</h2>
    <canvas id="judgeChart" height="180"></canvas>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Judge</th><th>Avg</th><th>Stdev</th><th>N</th></tr></thead>
        <tbody>
        <?php foreach ($judge_patterns as $p): ?>
          <tr><td><?= e($p['name']) ?></td><td><?= number_format((float)$p['avg_score'],2) ?></td><td><?= number_format((float)$p['stdev'],2) ?></td><td><?= (int)$p['n'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="card" style="margin-top:1rem">
  <h2>Contestant totals</h2>
  <canvas id="rankChart" height="140"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const dist = <?= json_encode($distribution) ?>;
const judges = <?= json_encode($judge_patterns) ?>;
const rows = <?= json_encode(array_map(fn($r)=>['name'=>$r['name'],'average'=>(float)$r['average']], $standings['rows'])) ?>;
new Chart(document.getElementById('distChart'), {
  type: 'bar',
  data: { labels: Object.keys(dist), datasets: [{ label: 'Contestants', data: Object.values(dist), backgroundColor: ['#5b2c90','#007dc5','#ffcc00','#4db848','#e31c23','#ec008c','#ff9800','#00bcd4','#5b2c90','#007dc5'] }] },
  options: { plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('judgeChart'), {
  type: 'bar',
  data: { labels: judges.map(j=>j.name), datasets: [{ label: 'Average score given', data: judges.map(j=>Number(j.avg_score)), backgroundColor: ['#5b2c90','#ec008c','#007dc5','#4db848','#ffcc00'] }] }
});
new Chart(document.getElementById('rankChart'), {
  type: 'bar',
  data: { labels: rows.map(r=>r.name), datasets: [{ label: 'Weighted total', data: rows.map(r=>r.average), backgroundColor: ['#5b2c90','#007dc5','#ec008c','#4db848','#ffcc00','#e31c23','#ff9800','#00bcd4'] }] },
  options: { indexAxis: 'y' }
});
</script>
