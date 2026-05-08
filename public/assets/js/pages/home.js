(function(){
'use strict';
const { newsDetailUrl, pageUrl, renderShell } = window.GenBIApp;
const { observeFadeUp } = window.GenBIUI;
const { site, stats, programs, news, bpiMembers, publicEvents } = window.GenBIData;
const API = window.GenBIAPI;

renderShell('home');
renderHero();
renderStats();
renderPrograms();
renderBPIList();
renderHomeEvents();
renderHomeNews();
renderHomeContact();
setupVideoModal();
setupHorizontalCarousels();
observeFadeUp();

function renderHero() {
  const slider = document.querySelector('#hero-slider');
  const dots = document.querySelector('#hero-dots');
  const eyebrow = document.querySelector('#hero-eyebrow');
  const title = document.querySelector('#hero-title');
  const caption = document.querySelector('#hero-caption');
  if (!slider) return;

  slider.innerHTML = site.heroSlides.map((slide, index) => `
    <img src="${slide.image}" alt="${slide.caption}" class="hero-image hero-bg-image ${index === 0 ? 'is-active' : ''}" />
  `).join('');
  dots.innerHTML = site.heroSlides.map((_, index) => `<button class="h-2.5 w-2.5 rounded-full bg-white/40 transition hover:bg-white ${index === 0 ? 'bg-white' : ''}" aria-label="Slide ${index + 1}" data-slide="${index}"></button>`).join('');
  let active = 0;
  const update = (index) => {
    active = index;
    const slide = site.heroSlides[active];
    slider.querySelectorAll('img').forEach((img, imageIndex) => img.classList.toggle('is-active', imageIndex === active));
    dots.querySelectorAll('button').forEach((button, buttonIndex) => {
      button.classList.toggle('bg-white', buttonIndex === active);
      button.classList.toggle('bg-white/40', buttonIndex !== active);
    });
    eyebrow.innerHTML = `${window.GenBIApp.icon('sparkles', 'h-4 w-4')} ${slide.eyebrow}`;
    title.textContent = slide.title;
    caption.textContent = slide.caption;
  };

  dots.querySelectorAll('button').forEach((button) => button.addEventListener('click', () => update(Number(button.dataset.slide))));
  update(0);
  window.setInterval(() => update((active + 1) % site.heroSlides.length), 6500);
}

function renderStats() {
  const root = document.querySelector('#stats-row');
  root.innerHTML = stats.map((item) => `
    <div class="fade-up">
      <p class="serif text-4xl font-semibold tracking-tight text-neutral-950">${item.value}</p>
      <p class="mt-2 text-sm leading-6 text-neutral-600">${item.label}</p>
    </div>
  `).join('');
}

function programIcon(title = '') {
  const key = title.toLowerCase();
  if (key.includes('siginjai')) return eventIcon('bank');
  if (key.includes('gentala')) return eventIcon('chart');
  if (key.includes('ggtc')) return eventIcon('academic');
  if (key.includes('leadership')) return eventIcon('spark');
  return eventIcon('users');
}

function renderPrograms() {
  const root = document.querySelector('#program-list');
  if (!root) return;
  if (root.dataset.ssr === 'true' && root.children.length) {
    hydrateProgramCards(root);
    return;
  }

  root.innerHTML = programs.map((program, index) => {
    const images = Array.isArray(program.images) && program.images.length ? program.images : [site.heroSlides[0]?.image || 'https://genbijambi.com/public/uploads/slider-1.png'];
    return `
      <article class="editorial-slide-card program-slide-card" role="group" aria-roledescription="slide" aria-label="Program ${index + 1} dari ${programs.length}" data-program-slides='${JSON.stringify(images)}'>
        <div class="program-slide-media"><img src="${images[0]}" alt="${program.name}" class="program-slide-image is-active" loading="lazy" /></div>
        <div class="program-slide-overlay"></div>
        <div class="program-slide-content">
          <span class="slide-index">${String(index + 1).padStart(2, '0')}</span>
          <span class="program-icon program-hero-icon">${window.GenBIApp.icon(program.icon_key || 'sparkles')}</span>
          <p class="slide-kicker">${program.title}</p>
          <h3>${program.name}</h3>
          <p>${program.description}</p>
          <span class="program-focus-badge mt-5">${program.focus}</span>
        </div>
      </article>
    `;
  }).join('');
  hydrateProgramCards(root);
}

function hydrateProgramCards(root) {
  root.querySelectorAll('.program-slide-card').forEach((card) => {
    const iconTarget = card.querySelector('[data-program-icon]');
    if (iconTarget) {
      const iconKey = iconTarget.dataset.programIcon || 'sparkles';
      iconTarget.innerHTML = window.GenBIApp.icon(iconKey);
    } else {
      const existingIcon = card.querySelector('.program-hero-icon');
      if (existingIcon && !existingIcon.innerHTML.trim()) {
        existingIcon.innerHTML = window.GenBIApp.icon('sparkles');
      }
    }

    const slides = JSON.parse(card.dataset.programSlides || '[]').filter(Boolean);
    const media = card.querySelector('.program-slide-media');
    if (!media || slides.length <= 1) return;

    media.innerHTML = slides.map((slide, index) => `
      <img src="${slide}" alt="" class="program-slide-image ${index === 0 ? 'is-active' : ''}" loading="lazy" />
    `).join('');

    let active = 0;
    window.setInterval(() => {
      const images = media.querySelectorAll('.program-slide-image');
      if (images.length <= 1) return;
      active = (active + 1) % images.length;
      images.forEach((image, index) => image.classList.toggle('is-active', index === active));
    }, 4200);
  });
}


async function renderBPIList() {
  const root = document.querySelector('#bpi-list');
  if (!root) return;
  let members = bpiMembers;
  try {
    const payload = await API.getTeamList({ per_page: 200 });
    members = Array.isArray(payload?.bpi) && payload.bpi.length ? payload.bpi : members;
  } catch (e) { /* fallback */ }
  root.innerHTML = members.map((member, index) => `
    <article class="editorial-slide-card bpi-slide-card" role="group" aria-roledescription="slide" aria-label="Anggota BPI ${index + 1} dari ${members.length}">
      <figure class="bpi-slide-photo">
        <span class="member-photo-skeleton" aria-hidden="true"></span>
        <img src="${member.photo || member.image}" alt="Foto ${member.name}" loading="lazy" onload="this.previousElementSibling.classList.add('is-hidden')" onerror="this.classList.add('is-hidden'); this.previousElementSibling.classList.remove('is-hidden')" />
      </figure>
      <div class="bpi-slide-content">
        <span class="bpi-number mx-auto">${String(index + 1).padStart(2, '0')}</span>
        <h3>${member.name}</h3>
        <p>${member.role}</p>
        <span class="blue-badge mx-auto mt-5">${member.commission}</span>
      </div>
    </article>
  `).join('');
}

function eventIcon(type) {
  const map = {
    heart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',
    users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',
    chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',
    calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',
    bank: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',
    academic: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',
    spark: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.76 5.45h5.74l-4.64 3.37 1.77 5.45L12 13.9l-4.63 3.37 1.77-5.45L4.5 8.45h5.74L12 3Z"/></svg>'
  };
  return map[type] || map.calendar;
}

function renderHomeEvents() {
  const root = document.querySelector('#home-events');
  if (!root) return;
  root.innerHTML = publicEvents.map((event, index) => `
    <article class="editorial-slide-card event-slide-card" role="group" aria-roledescription="slide" aria-label="Agenda ${index + 1} dari ${publicEvents.length}">
      <span class="event-icon mx-auto">${eventIcon(event.icon)}</span>
      <div class="event-meta justify-center"><span>${event.type}</span><span>${event.date}</span></div>
      <h3>${event.title}</h3>
      <p>${event.description}</p>
    </article>
  `).join('');
}

function renderHomeContact() {
  const root = document.querySelector('#home-contact-card');
  if (!root) return;
  root.innerHTML = `
    <div>
      <p class="eyebrow">Contact us</p>
      <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">Mau berkolaborasi dengan GenBI Jambi?</h2>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Hubungi kami untuk informasi kegiatan, publikasi, kolaborasi, dan agenda komunitas.</p>
    </div>
    <div class="contact-prefooter-card">
      <p class="contact-label">Address</p>
      <p>${site.address}</p>
      <div class="mt-5 grid gap-2 text-sm">
        <a href="mailto:${site.email}">${site.email}</a>
        <a href="tel:${site.phone}">${site.phone}</a>
      </div>
      <a data-transition href="${pageUrl('contact')}" class="btn btn-primary mt-6 w-fit">Contact Us</a>
    </div>
  `;
}

function renderHomeNews() {
  const root = document.querySelector('#home-news');
  root.innerHTML = news.slice(0, 3).map((item) => `
    <a data-transition href="${newsDetailUrl(item)}" class="home-news-card">
      <figure class="home-news-media"><img src="${item.image}" alt="${item.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" /></figure>
      <div class="home-news-copy">
        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
          <span class="text-blue-800">${item.category}</span><span>${item.date}</span><span>${item.readTime}</span>
        </div>
        <h3 class="serif text-2xl font-semibold leading-tight tracking-tight text-neutral-950 md:text-3xl">${item.title}</h3>
        <p class="text-base leading-7 text-neutral-600">${item.excerpt}</p>
      </div>
    </a>
  `).join('');
}

function setupHorizontalCarousels() {
  document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('.horizontal-carousel');
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    if (!track || !previous || !next) return;

    const getDistance = () => {
      const firstCard = track.querySelector('.editorial-slide-card');
      if (!firstCard) return track.clientWidth * 0.85;
      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
      return firstCard.getBoundingClientRect().width + gap;
    };

    previous.addEventListener('click', () => track.scrollBy({ left: -getDistance(), behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: getDistance(), behavior: 'smooth' }));
  });
}

function setupVideoModal() {
  const modal = document.querySelector('#video-modal');
  const panel = modal?.querySelector('.modal-panel');
  const open = document.querySelector('#open-video');
  const close = document.querySelector('#close-video');
  const iframe = document.querySelector('#profile-video');
  if (!modal || !open || !close || !iframe) return;
  const show = () => {
    iframe.src = site.videoResourceUrl;
    modal.classList.remove('hidden');
    window.setTimeout(() => panel.classList.add('is-open'), 20);
  };
  const hide = () => {
    panel.classList.remove('is-open');
    window.setTimeout(() => {
      modal.classList.add('hidden');
      iframe.src = '';
    }, 160);
  };
  open.addEventListener('click', show);
  close.addEventListener('click', hide);
  modal.addEventListener('click', (event) => { if (event.target === modal) hide(); });
}

})();
