(function (root, factory) {
  'use strict';

  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
    return;
  }

  root.GenBIUI = factory();
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  let lockCount = 0;
  let activeOverlay = null;

  const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
  ].join(',');

  function observeFadeUp() {
    const items = document.querySelectorAll('.fade-up');
    if (!items.length) return;
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    items.forEach((item) => observer.observe(item));
  }

  function getFocusable(container) {
    if (!container) return [];
    return Array.from(container.querySelectorAll(focusableSelector)).filter((element) => {
      return !element.hasAttribute('disabled') && element.offsetParent !== null;
    });
  }

  function lockBody() {
    lockCount += 1;
    document.body.classList.add('modal-lock');
  }

  function unlockBody() {
    lockCount = Math.max(0, lockCount - 1);
    if (lockCount === 0) document.body.classList.remove('modal-lock');
  }

  function createModalController(modal, options = {}) {
    if (!modal) return null;

    const {
      panelSelector = '[role="dialog"]',
      closeSelector = '[data-modal-close], .modal-close',
      initialFocusSelector,
      onClose,
    } = options;

    let previousFocus = null;
    let isOpen = false;

    const getPanel = () => modal.querySelector(panelSelector) || modal;

    const close = () => {
      if (!isOpen) return;
      isOpen = false;
      modal.classList.add('hidden');
      modal.innerHTML = '';
      unlockBody();
      modal.removeEventListener('click', onBackdrop);
      window.removeEventListener('keydown', onKeydown);
      if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
      onClose?.();
    };

    const onBackdrop = (event) => {
      if (event.target === modal) close();
    };

    const onKeydown = (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        close();
        return;
      }

      if (event.key !== 'Tab') return;
      const focusable = getFocusable(getPanel());
      if (!focusable.length) {
        event.preventDefault();
        getPanel().focus();
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };

    const open = ({ content = '', trigger = document.activeElement } = {}) => {
      previousFocus = trigger;
      modal.innerHTML = content;
      modal.classList.remove('hidden');
      lockBody();
      isOpen = true;
      const panel = getPanel();
      if (!panel.hasAttribute('tabindex')) panel.setAttribute('tabindex', '-1');
      modal.querySelectorAll(closeSelector).forEach((button) => button.addEventListener('click', close));
      modal.addEventListener('click', onBackdrop);
      window.addEventListener('keydown', onKeydown);
      const initialFocus = initialFocusSelector ? modal.querySelector(initialFocusSelector) : null;
      const focusTarget = initialFocus || getFocusable(panel)[0] || panel;
      window.setTimeout(() => focusTarget.focus(), 0);
    };

    return { open, close };
  }

  function closeActiveSelect() {
    if (!activeOverlay) return;
    activeOverlay.menu.classList.add('hidden');
    activeOverlay.button.setAttribute('aria-expanded', 'false');
    activeOverlay = null;
  }

  function createCustomSelect(root, { label = 'Filter', options = [], value = 'Semua', onChange }) {
    if (!root) return null;
    let current = value;

    const render = () => {
      root.classList.add('custom-select-root');
      root.innerHTML = `
        <div class="custom-select">
          <button class="select-button" type="button" aria-expanded="false"><span>${current}</span><span>⌄</span></button>
          <div class="select-menu hidden">
            ${options.map((option) => `<button type="button" class="${option === current ? 'is-active' : ''}" data-value="${option}">${option}</button>`).join('')}
          </div>
        </div>
      `;
      const button = root.querySelector('.select-button');
      const menu = root.querySelector('.select-menu');
      button.setAttribute('aria-label', label);

      button.addEventListener('click', (event) => {
        event.stopPropagation();
        const willOpen = menu.classList.contains('hidden');
        closeActiveSelect();
        menu.classList.toggle('hidden', !willOpen);
        button.setAttribute('aria-expanded', String(willOpen));
        activeOverlay = willOpen ? { root, button, menu } : null;
      });

      button.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeActiveSelect();
      });

      menu.querySelectorAll('button').forEach((item) => {
        item.addEventListener('click', () => {
          current = item.dataset.value;
          closeActiveSelect();
          onChange?.(current);
          render();
          root.querySelector('.select-button')?.focus();
        });
      });
    };

    render();
    return { close: closeActiveSelect };
  }

  if (typeof document !== 'undefined') {
    document.addEventListener('click', (event) => {
      if (activeOverlay && !activeOverlay.root.contains(event.target)) closeActiveSelect();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeActiveSelect();
    });
  }

  function unique(items) {
    return ['Semua', ...Array.from(new Set(items)).filter(Boolean)];
  }

  function safeImage(url) {
    return url || 'https://genbijambi.com/public/uploads/slider-1.png';
  }

  return {
    closeActiveSelect,
    createCustomSelect,
    createModalController,
    getFocusable,
    lockBody,
    observeFadeUp,
    safeImage,
    unique,
    unlockBody,
  };
});
