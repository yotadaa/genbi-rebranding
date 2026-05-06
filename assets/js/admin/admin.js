(function () {
  'use strict';

  const { site } = window.GenBIData;
  const { adminUrl, pageUrl } = window.GenBIApp;

  const icons = {
    dashboard: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5h6.75v6.75H3.75V13.5Zm9.75 0h6.75v6.75H13.5V13.5ZM3.75 3.75h6.75v6.75H3.75V3.75Zm9.75 0h6.75v6.75H13.5V3.75Z"/></svg>',
    settings: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.6 3.5h4.8l.58 2.32a7.8 7.8 0 0 1 1.7.98l2.27-.68 2.4 4.16-1.7 1.64c.06.35.1.72.1 1.08s-.04.73-.1 1.08l1.7 1.64-2.4 4.16-2.27-.68a7.8 7.8 0 0 1-1.7.98l-.58 2.32H9.6l-.58-2.32a7.8 7.8 0 0 1-1.7-.98l-2.27.68-2.4-4.16 1.7-1.64a6.42 6.42 0 0 1-.1-1.08c0-.36.04-.73.1-1.08l-1.7-1.64 2.4-4.16 2.27.68c.53-.4 1.1-.73 1.7-.98L9.6 3.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z"/></svg>',
    page: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-6.1a2.25 2.25 0 0 0-.66-1.59l-3.4-3.4a2.25 2.25 0 0 0-1.59-.66H6.75A2.25 2.25 0 0 0 4.5 4.75v14.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h7.5M8.25 15h7.5M8.25 18h4.5M14.25 2.5v4.25c0 .83.67 1.5 1.5 1.5H20"/></svg>',
    language: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21M12 17.25h7.5M3 5.25h9M7.5 3v2.25m0 0A9 9 0 0 1 3.75 12M7.5 5.25A9 9 0 0 0 11.25 12M5.25 9h4.5"/></svg>',
    news: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h13.5A1.5 1.5 0 0 1 19.5 8.25v9A2.25 2.25 0 0 0 21.75 15V6.75H19.5m-15 0A1.5 1.5 0 0 0 3 8.25v9A2.25 2.25 0 0 0 5.25 19.5h14.25M7.5 10.5h6M7.5 13.5h6M7.5 16.5h3"/></svg>',
    event: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',
    subscriber: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.57 5.28a2.25 2.25 0 0 1-2.36 0L2.25 6.75"/></svg>',
    users: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',
    slider: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 16.5 4.72-4.72a1.5 1.5 0 0 1 2.12 0l2.16 2.16 1.22-1.22a1.5 1.5 0 0 1 2.12 0l4.16 4.16M8.25 8.25h.01"/></svg>',
    gallery: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 7.5 10.5a2.25 2.25 0 0 1 3.18 0l1.82 1.82.82-.82a2.25 2.25 0 0 1 3.18 0l5.25 5.25M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6Zm12-1.5h.01"/></svg>',
    feature: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 3.75 8.25 12 12.75l8.25-4.5L12 3.75Zm0 9v7.5m0-7.5L3.75 8.25m8.25 4.5 8.25-4.5"/></svg>',
    faq: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75a2.25 2.25 0 1 1 3.38 1.95c-.85.49-1.13.92-1.13 1.8v.38M12 17.25h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    social: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9M7.5 12h6M21 12c0 4.14-4.03 7.5-9 7.5a10.7 10.7 0 0 1-3.72-.65L3 20.25l1.42-3.79A6.85 6.85 0 0 1 3 12c0-4.14 4.03-7.5 9-7.5s9 3.36 9 7.5Z"/></svg>',
    menu: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"/></svg>',
    plus: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>',
    trash: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5m-9.75 0 .75 12A2.25 2.25 0 0 0 10.5 21h3a2.25 2.25 0 0 0 2.25-2.25l.75-11.25M9.75 7.5V5.25A1.5 1.5 0 0 1 11.25 3.75h1.5a1.5 1.5 0 0 1 1.5 1.5V7.5"/></svg>',
    edit: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65m-1.13-3.78a1.88 1.88 0 0 1 2.65 2.65L8.25 18.79 4.5 19.5l.71-3.75L18.38 3.36Z"/></svg>',
    grid: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"/></svg>',
    list: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.01M3.75 12h.01M3.75 17.25h.01"/></svg>',
    search: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>'
  };

  icons.photo = icons.slider;
  icons.image = icons.gallery;
  icons.documentText = icons.page;
  icons.calendar = icons.event;
  icons.newspaper = icons.news;
  icons.chat = icons.social;
  icons.squares = icons.feature;
  icons.table = icons.page;
  icons.mail = icons.subscriber;
  icons.bars = icons.menu;
  icons.window = icons.page;
  icons.swatch = icons.settings;
  icons.sparkles = icons.feature;

  const links = [
    { key: 'dashboard', label: 'Dashboard', href: adminUrl('dashboard'), icon: 'dashboard' },
    { key: 'settings', label: 'Settings', href: adminUrl('settings'), icon: 'settings' },
    { key: 'page', label: 'Page', href: adminUrl('page'), icon: 'page' },
    { key: 'language', label: 'Language', href: adminUrl('language'), icon: 'language' },
    { key: 'news', label: 'News', href: adminUrl('news'), icon: 'news', children: [
      { key: 'category', label: 'Category', href: adminUrl('category') },
      { key: 'news-list', label: 'News', href: adminUrl('news') },
      { key: 'comment', label: 'Comment', href: adminUrl('comment') }
    ] },
    { key: 'event', label: 'Event', href: adminUrl('event'), icon: 'event' },
    { key: 'subscriber', label: 'Subscriber', href: '#', icon: 'subscriber' },
    { key: 'team', label: 'Team Member', href: adminUrl('team-member'), icon: 'users' },
    { key: 'slider', label: 'Slider', href: adminUrl('slider'), icon: 'slider' },
    { key: 'testimonial', label: 'Testimonial', href: '#', icon: 'social' },
    { key: 'gallery', label: 'Photo Gallery', href: adminUrl('photo'), icon: 'gallery' },
    { key: 'feature', label: 'Feature', href: adminUrl('feature'), icon: 'feature' },
    { key: 'why', label: 'Why Choose Us', href: adminUrl('why-choose'), icon: 'sparkles' },
    { key: 'faq', label: 'FAQ', href: adminUrl('faq'), icon: 'faq' },
    { key: 'social', label: 'Social Media', href: adminUrl('social-media'), icon: 'social' }
  ];

  function icon(name, extra = '') {
    const raw = icons[name] || icons.page;
    return extra ? raw.replace('class="h-5 w-5"', `class="h-5 w-5 ${extra}"`) : raw;
  }

  function renderAdminShell(active = 'dashboard') {
    renderSidebar(active);
    renderTopbar(active);
    setupAdminMobile();
    ensureConfirmModal();
    document.body.classList.add('page-ready');
  }

  function renderSidebar(active) {
    const root = document.querySelector('#admin-sidebar');
    if (!root) return;
    root.innerHTML = `
      <div class="flex h-full flex-col p-4">
        <a href="${pageUrl('home')}" class="admin-brand">
          <span class="admin-brand-logo"><img src="${site.logo}" alt="${site.name}" /></span>
          <span>
            <span class="block text-sm font-bold text-white">GenBI CMS</span>
            <span class="block text-xs font-medium text-blue-100">Admin Panel</span>
          </span>
        </a>
        <nav class="mt-6 grid gap-1">
          ${links.map((item) => {
            const isActive = item.key === active || item.children?.some((child) => child.key === active);
            return `
              <div class="admin-nav-group ${isActive ? 'is-open' : ''}">
                <a href="${item.href}" class="admin-link ${isActive ? 'admin-link-active' : ''}">
                  ${icon(item.icon)}<span>${item.label}</span>${item.children ? '<span class="ml-auto text-blue-100/80">›</span>' : ''}
                </a>
                ${item.children ? `<div class="admin-subnav">${item.children.map((child) => `<a href="${child.href}" class="admin-sub-link ${child.key === active ? 'is-active' : ''}">${child.label}</a>`).join('')}</div>` : ''}
              </div>
            `;
          }).join('')}
        </nav>
      </div>
    `;
  }

  function renderTopbar(active) {
    const root = document.querySelector('#admin-topbar');
    if (!root) return;
    const flatLinks = links.flatMap((item) => [item, ...(item.children || [])]);
    const label = flatLinks.find((item) => item.key === active)?.label || 'Admin';
    root.innerHTML = `
      <div class="admin-topbar-inner">
        <div class="flex items-center gap-3">
          <button id="open-admin-menu" class="btn-icon admin-menu-button lg:hidden" aria-label="Open admin menu">${window.GenBIApp.icon('menu')}</button>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-100">Admin Panel</p>
            <h1 class="text-lg font-bold tracking-tight text-white">${label}</h1>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <a href="${pageUrl('home')}" class="admin-visit-link">Visit Website</a>
          <span class="admin-top-logo"><img src="${site.logo}" alt="${site.name}" /></span>
        </div>
      </div>
    `;
  }

  function setupAdminMobile() {
    const sidebar = document.querySelector('#admin-sidebar');
    const open = document.querySelector('#open-admin-menu');
    const backdrop = document.querySelector('#admin-mobile-backdrop');
    if (!sidebar || !open || !backdrop) return;
    const show = () => {
      sidebar.classList.add('is-open');
      backdrop.classList.remove('hidden');
      document.body.classList.add('modal-lock');
      open.setAttribute('aria-expanded', 'true');
      window.addEventListener('keydown', onKeydown);
    };
    const hide = () => {
      sidebar.classList.remove('is-open');
      backdrop.classList.add('hidden');
      document.body.classList.remove('modal-lock');
      open.setAttribute('aria-expanded', 'false');
      window.removeEventListener('keydown', onKeydown);
      open.focus();
    };
    const onKeydown = (event) => {
      if (event.key === 'Escape') hide();
    };
    open.setAttribute('aria-expanded', 'false');
    open.setAttribute('aria-controls', 'admin-sidebar');
    open.addEventListener('click', show);
    backdrop.addEventListener('click', hide);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', hide));
  }

  function showToast(message = 'Perubahan disimpan pada mode simulasi.') {
    const toast = document.querySelector('#admin-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.setTimeout(() => toast.classList.remove('is-visible'), 2400);
  }

  function ensureConfirmModal() {
    if (document.querySelector('#admin-confirm-modal')) return;
    document.body.insertAdjacentHTML('beforeend', `
      <div id="admin-confirm-modal" class="admin-confirm hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="admin-confirm-panel">
          <div class="admin-confirm-icon">${icon('trash')}</div>
          <h2 id="confirm-title" class="serif text-3xl font-semibold tracking-tight text-neutral-950">Konfirmasi tindakan</h2>
          <p id="confirm-message" class="mt-3 text-sm leading-7 text-neutral-600">Apakah kamu yakin?</p>
          <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="confirm-cancel" class="btn btn-secondary">Batal</button>
            <button type="button" id="confirm-ok" class="btn btn-primary">Ya, lanjutkan</button>
          </div>
        </div>
      </div>
    `);
  }

  function showConfirm({ title = 'Konfirmasi tindakan', message = 'Apakah kamu yakin?', confirmText = 'Ya, lanjutkan', danger = false } = {}) {
    ensureConfirmModal();
    return new Promise((resolve) => {
      const modal = document.querySelector('#admin-confirm-modal');
      const panel = modal.querySelector('.admin-confirm-panel');
      const ok = modal.querySelector('#confirm-ok');
      const cancel = modal.querySelector('#confirm-cancel');
      modal.querySelector('#confirm-title').textContent = title;
      modal.querySelector('#confirm-message').textContent = message;
      ok.textContent = confirmText;
      ok.className = danger ? 'btn btn-danger' : 'btn btn-primary';
      const close = (value) => {
        panel.classList.remove('is-open');
        window.setTimeout(() => modal.classList.add('hidden'), 120);
        ok.removeEventListener('click', onOk);
        cancel.removeEventListener('click', onCancel);
        modal.removeEventListener('click', onBackdrop);
        window.removeEventListener('keydown', onKey);
        resolve(value);
      };
      const onOk = () => close(true);
      const onCancel = () => close(false);
      const onBackdrop = (event) => { if (event.target === modal) close(false); };
      const onKey = (event) => { if (event.key === 'Escape') close(false); };
      ok.addEventListener('click', onOk);
      cancel.addEventListener('click', onCancel);
      modal.addEventListener('click', onBackdrop);
      window.addEventListener('keydown', onKey);
      modal.classList.remove('hidden');
      window.setTimeout(() => panel.classList.add('is-open'), 20);
      cancel.focus();
    });
  }

  function escapeHtml(value = '') {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function initials(name = '') {
    return name.split(' ').filter(Boolean).slice(0, 2).map((word) => word[0]).join('').toUpperCase();
  }

  window.GenBIAdmin = { renderAdminShell, showToast, showConfirm, icon, escapeHtml, initials };
})();
