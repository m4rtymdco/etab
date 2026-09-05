(() => {
  const board = document.querySelector('[data-live-board]');
  if (!board) return;
  const url = board.dataset.url;
  const interval = parseInt(board.dataset.interval || '4000', 10);

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
  }

  function placeMarkup(rank, scored) {
    if (!scored || !rank) return '<span class="place-num">—</span>';
    if (rank <= 3) return `<span class="place-medal place-${rank}">${rank}</span>`;
    return `<span class="place-num">${rank}</span>`;
  }

  function formatScore(value) {
    const n = Number(value || 0);
    if (Math.abs(n - Math.round(n)) < 0.005) return String(Math.round(n));
    return n.toFixed(2);
  }

  function rowHtml(r) {
    const scored = Number(r.judge_count || 0) > 0;
    const rank = r.rank;
    const cls = rank && rank <= 3 && scored ? `rank-${rank}` : '';
    const cat = escapeHtml(r.category || '');
    const pct = Number(r.average || 0).toFixed(1) + '%';
    return `<tr class="${cls}">
      <td>${placeMarkup(rank, scored)}</td>
      <td><strong class="contestant-name">${escapeHtml(r.name)}</strong><small class="contestant-cat">${cat}</small></td>
      <td class="num"><strong>${scored ? formatScore(r.score_sum) : '—'}</strong></td>
      <td class="num">${scored ? `<span class="pct-pill">${pct}</span>` : '—'}</td>
    </tr>`;
  }

  function render(data) {
    const groups = data.groups && data.groups.length
      ? data.groups
      : [{ key: 'all', rows: data.rows || [] }];
    groups.forEach((g) => {
      const tb = board.querySelector(`tbody[data-group="${g.key}"]`);
      if (!tb) return;
      tb.innerHTML = g.rows.length
        ? g.rows.map(rowHtml).join('')
        : '<tr><td colspan="4" class="muted">No contestants in this category.</td></tr>';
    });
    const stamp = board.querySelector('[data-updated]');
    if (stamp) stamp.textContent = new Date(data.updated_at).toLocaleTimeString();
  }

  async function tick() {
    try {
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      if (res.ok) render(await res.json());
    } catch (e) { /* ignore */ }
  }
  setInterval(tick, interval);
})();
