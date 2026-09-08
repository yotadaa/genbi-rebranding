(function () {
  'use strict';

  const gallery = document.querySelector('[data-prestasi-gallery]');
  if (!gallery) {
    return;
  }

  const thumbs = Array.from(gallery.querySelectorAll('[data-prestasi-thumb]'));
  const modal = document.querySelector('[data-prestasi-image-modal]');
  const modalImage = modal?.querySelector('[data-prestasi-modal-image]');
  const closeButtons = Array.from(modal?.querySelectorAll('[data-prestasi-modal-close]') || []);
  if (thumbs.length === 0 || !modal || !modalImage) {
    return;
  }

  let previousFocus = null;

  const closeModal = () => {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-lock');
    modalImage.removeAttribute('src');
    modalImage.alt = '';
    if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
  };

  const openModal = (src, alt, trigger) => {
    if (!src) return;
    previousFocus = trigger || document.activeElement;
    modalImage.src = src;
    modalImage.alt = alt || 'Foto prestasi';
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-lock');
    window.setTimeout(() => modal.querySelector('.prestasi-image-modal-close')?.focus(), 0);
  };

  thumbs.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      const src = thumb.dataset.imageSrc || '';
      const alt = thumb.dataset.imageAlt || thumb.querySelector('img')?.alt || 'Foto prestasi';
      openModal(src, alt, thumb);
    });
  });

  closeButtons.forEach((button) => button.addEventListener('click', closeModal));
  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
  });
})();
