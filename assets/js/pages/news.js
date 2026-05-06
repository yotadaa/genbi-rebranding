(function(){
'use strict';
const { newsDetailUrl, renderShell } = window.GenBIApp;
const { createCustomSelect, observeFadeUp, unique } = window.GenBIUI;
const API = window.GenBIAPI;

renderShell('news');
observeFadeUp();

let state = { query: '', category: 'Semua' };
let news = [];
const search = document.querySelector('#news-search');
const list = document.querySelector('#news-list');
const count = document.querySelector('#news-count');

init();

search.addEventListener('input', (event) => {
  state.query = event.target.value;
  renderNews();
});

async function init() {
  count.textContent = 'Memuat berita...';
  news = await API.getNewsList();
  createCustomSelect(document.querySelector('#news-category'), {
    label: 'Kategori',
    options: unique(news.map((item) => item.category)),
    onChange: (value) => { state.category = value; renderNews(); }
  });
  renderNews();
}

function renderNews() {
  const query = state.query.toLowerCase().trim();
  const filtered = news.filter((item) => {
    const haystack = `${item.title} ${item.category} ${item.excerpt}`.toLowerCase();
    return (!query || haystack.includes(query)) && (state.category === 'Semua' || item.category === state.category);
  });
  count.textContent = `Menampilkan ${filtered.length} berita.`;
  list.innerHTML = filtered.length ? filtered.map((item, index) => `
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
}

})();
