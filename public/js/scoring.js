(() => {
  const form = document.querySelector('[data-scoring-form]');
  if (!form) return;
  const weightEl = document.getElementById('score-weights');
  let weights = {};
  try {
    weights = JSON.parse(weightEl ? weightEl.textContent : '{}');
  } catch (err) {
    weights = {};
  }
  const pctEl = document.querySelector('[data-live-pct]');
  const draftUrl = form.dataset.draftUrl;
  const csrf = form.querySelector('input[name="_csrf"]')?.value;
  let readonly = form.dataset.readonly === '1';
  const submitted = form.dataset.submitted === '1';
  const modal = document.querySelector('[data-finalize-modal]');
  let allowSubmit = false;

  function inputs() {
    return form.querySelectorAll('input[data-criteria-id]');
  }

  function bounds(input) {
    let min = parseFloat(input.min);
    let max = parseFloat(input.max);
    if (Number.isNaN(min)) min = 0;
    if (Number.isNaN(max) || max <= min) max = 100;
    return { min, max };
  }

  function calc() {
    let weighted = 0;
    inputs().forEach((input) => {
      const id = input.dataset.criteriaId;
      const v = parseFloat(input.value || '0');
      const w = parseFloat(weights[id] ?? weights[String(id)] ?? 0);
      weighted += (Number.isNaN(v) ? 0 : v) * (w / 100);
    });
    if (pctEl) {
      pctEl.textContent = weighted.toFixed(2) + '%';
    }
  }

  form.querySelectorAll('[data-step]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (readonly) return;
      const wrap = btn.closest('.stepper');
      const input = wrap?.querySelector('input[data-criteria-id]');
      if (!input) return;
      const step = parseFloat(btn.getAttribute('data-step') || '1');
      const { min, max } = bounds(input);
      let cur = parseFloat(input.value);
      if (Number.isNaN(cur)) cur = 0;
      let next = cur + step;
      next = Math.min(max, Math.max(min, next));
      input.value = String(Math.round(next * 10) / 10);
      calc();
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  form.addEventListener('input', calc);
  calc();

  let t;
  function saveDraft() {
    if (!draftUrl || readonly || submitted) return;
    const payload = { round: parseInt(form.dataset.round || '1', 10), scores: {}, comments: form.querySelector('[name="comments"]')?.value || '' };
    inputs().forEach((input) => {
      payload.scores[input.dataset.criteriaId] = input.value;
    });
    fetch(draftUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
      body: JSON.stringify(payload),
    }).catch(() => {});
  }
  form.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(saveDraft, 800);
  });

  form.addEventListener('submit', (e) => {
    if (!submitted || allowSubmit || !modal) return;
    e.preventDefault();
    modal.hidden = false;
  });

  modal?.querySelector('[data-finalize-cancel]')?.addEventListener('click', () => {
    modal.hidden = true;
  });
  modal?.querySelector('[data-finalize-confirm]')?.addEventListener('click', () => {
    allowSubmit = true;
    modal.hidden = true;
    form.requestSubmit();
  });
  modal?.addEventListener('click', (e) => {
    if (e.target === modal) modal.hidden = true;
  });
})();
