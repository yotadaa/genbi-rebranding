(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { createModalController, observeFadeUp, safeImage } = window.GenBIUI;
const API = window.GenBIAPI;

renderShell('event');

const ssrList = document.querySelector('#event-list[data-ssr="true"]');
const ssrDetail = document.body.dataset.ssr === 'true' && !document.querySelector('#event-list');

if (ssrDetail) {
  document.body.classList.add('page-ready');
  observeFadeUp();
} else if (ssrList) {
  // SSR path: content already rendered server-side
  // Bind search form Enter key
  const searchInput = document.querySelector('#event-search');
  if (searchInput) {
    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        document.querySelector('#event-filter-form')?.submit();
      }
    });
  }

  // Bind detail buttons to open modal (progressive enhancement)
  const eventModal = createModalController(document.querySelector('#event-modal'));
  ssrList.querySelectorAll('.open-event').forEach((button) => {
    button.addEventListener('click', (event) => {
      // If JS is available, intercept link click and open modal instead
      event.preventDefault();
      const id = Number(button.dataset.id);
      const slug = button.dataset.slug || '';
      if (id > 0) openEventModal({ id, slug }, eventModal);
    });
  });
} else {
  // CSR fallback path: fetch data and render client-side (for fallbacks/event.html)
  observeFadeUp();

  let events = [];
  let state = { query: '', page: 1, perPage: 9 };

  const search = document.querySelector('#event-search');
  const count = document.querySelector('#event-count');
  const list = document.querySelector('#event-list');
  const pagination = document.querySelector('#event-pagination');
  const eventModal = createModalController(document.querySelector('#event-modal'));

  init();

  async function init() {
    if (count) count.textContent = 'Memuat data event...';
    events = await API.getEventList();

    if (search) {
      search.addEventListener('input', (event) => {
        state.query = event.target.value;
        state.page = 1;
        renderEvents();
      });
    }

    document.querySelector('#event-reset')?.addEventListener('click', () => {
      if (search) search.value = '';
      state.query = '';
      state.page = 1;
      renderEvents();
    });

    renderEvents();
  }

  function getFiltered() {
    const query = state.query.toLowerCase().trim();
    return events.filter((item) => {
      const haystack = `${item.title} ${item.excerpt} ${item.location} ${item.status}`.toLowerCase();
      return !query || haystack.includes(query);
    });
  }

  function renderEvents() {
    const filtered = getFiltered();
    const totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
    state.page = Math.min(state.page, totalPages);
    const start = (state.page - 1) * state.perPage;
    const paged = filtered.slice(start, start + state.perPage);

    if (count) {
      count.textContent = filtered.length
        ? `Menampilkan ${start + 1}-${Math.min(start + state.perPage, filtered.length)} dari ${filtered.length} event.`
        : 'Tidak ada event yang cocok.';
    }

    if (list) {
      list.innerHTML = paged.length ? paged.map((item) => `
        <article class="event-card">
          <div class="event-card-image">
            <img src="${safeImage(item.image)}" alt="${item.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
            <span class="event-card-badge ${item.status === 'Upcoming' ? 'upcoming' : ''}">${item.status}</span>
          </div>
          <div class="event-card-body">
            <p class="eyebrow">${item.start_date}${item.end_date && item.end_date !== item.start_date ? ' – ' + item.end_date : ''}</p>
            <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${item.title}</h3>
            <p class="mt-3 text-sm leading-6 text-neutral-600">${item.excerpt}</p>
            ${item.location ? `<p class="mt-2 text-sm font-semibold text-blue-800">${item.location}</p>` : ''}
          </div>
          <button class="btn btn-secondary open-event" data-id="${item.id}" data-slug="${item.slug || ''}">Detail</button>
        </article>
      `).join('') : `<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada event yang cocok.</div>`;

      list.querySelectorAll('.open-event').forEach((button) => {
        button.addEventListener('click', () => openEventCSR({ id: Number(button.dataset.id), slug: button.dataset.slug || '' }, eventModal));
      });
    }
    renderPagination(totalPages);
  }

  function renderPagination(totalPages) {
    if (!pagination) return;
    if (totalPages <= 1) { pagination.innerHTML = ''; return; }
    const pages = Array.from({ length: totalPages }, (_, i) => i + 1);
    pagination.innerHTML = `
      <button class="pager-button" type="button" data-page="${Math.max(1, state.page - 1)}" ${state.page === 1 ? 'disabled' : ''}>Sebelumnya</button>
      ${pages.map((p) => `<button class="pager-button ${p === state.page ? 'is-active' : ''}" type="button" data-page="${p}">${p}</button>`).join('')}
      <button class="pager-button" type="button" data-page="${Math.min(totalPages, state.page + 1)}" ${state.page === totalPages ? 'disabled' : ''}>Berikutnya</button>
    `;
    pagination.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => {
        state.page = Number(button.dataset.page) || 1;
        renderEvents();
        if (list) list.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  function openEventCSR(target, modal) {
    const item = events.find((e) => e.id === target.id || e.slug === target.slug);
    if (!item) return;
    renderEventModal(item, modal);
  }
}

function eventDetailHref(target) {
  return `/event/${encodeURIComponent(target.slug || target.id || '')}`;
}

async function openEventModal(target, modal) {
  try {
    const response = await fetch(eventDetailHref(target), { headers: { 'Accept': 'application/json' } });
    if (!response.ok) {
      window.location.href = eventDetailHref(target);
      return;
    }
    const json = await response.json();
    const item = json.data;
    if (!item) {
      window.location.href = eventDetailHref(target);
      return;
    }
    renderEventModal(item, modal);
  } catch (e) {
    window.location.href = eventDetailHref(target);
  }
}

function renderEventModal(item, modal) {
  const safeImg = window.GenBIUI?.safeImage || ((v) => v || 'https://genbijambi.com/public/uploads/slider-1.png');
  modal.open({ content: `
    <div class="public-modal-panel event-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="event-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail event">\u00d7</button>
      <div class="event-detail-image">
        <img src="${safeImg(item.banner || item.image)}" alt="${item.title}" />
      </div>
      <div class="event-detail-content">
        <div class="event-detail-heading">
          <span class="event-card-badge ${item.status === 'Upcoming' ? 'upcoming' : ''}">${item.status}</span>
          <h3 id="event-title" class="serif mt-2 text-3xl font-semibold tracking-tight text-neutral-950">${item.title}</h3>
        </div>
        <div class="event-detail-info-grid">
          <div class="event-detail-info-card"><span>Tanggal</span><strong>${item.start_date}${item.end_date && item.end_date !== item.start_date ? ' \u2013 ' + item.end_date : ''}</strong></div>
          ${item.location ? `<div class="event-detail-info-card"><span>Lokasi</span><strong>${item.location}</strong></div>` : ''}
        </div>
        <div class="event-detail-body">${item.content || item.excerpt}</div>
        <a href="/event/${encodeURIComponent(item.slug || item.id)}" class="btn btn-secondary mt-6">Lihat halaman detail</a>
      </div>
    </div>
  ` });
}

})();
