(function () {
  'use strict';
const { navItems, site } = window.GenBIData;
const Core = window.GenBIAPICore;

const HERO_ICONS = {
  menu: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',
  x: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>',
  mail: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.62a2.25 2.25 0 0 1-2.36 0l-7.5-4.62a2.25 2.25 0 0 1-1.07-1.92v-.24"/></svg>',
  phone: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z"/></svg>',
  arrowRight: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>',
  sparkles: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 3.7 8.4 8.1 4 9.5l4.4 1.4 1.4 4.4 1.4-4.4 4.4-1.4-4.4-1.4-1.4-4.4Zm7.4 6.7-.9 2.8-2.8.9 2.8.9.9 2.8.9-2.8 2.8-.9-2.8-.9-.9-2.8Z"/></svg>'
};

function icon(name, cls = '') {
  return (HERO_ICONS[name] || '').replace('class="', `class="${cls} `);
}

function renderShell(activeKey = 'home') {
  renderHeader(activeKey);
  renderFooter();
  setupMobileMenu();
  setupAutoHideHeader();
  setupPageTransitions();
  setupBackToTop();
}

function renderHeader(activeKey) {
  const header = document.querySelector('#site-header');
  if (!header) return;

  const nav = navItems
    .map((item) => {
      const active = item.key === activeKey;
      return `<a data-transition href="${pageUrl(item.key)}" class="nav-link ${active ? 'nav-link-active' : ''}">${item.label}</a>`;
    })
    .join('');

  header.innerHTML = `
    <div id="site-header-shell" class="site-header-shell">
      <div class="top-strip hidden md:block">
        <div class="site-container flex h-9 items-center justify-between text-[13px] text-white/90">
          <div class="flex items-center gap-4">
            <a href="mailto:${site.email}" class="inline-flex items-center gap-2 hover:text-white">${icon('mail')}${site.email}</a>
            <span class="h-4 w-px bg-white/30"></span>
            <a href="tel:${site.phone}" class="inline-flex items-center gap-2 hover:text-white">${icon('phone')}${site.phone}</a>
          </div>
          <div class="flex items-center gap-3" aria-label="Social links">
            <a href="#" class="social-mini">Fb</a>
            <a href="#" class="social-mini">Ig</a>
            <a href="#" class="social-mini">Yt</a>
            <a href="#" class="social-mini">Wa</a>
          </div>
        </div>
      </div>
      <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
        <div class="site-container flex h-20 items-center justify-between">
          <a data-transition href="${pageUrl('home')}" class="flex items-center gap-3" aria-label="Go to home">
            <span class="logo-shell"><img src="${site.logo}" alt="${site.name}" class="h-9 w-auto" /></span>
            <span class="leading-tight">
              <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
              <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
            </span>
          </a>
          <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
            ${nav}
          </nav>
          <div class="hidden items-center gap-3 lg:flex">
            <a data-transition href="${adminUrl('dashboard')}" class="btn btn-secondary">Admin Preview</a>
            <a data-transition href="${pageUrl('contact')}" class="btn btn-primary">Hubungi Kami ${icon('arrowRight')}</a>
          </div>
          <button id="open-menu" class="btn-icon lg:hidden" aria-label="Open menu">${icon('menu')}</button>
        </div>
      </header>
    </div>
    <div id="site-header-spacer" aria-hidden="true"></div>
    <div id="mobile-panel" class="fixed inset-0 z-[70] hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden">
      <div class="mobile-sheet">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="logo-shell"><img src="${site.logo}" alt="${site.name}" class="h-8 w-auto" /></span>
            <span class="font-semibold text-neutral-950">Menu</span>
          </div>
          <button id="close-menu" class="btn-icon" aria-label="Close menu">${icon('x')}</button>
        </div>
        <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
          ${navItems
            .map((item) => `<a data-transition href="${pageUrl(item.key)}" class="mobile-link ${item.key === activeKey ? 'mobile-link-active' : ''}">${item.label}<span>›</span></a>`)
            .join('')}
          <a data-transition href="${adminUrl('dashboard')}" class="mobile-link">Admin Preview<span>›</span></a>
        </nav>
        <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
          <strong>${site.name}</strong><br />${site.tagline}
        </div>
      </div>
    </div>
  `;
}

function renderFooter() {
  const footer = document.querySelector('#site-footer');
  if (!footer) return;

  footer.innerHTML = `
    <section class="border-t border-neutral-900/10 bg-blue-950 text-white">
      <div class="site-container grid gap-10 py-14 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
        <div>
          <div class="flex items-center gap-3">
            <span class="logo-shell logo-shell-light"><img src="${site.logo}" alt="${site.name}" class="h-10 w-auto" /></span>
            <div>
              <p class="font-semibold">${site.name}</p>
              <p class="text-sm text-blue-100/80">${site.tagline}</p>
            </div>
          </div>
          <p class="mt-5 max-w-md text-sm leading-7 text-blue-100/80">Website publik untuk profil komunitas, kegiatan, prestasi, berita, anggota, dan kontak resmi GenBI Jambi.</p>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Navigasi</h3>
          <div class="mt-4 grid gap-2 text-sm text-blue-100/80">
            ${navItems.map((item) => `<a data-transition href="${pageUrl(item.key)}" class="w-fit hover:text-white">${item.label}</a>`).join('')}
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Kontak</h3>
          <div class="mt-4 grid gap-2 text-sm leading-6 text-blue-100/80">
            <a href="mailto:${site.email}" class="hover:text-white">${site.email}</a>
            <a href="tel:${site.phone}" class="hover:text-white">${site.phone}</a>
            <p>${site.address}</p>
          </div>
        </div>
      </div>
      <div class="border-t border-white/10 py-5 text-center text-xs text-blue-100/70">Copyright © 2026, GenBI Provinsi Jambi. Static HTML prototype.</div>
    </section>
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">↑</button>
  `;
}

function setupMobileMenu() {
  const panel = document.querySelector('#mobile-panel');
  const open = document.querySelector('#open-menu');
  const close = document.querySelector('#close-menu');
  if (!panel || !open || !close) return;

  const show = () => {
    panel.classList.remove('hidden');
    document.body.classList.add('modal-lock');
    open.setAttribute('aria-expanded', 'true');
    window.addEventListener('keydown', onKeydown);
  };

  const hide = () => {
    panel.classList.add('hidden');
    document.body.classList.remove('modal-lock');
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

function setupAutoHideHeader() {
  const shell = document.querySelector('#site-header-shell');
  const spacer = document.querySelector('#site-header-spacer');
  if (!shell || !spacer) return;

  let lastScrollY = window.scrollY;
  let ticking = false;
  const revealThreshold = 24;
  const hideThreshold = 96;

  const syncSpacerHeight = () => {
    spacer.style.height = `${shell.offsetHeight}px`;
  };

  const update = () => {
    const currentScrollY = Math.max(window.scrollY, 0);
    const delta = currentScrollY - lastScrollY;
    const nearTop = currentScrollY <= revealThreshold;
    const scrollingUp = delta < 0;
    const scrollingDown = delta > 0;

    shell.classList.toggle('is-scrolled', currentScrollY > 0);

    if (nearTop || scrollingUp) {
      shell.classList.remove('is-hidden');
    } else if (currentScrollY > hideThreshold && scrollingDown) {
      shell.classList.add('is-hidden');
    }

    lastScrollY = currentScrollY;
    ticking = false;
  };

  syncSpacerHeight();
  update();
  window.addEventListener('resize', syncSpacerHeight);
  window.addEventListener('load', syncSpacerHeight);
  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(update);
  }, { passive: true });
}

function setupPageTransitions() {
  document.body.classList.add('page-ready');
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-transition]');
    if (!link) return;
    const url = new URL(link.href, window.location.href);
    const current = new URL(window.location.href);
    if (url.origin !== current.origin || link.target || event.metaKey || event.ctrlKey) return;
    event.preventDefault();
    document.body.classList.add('page-leaving');
    window.setTimeout(() => {
      window.location.href = url.href;
    }, 130);
  });
}

function setupBackToTop() {
  const button = document.querySelector('#back-to-top');
  if (!button) return;
  const toggle = () => button.classList.toggle('is-visible', window.scrollY > 500);
  toggle();
  window.addEventListener('scroll', toggle, { passive: true });
  button.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

function formatInitials(name) {
  return name
    .split(' ')
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();
}

function getParam(name) {
  const search = new URLSearchParams(window.location.search);
  const value = search.get(name);
  if (value) return value;
  if (name === 'slug' && window.location.pathname.startsWith('/news/')) return decodeURIComponent(window.location.pathname.split('/').filter(Boolean).pop() || '');
  return null;
}

function pageUrl(page) {
  return Core?.pageUrl ? Core.pageUrl(page, window.location) : `/${page}`;
}

function adminUrl(page) {
  return Core?.adminUrl ? Core.adminUrl(page, window.location) : `/admin/${page}`;
}

function newsDetailUrl(news) {
  return Core?.newsDetailUrl ? Core.newsDetailUrl(news, window.location) : `/news/${news.slug || news.id}`;
}

window.GenBIApp = {
  adminUrl,
  renderShell,
  formatInitials,
  getParam,
  icon,
  newsDetailUrl,
  pageUrl,
};

})();
