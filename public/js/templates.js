(() => {
  const list = document.getElementById('criteria-list');
  const tpl = document.getElementById('criterion-row-tpl');
  const empty = document.getElementById('criteria-empty');
  const addBtn = document.querySelector('[data-add-criterion]');
  const totalEl = document.querySelector('[data-weight-total]');
  if (!list || !tpl || !addBtn) return;

  function count() {
    return list.querySelectorAll('.criterion-block').length;
  }

  function refresh() {
    if (empty) empty.style.display = count() ? 'none' : 'block';
    let sum = 0;
    list.querySelectorAll('[data-weight]').forEach((el) => {
      sum += parseFloat(el.value || '0');
    });
    if (totalEl) totalEl.textContent = sum.toFixed(2);
  }

  function addRow() {
    const node = tpl.content.cloneNode(true);
    const order = node.querySelector('[data-order]');
    if (order) order.value = String(count() + 1);
    list.appendChild(node);
    refresh();
  }

  addBtn.addEventListener('click', addRow);
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-criterion]');
    if (!btn) return;
    btn.closest('.criterion-block')?.remove();
    refresh();
  });
  list.addEventListener('input', refresh);
  addRow();
})();
