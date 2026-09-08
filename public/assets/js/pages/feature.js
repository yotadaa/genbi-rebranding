(function () {
  'use strict';

  const root = document.querySelector('#feature-program-list[data-ssr="true"]');
  if (!root) return;

  root.querySelectorAll('.program-slide-card').forEach((card) => {
    const iconTarget = card.querySelector('[data-program-icon]');
    if (iconTarget && window.GenBIApp?.icon) {
      iconTarget.innerHTML = window.GenBIApp.icon(iconTarget.dataset.programIcon || 'sparkles', 'program-icon-svg');
    }

    let slides = [];
    try {
      slides = JSON.parse(card.dataset.programSlides || '[]').filter((image) => typeof image === 'string' && image !== '');
    } catch {
      slides = [];
    }
    if (slides.length === 0) return;

    card.style.setProperty('--program-bg-image', `url('${slides[0]}')`);
    if (slides.length < 2) return;

    let index = 0;
    window.setInterval(() => {
      index = (index + 1) % slides.length;
      card.style.setProperty('--program-bg-image', `url('${slides[index]}')`);
    }, 4200);
  });
})();
