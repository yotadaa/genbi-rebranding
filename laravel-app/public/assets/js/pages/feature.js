(function () {
  'use strict';

  window.GenBIApp.renderShell('feature');

  const list = document.querySelector('#feature-program-list');
  if (!list) return;

  list.querySelectorAll('.program-slide-card').forEach((card) => {
    const iconTarget = card.querySelector('[data-program-icon]');
    if (iconTarget) {
      iconTarget.innerHTML = window.GenBIApp.icon(iconTarget.dataset.programIcon || 'sparkles', 'program-icon-svg');
    }

    let images = [];
    try {
      images = JSON.parse(card.dataset.programSlides || '[]').filter(Boolean);
    } catch (error) {
      images = [];
    }

    if (!images.length) return;
    card.style.setProperty('--program-bg-image', `url('${images[0]}')`);

    if (images.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      let index = 0;
      window.setInterval(() => {
        index = (index + 1) % images.length;
        card.style.setProperty('--program-bg-image', `url('${images[index]}')`);
      }, 4200);
    }
  });
})();
