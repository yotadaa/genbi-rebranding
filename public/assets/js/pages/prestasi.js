(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { createModalController } = window.GenBIUI;
const API = window.GenBIAPI;
const { news } = window.GenBIData;

renderShell('prestasi');

// Check if SSR markup exists - if so, only bind layout toggle and modal
const ssrList = document.querySelector('#prestasi-list[data-ssr="true"]');
if (ssrList) {
  document.body.classList.add('page-ready');
  // Bind layout toggle buttons
  document.querySelectorAll('[data-prestasi-layout]').forEach((button) => {
    button.addEventListener('click', () => {
      const layout = button.dataset.prestasiLayout || 'list';
      document.querySelectorAll('[data-prestasi-layout]').forEach((item) => item.classList.toggle('is-active', item === button));
      ssrList.className = layout === 'grid' ? 'prestasi-grid' : 'soft-card overflow-hidden prestasi-list';
    });
  });
  return;
}

ensurePrestasiModal();

let prestasi = [];
let layout = 'list';
const list = document.querySelector('#prestasi-list');
const prestasiModal = createModalController(document.querySelector('#prestasi-modal'));

init();

document.querySelectorAll('[data-prestasi-layout]').forEach((button) => {
  button.addEventListener('click', () => {
    layout = button.dataset.prestasiLayout || 'list';
    document.querySelectorAll('[data-prestasi-layout]').forEach((item) => item.classList.toggle('is-active', item === button));
    renderPrestasi();
  });
});

async function init() {
  list.innerHTML = '<div class="p-8 text-center text-sm text-neutral-600">Memuat data prestasi...</div>';
  prestasi = await API.getPrestasiList();
  renderPrestasi();
}

function prestasiImage(item, index) {
  return item.image || news[index % news.length]?.image || 'https://genbijambi.com/public/uploads/slider-1.png';
}

function imageFallback(img, item, index) {
  const rawImage = item?.raw?.photo || item?.raw?.image || '';
  const driveId = extractDriveId(rawImage);
  const fallback = news[index % news.length]?.image || 'https://genbijambi.com/public/uploads/slider-1.png';

  if (driveId && !img.dataset.driveFallback) {
    img.dataset.driveFallback = 'thumbnail';
    img.src = `https://drive.google.com/uc?export=view&id=${encodeURIComponent(driveId)}`;
    return;
  }

  if (driveId && img.dataset.driveFallback === 'thumbnail') {
    img.dataset.driveFallback = 'download';
    img.src = `https://drive.google.com/uc?export=download&id=${encodeURIComponent(driveId)}`;
    return;
  }

  img.onerror = null;
  img.src = fallback;
}

function extractDriveId(value = '') {
  const text = String(value || '');
  if (!/(drive\.google\.com|docs\.google\.com)/i.test(text)) return '';
  return text.match(/[?&]id=([-\w]{10,})/i)?.[1]
    || text.match(/\/file\/d\/([-\w]{10,})/i)?.[1]
    || text.match(/[-\w]{25,}/)?.[0]
    || '';
}

function renderPrestasi() {
  list.className = layout === 'grid' ? 'prestasi-grid' : 'soft-card overflow-hidden prestasi-list';
  list.innerHTML = prestasi.map((item, index) => layout === 'grid' ? renderGridItem(item, index) : renderListItem(item, index)).join('');
  list.querySelectorAll('.prestasi-grid-image img').forEach((image) => {
    const index = Number(image.closest('[data-index]')?.dataset.index || 0);
    const item = prestasi[index];
    image.addEventListener('error', () => imageFallback(image, item, index));
  });
  list.querySelectorAll('[data-id]').forEach((button) => button.addEventListener('click', () => openPrestasi(Number(button.dataset.id))));
}

function renderListItem(item, index) {
  return `
    <button type="button" class="prestasi-row soft-row" data-id="${item.id}" data-index="${index}">
      <span class="serif prestasi-number">${String(index + 1).padStart(2, '0')}</span>
      <div class="prestasi-row-copy">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
          <span>${item.category}</span><span>•</span><span>${item.year}</span>
        </div>
        <h3 class="serif mt-2 text-2xl font-semibold leading-tight tracking-tight text-neutral-950">${item.title}</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">${item.description}</p>
      </div>
      <div class="prestasi-person">
        <strong>${item.name}</strong><br />${item.campus || item.institution || ''}
      </div>
    </button>
  `;
}

function renderGridItem(item, index) {
  return `
    <button type="button" class="prestasi-grid-card soft-card" data-id="${item.id}" data-index="${index}">
      <figure class="prestasi-grid-image">
        <img src="${prestasiImage(item, index)}" alt="Dokumentasi ${item.title}" loading="lazy" />
      </figure>
      <div class="prestasi-grid-copy">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
          <span>${item.category}</span><span>•</span><span>${item.year}</span>
        </div>
        <h3 class="serif mt-3 text-2xl font-semibold leading-tight tracking-tight text-neutral-950">${item.title}</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">${item.description}</p>
        <p class="mt-4 rounded-2xl bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-950"><strong>${item.name}</strong><br />${item.campus || item.institution || ''}</p>
      </div>
    </button>
  `;
}

function ensurePrestasiModal() {
  if (document.querySelector('#prestasi-modal')) return;
  const modal = document.createElement('div');
  modal.id = 'prestasi-modal';
  modal.className = 'public-fixed-modal hidden';
  document.body.appendChild(modal);
}

function openPrestasi(id) {
  const item = prestasi.find((entry) => entry.id === id) || prestasi[0];
  const index = prestasi.findIndex((entry) => entry.id === id);
  prestasiModal.open({ content: `
    <div class="public-modal-panel prestasi-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="prestasi-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail prestasi">×</button>
      <figure class="prestasi-detail-image">
        <img src="${prestasiImage(item, Math.max(index, 0))}" alt="Dokumentasi ${item.title}" />
      </figure>
      <div class="prestasi-detail-copy">
        <p class="eyebrow">Detail prestasi</p>
        <h2 id="prestasi-title" class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">${item.title}</h2>
        <p class="mt-4 text-base leading-7 text-neutral-600">${item.description}</p>
        <div class="prestasi-detail-meta">
          <p><span>Nama</span><strong>${item.name}</strong></p>
          <p><span>Kampus</span><strong>${item.campus}</strong></p>
          <p><span>Kategori</span><strong>${item.category}</strong></p>
          <p><span>Tahun</span><strong>${item.year}</strong></p>
        </div>
        <p class="mt-5 rounded-2xl bg-blue-50 p-4 text-sm leading-7 text-blue-950">${item.detail || item.description}</p>
      </div>
    </div>
  ` });
  const image = document.querySelector('#prestasi-modal .prestasi-detail-image img');
  image?.addEventListener('error', () => imageFallback(image, item, Math.max(index, 0)));
}

})();
