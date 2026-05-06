(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { createModalController, observeFadeUp, unique } = window.GenBIUI;
const API = window.GenBIAPI;
const { news } = window.GenBIData;

renderShell('prestasi');
observeFadeUp();
ensurePrestasiModal();

let active = 'Semua';
let prestasi = [];
const chips = document.querySelector('#prestasi-chips');
const list = document.querySelector('#prestasi-list');
const prestasiModal = createModalController(document.querySelector('#prestasi-modal'));

init();

async function init() {
  list.innerHTML = '<div class="p-8 text-center text-sm text-neutral-600">Memuat data prestasi...</div>';
  prestasi = await API.getPrestasiList();
  renderChips();
  renderPrestasi();
}

function renderChips() {
  const categories = unique(prestasi.map((item) => item.category));
  chips.innerHTML = categories.map((category) => `<button class="chip ${category === active ? 'is-active' : ''}" data-category="${category}">${category}</button>`).join('');
  chips.querySelectorAll('button').forEach((button) => {
    button.addEventListener('click', () => {
      active = button.dataset.category;
      renderChips();
      renderPrestasi();
    });
  });
}

function prestasiImage(item, index) {
  return item.image || news[index % news.length]?.image || 'https://genbijambi.com/public/uploads/slider-1.png';
}

function renderPrestasi() {
  const filtered = active === 'Semua' ? prestasi : prestasi.filter((item) => item.category === active);
  list.innerHTML = filtered.map((item, index) => `
    <button type="button" class="prestasi-row soft-row" data-id="${item.id}">
      <span class="serif prestasi-number">${String(index + 1).padStart(2, '0')}</span>
      <div class="prestasi-row-copy">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
          <span>${item.category}</span><span>•</span><span>${item.year}</span>
        </div>
        <h3 class="serif mt-2 text-2xl font-semibold leading-tight tracking-tight text-neutral-950">${item.title}</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">${item.description}</p>
      </div>
      <div class="prestasi-person">
        <strong>${item.name}</strong><br />${item.campus}
      </div>
    </button>
  `).join('');
  list.querySelectorAll('[data-id]').forEach((button) => button.addEventListener('click', () => openPrestasi(Number(button.dataset.id))));
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
        <img src="${prestasiImage(item, Math.max(index, 0))}" alt="Dokumentasi ${item.title}" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
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
        <p class="mt-5 rounded-2xl bg-blue-50 p-4 text-sm leading-7 text-blue-950">Detail ini memakai data dummy untuk prototipe. Pada implementasi CMS, bagian ini dapat diisi foto kegiatan, deskripsi panjang, penyelenggara lomba, level kompetisi, dan tautan publikasi.</p>
      </div>
    </div>
  ` });
}

})();
