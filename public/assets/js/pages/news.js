(function(){
'use strict';
const { newsDetailUrl, renderShell } = window.GenBIApp;
const { createCustomSelect, observeFadeUp, unique } = window.GenBIUI;
const API = window.GenBIAPI;

renderShell('news');
observeFadeUp();

// Check if SSR markup exists - if so, skip client-side rendering
const list = document.querySelector('#news-list');
if (list?.dataset.ssr === 'true') {
  document.body.classList.add('page-ready');
  return;
}

let state = { query: '', category: 'Semua', page: 1, perPage: 12 };
let news = [];
const search = document.querySelector('#news-search');
const count = document.querySelector('#news-count');
const pagination = document.querySelector('#news-pagination');

init();

search.addEventListener('input', (event) => {
  state.query = event.target.value;
  state.page = 1;
  renderNews();
});

async function init() {
  count.textContent = 'Memuat berita...';
  news = await API.getNewsList();
  createCustomSelect(document.querySelector('#news-category'), {
    label: 'Kategori',
    options: unique(news.map((item) => item.category)),
    onChange: (value) => { state.category = value; state.page = 1; renderNews(); }
  });
  renderNews();
}

function renderNews() {
  const query = state.query.toLowerCase().trim();
  const filtered = news.filter((item) => {
    const haystack = `${item.title} ${item.category} ${item.excerpt}`.toLowerCase();
    return (!query || haystack.includes(query)) && (state.category === 'Semua' || item.category === state.category);
  });
  const totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
  state.page = Math.min(state.page, totalPages);
  const start = (state.page - 1) * state.perPage;
  const paged = filtered.slice(start, start + state.perPage);
  count.textContent = filtered.length
    ? `Menampilkan ${start + 1}-${Math.min(start + state.perPage, filtered.length)} dari ${filtered.length} berita.`
    : 'Tidak ada berita yang cocok.';
  list.innerHTML = paged.length ? paged.map((item, index) => `
    <a data-transition href="${newsDetailUrl(item)}" class="article-link ${index === 0 ? 'pt-0 border-t-0' : ''}">
      <div class="grid gap-5 md:grid-cols-[170px_1fr] md:items-start">
        <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-blue-50">
          <img src="${item.image}" alt="${item.title}" class="h-full w-full object-cover" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
            <span class="text-blue-800">${item.category}</span><span>${item.date}</span><span>${item.readTime}</span>
          </div>
          <h3 class="serif mt-3 text-3xl font-semibold leading-tight tracking-tight text-neutral-950">${item.title}</h3>
          <p class="mt-4 text-base leading-7 text-neutral-600">${item.excerpt}</p>
          <span class="btn btn-secondary mt-5 w-fit">Detail</span>
        </div>
      </div>
    </a>
  `).join('') : `<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada data berita yang cocok.</div>`;
  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  if (!pagination) return;
  if (totalPages <= 1) {
    pagination.innerHTML = '';
    return;
  }
  const pages = Array.from({ length: totalPages }, (_, index) => index + 1);
  pagination.innerHTML = `
    <button class="pager-button" type="button" data-page="${Math.max(1, state.page - 1)}" ${state.page === 1 ? 'disabled' : ''}>Sebelumnya</button>
    ${pages.map((page) => `<button class="pager-button ${page === state.page ? 'is-active' : ''}" type="button" data-page="${page}">${page}</button>`).join('')}
    <button class="pager-button" type="button" data-page="${Math.min(totalPages, state.page + 1)}" ${state.page === totalPages ? 'disabled' : ''}>Berikutnya</button>
  `;
  pagination.querySelectorAll('[data-page]').forEach((button) => {
    button.addEventListener('click', () => {
      state.page = Number(button.dataset.page) || 1;
      renderNews();
      list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
}

})();
