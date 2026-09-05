(() => {
  const root = document.documentElement;
  const saved = localStorage.getItem('etab-theme');
  if (saved) root.setAttribute('data-theme', saved);
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('etab-theme', next);
    });
  });
  const sidebar = document.querySelector('.sidebar');
  document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => sidebar?.classList.toggle('open'));
  });
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('submit', (e) => {
      if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
  setTimeout(() => {
    document.querySelectorAll('.toast').forEach((t) => t.remove());
  }, 4200);
})();
