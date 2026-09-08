(function () {
  'use strict';

  const fallbackSite = window.GenBIData.site || {};
  const site = {
    ...fallbackSite,
    ...(window.GenBISiteSettings || {}),
    heroSlides: Array.isArray(window.GenBISiteSettings?.heroSlides) && window.GenBISiteSettings.heroSlides.length
      ? window.GenBISiteSettings.heroSlides
      : fallbackSite.heroSlides,
  };
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
    bank: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',
    chart: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',
    academic: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',
    heart: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',
    faq: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75a2.25 2.25 0 1 1 3.38 1.95c-.85.49-1.13.92-1.13 1.8v.38M12 17.25h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    social: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9M7.5 12h6M21 12c0 4.14-4.03 7.5-9 7.5a10.7 10.7 0 0 1-3.72-.65L3 20.25l1.42-3.79A6.85 6.85 0 0 1 3 12c0-4.14 4.03-7.5 9-7.5s9 3.36 9 7.5Z"/></svg>',
    menu: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"/></svg>',
    plus: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>',
    trash: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5m-9.75 0 .75 12A2.25 2.25 0 0 0 10.5 21h3a2.25 2.25 0 0 0 2.25-2.25l.75-11.25M9.75 7.5V5.25A1.5 1.5 0 0 1 11.25 3.75h1.5a1.5 1.5 0 0 1 1.5 1.5V7.5"/></svg>',
    edit: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65m-1.13-3.78a1.88 1.88 0 0 1 2.65 2.65L8.25 18.79 4.5 19.5l.71-3.75L18.38 3.36Z"/></svg>',
    grid: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"/></svg>',
    list: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.01M3.75 12h.01M3.75 17.25h.01"/></svg>',
    search: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>',
    chevronDown: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>',
    bolt: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>',
    shield: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75c2.7 2.3 5.26 3.24 7.5 3.75v4.95c0 4.2-2.43 7.23-7.5 9.8-5.07-2.57-7.5-5.6-7.5-9.8V7.5c2.24-.51 4.8-1.45 7.5-3.75Z"/></svg>',
    globe: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3c3.5 3.5 3.5 14.5 0 18m0-18c-3.5 3.5-3.5 14.5 0 18M3.75 7.5h16.5M3.75 16.5h16.5"/></svg>',
    megaphone: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.25v1.5A2.25 2.25 0 0 0 5.25 15h2.04l1.53 4.06A1.5 1.5 0 0 0 10.22 20h.53a1.5 1.5 0 0 0 1.46-1.83L11.65 15H12l7.5 3.75V5.25L12 9H5.25A2.25 2.25 0 0 0 3 11.25Z"/></svg>',
    lightBulb: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-5.25 3h4.5M12 3a6.75 6.75 0 0 0-3.98 12.2c.6.44.98 1.12.98 1.87V18h6v-.93c0-.75.38-1.43.98-1.87A6.75 6.75 0 0 0 12 3Z"/></svg>',
    briefcase: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V6A2.25 2.25 0 0 1 11.25 3.75h1.5A2.25 2.25 0 0 1 15 6v.75m-10.5 0h15A1.5 1.5 0 0 1 21 8.25v8.25A2.25 2.25 0 0 1 18.75 18.75H5.25A2.25 2.25 0 0 1 3 16.5V8.25a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>',
    chatBubble: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5h9m-9 3h5.25M21 12c0 4.14-4.03 7.5-9 7.5a10.7 10.7 0 0 1-3.72-.65L3 20.25l1.42-3.79A6.85 6.85 0 0 1 3 12c0-4.14 4.03-7.5 9-7.5s9 3.36 9 7.5Z"/></svg>',
    mapPin: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6-4.35 6-10.2a6 6 0 1 0-12 0C6 16.65 12 21 12 21Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"/></svg>',
    trophy: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5h7.5v2.25A3.75 3.75 0 0 1 12 10.5a3.75 3.75 0 0 1-3.75-3.75V4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.75H4.5A1.5 1.5 0 0 0 3 8.25c0 2.49 2.01 4.5 4.5 4.5h.17m10.33-6H19.5A1.5 1.5 0 0 1 21 8.25c0 2.49-2.01 4.5-4.5 4.5h-.17M12 10.5V15m-3 4.5h6"/></svg>',
    rocket: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75c2.65.17 4.83 2.35 5 5L12 16l-7.25-7.25c.17-2.65 2.35-4.83 5-5L12 6l2.25-2.25Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75 6 18l-2.25.75L4.5 16.5 6.75 14.25M15.75 8.25a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>',
    building: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5.25 21V6.75A2.25 2.25 0 0 1 7.5 4.5h9a2.25 2.25 0 0 1 2.25 2.25V21M9 9h.01M12 9h.01M15 9h.01M9 12h.01M12 12h.01M15 12h.01M11.25 21v-3.75h1.5V21"/></svg>',
    document: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3v4.5a.75.75 0 0 0 .75.75h4.5M6.75 3.75h7.06c.4 0 .78.16 1.06.44l3.94 3.94c.28.28.44.66.44 1.06v10.06A2.25 2.25 0 0 1 17 21.75H6.75A2.25 2.25 0 0 1 4.5 19.5V6A2.25 2.25 0 0 1 6.75 3.75Z"/></svg>',
    clock: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4.5 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    flag: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 21V4.5m0 0h9l-.75 3 3.75 1.5-.75 3h-11.25"/></svg>',
    paperAirplane: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.25 21 3l-8.25 18-1.5-7.5L3 11.25Z"/></svg>',
    userGroup: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.75c0-1.9-2.69-3.45-6-3.45s-6 1.55-6 3.45M12 12.75A3.75 3.75 0 1 0 12 5.25a3.75 3.75 0 0 0 0 7.5M20.25 18.75c0-1.4-1.2-2.62-3-3.19M16.5 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',
    qrCode: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h6v6h-6v-6Zm9 0h6v6h-6v-6Zm-9 9h6v6h-6v-6Zm11.25 0h3.75M13.5 15.75h2.25V18m3.75-2.25V19.5H15"/></svg>',
    presentationChart: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5v10.5H3.75V4.5Zm8.25 10.5v4.5m-3 0h6M7.5 11.25l2.25-2.25 1.5 1.5 3-3"/></svg>',
    envelopeOpen: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25 12 13.5l9.75-5.25M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>',
    phoneArrowUp: '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6h3.75V9.75M18.75 6l-4.5 4.5M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z"/></svg>'
  };

  icons.xMark = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';
  icons.chevronRight = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>';
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
  icons.academicCap = icons.academic;
  icons.archiveBox = icons.document;
  icons.arrowTrendingUp = icons.chart;
  icons.atSymbol = icons.envelopeOpen;
  icons.banknotes = icons.bank;
  icons.beaker = icons.lightBulb;
  icons.bookOpen = icons.academic;
  icons.bookmark = icons.flag;
  icons.calendarDays = icons.event;
  icons.camera = icons.gallery;
  icons.chartBar = icons.chart;
  icons.chartPie = icons.presentationChart;
  icons.chatBubbleLeftRight = icons.chatBubble;
  icons.clipboardDocument = icons.document;
  icons.cloud = icons.globe;
  icons.codeBracket = icons.document;
  icons.cog6Tooth = icons.settings;
  icons.commandLine = icons.document;
  icons.computerDesktop = icons.presentationChart;
  icons.cpuChip = icons.settings;
  icons.creditCard = icons.bank;
  icons.cube = icons.feature;
  icons.devicePhoneMobile = icons.phoneArrowUp;
  icons.envelope = icons.subscriber;
  icons.eye = icons.search;
  icons.film = icons.gallery;
  icons.fire = icons.bolt;
  icons.folder = icons.archiveBox;
  icons.gift = icons.heart;
  icons.handRaised = icons.social;
  icons.handThumbUp = icons.heart;
  icons.hashtag = icons.qrCode;
  icons.home = icons.building;
  icons.identification = icons.users;
  icons.inbox = icons.envelopeOpen;
  icons.key = icons.shield;
  icons.link = icons.globe;
  icons.lockClosed = icons.shield;
  icons.magnifyingGlass = icons.search;
  icons.map = icons.mapPin;
  icons.microphone = icons.megaphone;
  icons.newspaper = icons.news;
  icons.paintBrush = icons.swatch;
  icons.pencilSquare = icons.edit;
  icons.photo = icons.gallery;
  icons.playCircle = icons.slider;
  icons.printer = icons.document;
  icons.puzzlePiece = icons.feature;
  icons.radio = icons.megaphone;
  icons.receiptPercent = icons.bank;
  icons.rss = icons.social;
  icons.scale = icons.bank;
  icons.server = icons.settings;
  icons.share = icons.paperAirplane;
  icons.shoppingBag = icons.briefcase;
  icons.speakerWave = icons.megaphone;
  icons.star = icons.sparkles;
  icons.tag = icons.flag;
  icons.ticket = icons.calendarDays;
  icons.truck = icons.briefcase;
  icons.tv = icons.presentationChart;
  icons.user = icons.users;
  icons.userCircle = icons.users;
  icons.userPlus = icons.userGroup;
  icons.videoCamera = icons.gallery;
  icons.wallet = icons.bank;
  icons.wifi = icons.globe;
  const programIconChoices = [
    'sparkles', 'star', 'bolt', 'fire', 'rocket', 'trophy', 'flag', 'bookmark', 'tag', 'gift', 'heart',
    'users', 'user', 'userGroup', 'userCircle', 'userPlus', 'identification', 'handRaised', 'handThumbUp', 'chatBubble', 'chatBubbleLeftRight',
    'academic', 'academicCap', 'bookOpen', 'lightBulb', 'beaker', 'puzzlePiece', 'commandLine', 'codeBracket', 'cpuChip', 'computerDesktop',
    'bank', 'banknotes', 'wallet', 'creditCard', 'receiptPercent', 'scale', 'chart', 'chartBar', 'chartPie', 'arrowTrendingUp', 'presentationChart',
    'calendar', 'calendarDays', 'clock', 'ticket', 'mapPin', 'map', 'building', 'home', 'briefcase', 'shoppingBag', 'truck',
    'news', 'newspaper', 'document', 'documentText', 'clipboardDocument', 'archiveBox', 'folder', 'pencilSquare', 'printer', 'envelope', 'envelopeOpen', 'atSymbol',
    'gallery', 'photo', 'camera', 'film', 'videoCamera', 'playCircle', 'tv', 'megaphone', 'microphone', 'speakerWave', 'radio', 'rss', 'share', 'paperAirplane',
    'globe', 'link', 'wifi', 'cloud', 'qrCode', 'magnifyingGlass', 'eye', 'grid', 'squares', 'shield', 'lockClosed', 'key', 'settings', 'cog6Tooth', 'server', 'swatch', 'paintBrush',
  ];
  const programIconGroups = [
    { key: 'featured', label: 'Featured', icons: ['sparkles', 'star', 'bolt', 'fire', 'rocket', 'trophy', 'flag', 'bookmark', 'tag', 'gift', 'heart'] },
    { key: 'community', label: 'Community', icons: ['users', 'user', 'userGroup', 'userCircle', 'userPlus', 'identification', 'handRaised', 'handThumbUp', 'chatBubble', 'chatBubbleLeftRight'] },
    { key: 'education', label: 'Education', icons: ['academic', 'academicCap', 'bookOpen', 'lightBulb', 'beaker', 'puzzlePiece', 'commandLine', 'codeBracket', 'cpuChip', 'computerDesktop'] },
    { key: 'finance', label: 'Finance', icons: ['bank', 'banknotes', 'wallet', 'creditCard', 'receiptPercent', 'scale', 'chart', 'chartBar', 'chartPie', 'arrowTrendingUp', 'presentationChart'] },
    { key: 'events', label: 'Events & Places', icons: ['calendar', 'calendarDays', 'clock', 'ticket', 'mapPin', 'map', 'building', 'home', 'briefcase', 'shoppingBag', 'truck'] },
    { key: 'content', label: 'Content', icons: ['news', 'newspaper', 'document', 'documentText', 'clipboardDocument', 'archiveBox', 'folder', 'pencilSquare', 'printer', 'envelope', 'envelopeOpen', 'atSymbol'] },
    { key: 'media', label: 'Media', icons: ['gallery', 'photo', 'camera', 'film', 'videoCamera', 'playCircle', 'tv', 'megaphone', 'microphone', 'speakerWave', 'radio', 'rss', 'share', 'paperAirplane'] },
    { key: 'utility', label: 'Utility', icons: ['globe', 'link', 'wifi', 'cloud', 'qrCode', 'magnifyingGlass', 'eye', 'grid', 'squares', 'shield', 'lockClosed', 'key', 'settings', 'cog6Tooth', 'server', 'swatch', 'paintBrush'] },
  ];

  const links = [
    { key: 'dashboard', label: 'Dashboard', href: adminUrl('dashboard'), icon: 'dashboard' },
    { key: 'settings', label: 'Settings', href: adminUrl('settings'), icon: 'settings', children: [
      { key: 'settings', label: 'Identity', href: adminUrl('settings') },
      { key: 'theme', label: 'Theme', href: adminUrl('settings') + '#theme' }
    ] },
    { key: 'page', label: 'Page', href: adminUrl('page'), icon: 'page' },
    { key: 'feature', label: 'Program Utama', href: adminUrl('feature'), icon: 'feature' },
    { key: 'event', label: 'Agenda', href: adminUrl('event'), icon: 'event' },
    { key: 'team', label: 'Team Member', href: adminUrl('team-member'), icon: 'users' },
    { key: 'news', label: 'News', href: adminUrl('news'), icon: 'news', children: [
      { key: 'category', label: 'Category', href: adminUrl('category') },
      { key: 'news-list', label: 'News', href: adminUrl('news') },
      { key: 'comment', label: 'Comment', href: adminUrl('comment') }
    ] },
    { key: 'buku', label: 'Katalog Buku', href: adminUrl('buku'), icon: 'bookOpen', children: [
      { key: 'buku-list', label: 'Daftar Buku', href: adminUrl('buku') },
      { key: 'buku-add', label: 'Tambah Buku', href: adminUrl('buku-add') }
    ] },
    { key: 'prestasi', label: 'Prestasi', href: adminUrl('prestasi'), icon: 'feature', children: [
      { key: 'prestasi-list', label: 'Prestasi', href: adminUrl('prestasi') },
      { key: 'prestasi-token', label: 'Token Form', href: adminUrl('prestasi-token') }
    ] },
    { key: 'presensi', label: 'Presensi', href: adminUrl('presensi'), icon: 'qrCode', children: [
      { key: 'presensi-list', label: 'Event Presensi', href: adminUrl('presensi') },
      { key: 'presensi-add', label: 'Add Event', href: adminUrl('presensi-add') }
    ] },
    { key: 'genbi-poin', label: 'GenBI Poin', href: adminUrl('genbi-poin'), icon: 'chartBar', children: [
      { key: 'genbi-poin-list', label: 'Rekap Poin', href: adminUrl('genbi-poin') },
      { key: 'genbi-poin-add', label: 'Tambah Aktivitas', href: adminUrl('genbi-poin-add') }
    ] },
    { key: 'subscriber', label: 'Subscriber', href: '#', icon: 'subscriber' },
    { key: 'slider', label: 'Slider', href: adminUrl('slider'), icon: 'slider' },
    { key: 'testimonial', label: 'Testimonial', href: '#', icon: 'social' },
    { key: 'gallery', label: 'Photo Gallery', href: adminUrl('photo'), icon: 'gallery' },
    { key: 'why', label: 'Why Choose Us', href: adminUrl('why-choose'), icon: 'sparkles' },
    { key: 'faq', label: 'FAQ', href: adminUrl('faq'), icon: 'faq' },
    { key: 'social', label: 'Social Media', href: adminUrl('social-media'), icon: 'social' },
    { key: 'language', label: 'Language', href: adminUrl('language'), icon: 'language' }
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
    window.setTimeout(() => normalizeSelectIcons(document), 0);

    // Restore visibility when page is loaded from bfcache (back/forward navigation)
    window.addEventListener('pageshow', function (event) {
      if (event.persisted) {
        document.body.classList.remove('page-leaving');
        document.body.classList.add('page-ready');
        window.setTimeout(() => normalizeSelectIcons(document), 0);
      }
    });
  }

  function renderSidebar(active) {
    const root = document.querySelector('#admin-sidebar');
    if (!root) return;
    root.innerHTML = `
      <div class="flex h-full flex-col p-4">
        <a href="${pageUrl('home')}" class="admin-brand">
          <span class="admin-brand-logo"><img src="${site.logo}" alt="${site.name}" /></span>
          <span class="admin-brand-copy">
            <span class="admin-brand-title">GenBI CMS</span>
            <span class="admin-brand-subtitle">Admin Panel</span>
          </span>
        </a>
        <nav class="mt-6 grid gap-1">
          ${links.map((item) => {
            const isActive = item.key === active || item.children?.some((child) => child.key === active);
            return `
              <div class="admin-nav-group ${isActive ? 'is-open' : ''}">
                <a href="${item.href}" class="admin-link ${isActive ? 'admin-link-active' : ''}">
                  ${icon(item.icon)}<span>${item.label}</span>${item.children ? icon('chevronRight', 'admin-nav-caret ml-auto') : ''}
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
            <p class="admin-topbar-kicker">Admin Panel</p>
            <h1 class="admin-topbar-title">${label}</h1>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <a href="${pageUrl('home')}" class="admin-visit-link">Visit Website</a>
          <form method="POST" action="/admin/logout" class="inline">
            <input type="hidden" name="_csrf_token" value="${(window.GenBIAPI && window.GenBIAPI.getCsrfToken && window.GenBIAPI.getCsrfToken()) || document.querySelector('meta[name=csrf-token]')?.content || ''}">
            <button type="submit" class="admin-visit-link text-red-200 hover:text-white" title="Logout">Logout</button>
          </form>
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

  function normalizeSelectIcons(root = document) {
    root.querySelectorAll('.select-button, .admin-select-button').forEach((button) => {
      Array.from(button.querySelectorAll('span')).forEach((span) => {
        const text = span.textContent.trim();
        if (text.length !== 1 || text.charCodeAt(0) !== 0x2304) return;
        span.className = 'select-button-icon';
        span.setAttribute('aria-hidden', 'true');
        span.innerHTML = icon('chevronDown', 'h-4 w-4 shrink-0');
      });
    });
  }

  function ensureConfirmModal() {
    if (document.querySelector('#admin-confirm-modal')) return;
    const modalRoot = document.querySelector('#admin-modal-root') || document.body;
    modalRoot.insertAdjacentHTML('beforeend', `
      <div id="admin-confirm-modal" class="admin-confirm hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="admin-confirm-panel">
          <div class="admin-confirm-icon">${icon('trash')}</div>
          <h2 id="confirm-title" class="serif text-3xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">Konfirmasi tindakan</h2>
          <div id="confirm-message" class="admin-confirm-message mt-3 text-sm leading-7 text-[rgb(var(--text-secondary))]">Apakah kamu yakin?</div>
          <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="confirm-cancel" class="btn btn-secondary">Batal</button>
            <button type="button" id="confirm-ok" class="btn btn-primary">Ya, lanjutkan</button>
          </div>
        </div>
      </div>
    `);
  }

  function showConfirm({
    title = 'Konfirmasi tindakan',
    message = 'Apakah kamu yakin?',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    danger = false,
    html = false,
    panelClass = '',
  } = {}) {
    ensureConfirmModal();
    return new Promise((resolve) => {
      window.GenBIUI?.closeActiveSelect?.();
      const modal = document.querySelector('#admin-confirm-modal');
      const panel = modal.querySelector('.admin-confirm-panel');
      const ok = modal.querySelector('#confirm-ok');
      const cancel = modal.querySelector('#confirm-cancel');
      const messageNode = modal.querySelector('#confirm-message');
      modal.querySelector('#confirm-title').textContent = title;
      messageNode[html ? 'innerHTML' : 'textContent'] = message;
      ok.textContent = confirmText;
      cancel.textContent = cancelText;
      ok.className = danger ? 'btn btn-danger' : 'btn btn-primary';
      panel.className = `admin-confirm-panel ${panelClass}`.trim();
      const close = (value) => {
        panel.classList.remove('is-open');
        panel.classList.remove('is-wide');
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
      if (panelClass.includes('is-wide')) panel.classList.add('is-wide');
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

  window.GenBIAdmin = { renderAdminShell, showToast, showConfirm, icon, normalizeSelectIcons, escapeHtml, initials, programIconChoices, programIconGroups };
})();
