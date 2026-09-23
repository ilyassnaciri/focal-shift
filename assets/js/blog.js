(() => {
  'use strict';
  const buttons = [...document.querySelectorAll('[data-blog-filter]')];
  const cards = [...document.querySelectorAll('[data-blog-card]')];
  const status = document.querySelector('[data-filter-status]');
  const empty = document.querySelector('[data-blog-empty]');
  if (!buttons.length || !cards.length) return;

  const labels = { all: 'toutes les catégories', interviews: 'les interviews', tests: 'les tests produits', guides: 'les guides' };
  const filterArticles = (category) => {
    let visible = 0;
    cards.forEach((card) => {
      const show = category === 'all' || card.dataset.blogCard === category;
      card.hidden = !show;
      if (show) visible += 1;
    });
    buttons.forEach((button) => {
      const active = button.dataset.blogFilter === category;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    if (status) status.textContent = `${visible} publication${visible > 1 ? 's' : ''} · ${labels[category]}`;
    if (empty) empty.hidden = visible !== 0;
    window.FocalAnalytics?.track('blog_filter', { category });
  };

  buttons.forEach((button) => button.addEventListener('click', () => filterArticles(button.dataset.blogFilter || 'all')));
})();
