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
  const chevronDownIcon = '<span class="select-button-icon" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg></span>';

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
    document.body.classList.add('has-observer');
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
    if (typeof activeOverlay.close === 'function') {
      activeOverlay.close();
      return;
    }
    activeOverlay.menu?.classList?.add('hidden');
    activeOverlay.button?.setAttribute?.('aria-expanded', 'false');
    activeOverlay = null;
  }

  function getPortalTarget(portalTarget) {
    if (portalTarget === false || typeof document === 'undefined') return null;
    if (!portalTarget) return document.body;
    if (typeof portalTarget === 'string') return document.querySelector(portalTarget);
    return portalTarget;
  }

  function positionFloatingMenu(button, menu, options = {}) {
    if (!button || !menu) return;

    const { offset = 6 } = options;
    const rect = button.getBoundingClientRect();
    const maxMenuHeight = Math.min(320, window.innerHeight - 24);
    const spaceBelow = window.innerHeight - rect.bottom - offset;
    const spaceAbove = rect.top - offset;
    const estimatedHeight = Math.min(maxMenuHeight, Math.max(48, menu.scrollHeight || 180));
    const openUp = spaceBelow < estimatedHeight && spaceAbove > spaceBelow;
    const height = Math.max(120, Math.min(maxMenuHeight, openUp ? spaceAbove : spaceBelow));
    const top = openUp
      ? rect.top + window.scrollY - Math.min(estimatedHeight, spaceAbove) - offset
      : rect.bottom + window.scrollY + offset;

    menu.style.left = `${rect.left + window.scrollX}px`;
    menu.style.top = `${top}px`;
    menu.style.width = `${rect.width}px`;
    menu.style.maxHeight = `${height}px`;
    menu.style.setProperty('--select-button-width', `${rect.width}px`);
  }

  function createDropdownController({
    root,
    button,
    menu,
    portalTarget = false,
    offset = 6,
    onOpen,
    onClose,
  } = {}) {
    if (!root || !button || !menu) return null;

    let isOpen = false;
    const portal = getPortalTarget(portalTarget);

    const contains = (node) => {
      if (!node) return false;
      return root.contains(node) || menu.contains(node);
    };

    const syncPosition = () => {
      if (!portal || !isOpen) return;
      positionFloatingMenu(button, menu, { offset });
    };

    const close = () => {
      if (!isOpen) return;
      isOpen = false;
      menu.classList.add('hidden');
      button.setAttribute('aria-expanded', 'false');
      if (activeOverlay === api) activeOverlay = null;
      onClose?.();
    };

    const open = () => {
      if (activeOverlay && activeOverlay !== api && typeof activeOverlay.close === 'function') {
        activeOverlay.close();
      }
      if (portal && menu.parentNode !== portal) {
        portal.appendChild(menu);
      }
      isOpen = true;
      syncPosition();
      menu.classList.remove('hidden');
      button.setAttribute('aria-expanded', 'true');
      activeOverlay = api;
      onOpen?.();
    };

    const toggle = () => {
      if (isOpen) {
        close();
        return;
      }
      open();
    };

    const api = {
      button,
      close,
      contains,
      isOpen: () => isOpen,
      menu,
      open,
      root,
      syncPosition,
      toggle,
    };

    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      toggle();
    });

    button.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        close();
      }
    });

    return api;
  }

  function enhanceNativeSelect(select, options = {}) {
    if (!select || select.dataset.customSelectReady === '1') return null;

    const {
      buttonClass = 'select-button',
      iconHtml = chevronDownIcon,
      menuClass = 'select-menu',
      offset = 6,
      portal = false,
      wrapperClass = 'custom-select custom-select-root',
    } = options;

    select.dataset.customSelectReady = '1';

    const wrapper = document.createElement('div');
    wrapper.className = wrapperClass;
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = buttonClass;
    button.setAttribute('aria-expanded', 'false');
    button.innerHTML = `<span></span>${iconHtml}`;

    const menu = document.createElement('div');
    menu.className = `${menuClass} hidden`;

    const updateButtonLabel = () => {
      const text = select.options[select.selectedIndex]?.text || 'Pilih';
      button.querySelector('span').textContent = text;
      const val = select.value || '';
      button.classList.toggle('is-active', val !== '');
      button.classList.toggle('has-value', val !== '');
    };

    const controller = createDropdownController({
      root: wrapper,
      button,
      menu,
      offset,
      portalTarget: portal ? document.body : false,
    });

    const buildOptions = () => {
      menu.innerHTML = '';
      Array.from(select.options).forEach((option) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.dataset.value = option.value;
        item.textContent = option.text;
        item.classList.toggle('is-active', option.selected);
        item.addEventListener('click', () => {
          select.value = option.value;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          updateButtonLabel();
          menu.querySelectorAll('button').forEach((entry) => {
            entry.classList.toggle('is-active', entry === item);
          });
          controller?.close();
        });
        menu.appendChild(item);
      });
    };

    wrapper.appendChild(button);
    if (!portal) {
      wrapper.appendChild(menu);
    }

    buildOptions();
    updateButtonLabel();

    return {
      button,
      controller,
      menu,
      refresh() {
        buildOptions();
        updateButtonLabel();
      },
      select,
      wrapper,
    };
  }

  function enhanceNativeSelects(root = document, selector = 'select.js-admin-custom-select', options = {}) {
    return Array.from(root.querySelectorAll(selector)).map((select) => enhanceNativeSelect(select, options));
  }

  function enhanceProjectSelects(root = document) {
    const publicSelects = enhanceNativeSelects(root, 'select.js-custom-select', {
      iconHtml: chevronDownIcon,
      portal: false,
      wrapperClass: 'custom-select custom-select-root',
    });

    const adminSelects = enhanceNativeSelects(root, 'select.js-admin-custom-select', {
      buttonClass: 'admin-select-button',
      iconHtml: chevronDownIcon,
      menuClass: 'admin-select-menu',
      portal: true,
      wrapperClass: 'admin-custom-select',
    });

    return [...publicSelects, ...adminSelects];
  }

  function createCustomSelect(root, { label = 'Filter', options = [], value = 'Semua', onChange }) {
    if (!root) return null;

    if (typeof document === 'undefined' || typeof document.createElement !== 'function') {
      let current = value;

      const render = () => {
        root.classList.add('custom-select-root');
        root.innerHTML = `
          <div class="custom-select">
            <button class="select-button" type="button" aria-expanded="false"><span>${current}</span>${chevronDownIcon}</button>
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

    const select = document.createElement('select');
    select.setAttribute('aria-label', label);
    options.forEach((option) => {
      const entry = document.createElement('option');
      entry.value = option;
      entry.textContent = option;
      entry.selected = option === value;
      select.appendChild(entry);
    });

    root.innerHTML = '';
    root.appendChild(select);

    const enhanced = enhanceNativeSelect(select, {
      iconHtml: chevronDownIcon,
      portal: false,
      wrapperClass: 'custom-select custom-select-root',
    });

    select.addEventListener('change', () => onChange?.(select.value));

    return {
      close: closeActiveSelect,
      refresh: enhanced?.refresh,
      select,
    };
  }

  function setupPublicMobileMenu() {
    const panel = document.querySelector('#mobile-panel');
    const open = document.querySelector('#open-menu');
    const close = document.querySelector('#close-menu');
    if (!panel || !open || !close) return;
    if (panel.dataset.mobileMenuReady === '1') return;
    panel.dataset.mobileMenuReady = '1';

    const show = () => {
      panel.classList.remove('hidden');
      lockBody();
      open.setAttribute('aria-expanded', 'true');
      window.addEventListener('keydown', onKeydown);
    };

    const hide = () => {
      if (panel.classList.contains('hidden')) return;
      panel.classList.add('hidden');
      unlockBody();
      open.setAttribute('aria-expanded', 'false');
      window.removeEventListener('keydown', onKeydown);
    };

    const onKeydown = (event) => {
      if (event.key === 'Escape') hide();
    };

    open.setAttribute('aria-expanded', 'false');
    open.setAttribute('aria-controls', 'mobile-panel');
    open.addEventListener('click', show);
    close.addEventListener('click', hide);
    panel.addEventListener('click', (event) => {
      if (event.target === panel) hide();
    });
    panel.querySelectorAll('a').forEach((link) => link.addEventListener('click', hide));
  }

  if (typeof document !== 'undefined') {
    const initUi = () => {
      enhanceProjectSelects(document);
      setupPublicMobileMenu();
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initUi);
    } else {
      initUi();
    }

    document.addEventListener('click', (event) => {
      if (activeOverlay && !activeOverlay.contains(event.target)) closeActiveSelect();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeActiveSelect();
    });

    document.addEventListener('scroll', () => activeOverlay?.syncPosition?.(), true);
    window.addEventListener('resize', () => activeOverlay?.syncPosition?.());
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
    createDropdownController,
    createModalController,
    enhanceNativeSelect,
    enhanceNativeSelects,
    enhanceProjectSelects,
    getFocusable,
    lockBody,
    observeFadeUp,
    positionFloatingMenu,
    safeImage,
    setupPublicMobileMenu,
    unique,
    unlockBody,
  };
});
