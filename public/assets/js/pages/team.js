(function(){
'use strict';
const { formatInitials, renderShell } = window.GenBIApp;
const { createCustomSelect, createModalController, observeFadeUp, safeImage, unique } = window.GenBIUI;
const API = window.GenBIAPI;

renderShell('team');
observeFadeUp();

let teamMembers = [];
let filterOptions = { divisions: [], campuses: [], years: [] };
let state = {
  query: '',
  division: 'Semua',
  campus: 'Semua',
  year: 'Semua',
  page: 1,
  perPage: 12,
  layout: 'grid'
};

const search = document.querySelector('#team-search');
const count = document.querySelector('#team-count');
const list = document.querySelector('#team-list');
const pagination = document.querySelector('#team-pagination');
const gridButton = document.querySelector('#team-layout-grid');
const listButton = document.querySelector('#team-layout-list');
const memberModal = createModalController(document.querySelector('#member-modal'));

init();

async function init() {
  count.textContent = 'Memuat data anggota...';
  const result = await API.getTeamList();
  teamMembers = result.members;
  filterOptions = result.filters || filterOptions;

  createCustomSelect(document.querySelector('#team-division'), {
    label: 'Divisi',
    options: unique(filterOptions.divisions.length ? filterOptions.divisions : teamMembers.map((member) => member.division)),
    onChange: (value) => { state.division = value; state.page = 1; renderTeam(); }
  });
  createCustomSelect(document.querySelector('#team-campus'), {
    label: 'Komisariat/Kampus',
    options: unique(filterOptions.campuses.length ? filterOptions.campuses : teamMembers.map((member) => member.campus)),
    onChange: (value) => { state.campus = value; state.page = 1; renderTeam(); }
  });
  createCustomSelect(document.querySelector('#team-year'), {
    label: 'Tahun',
    options: unique(filterOptions.years.length ? filterOptions.years : teamMembers.map((member) => member.year)),
    onChange: (value) => { state.year = value; state.page = 1; renderTeam(); }
  });

  search.addEventListener('input', (event) => {
    state.query = event.target.value;
    state.page = 1;
    renderTeam();
  });

  gridButton?.addEventListener('click', () => setLayout('grid'));
  listButton?.addEventListener('click', () => setLayout('list'));

  document.querySelector('#team-reset').addEventListener('click', () => {
    window.location.reload();
  });

  renderTeam();
}

function setLayout(layout) {
  state.layout = layout;
  gridButton?.classList.toggle('is-active', layout === 'grid');
  listButton?.classList.toggle('is-active', layout === 'list');
  renderTeam();
}

function getFilteredMembers() {
  const query = state.query.toLowerCase().trim();
  return teamMembers.filter((member) => {
    const haystack = `${member.name} ${member.role} ${member.division} ${member.campus} ${member.commission} ${member.year}`.toLowerCase();
    return (!query || haystack.includes(query))
      && (state.division === 'Semua' || member.division === state.division)
      && (state.campus === 'Semua' || member.campus === state.campus || member.commission === state.campus)
      && (state.year === 'Semua' || String(member.year) === String(state.year));
  });
}

function renderTeam() {
  const filtered = getFilteredMembers();
  const totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
  state.page = Math.min(state.page, totalPages);
  const start = (state.page - 1) * state.perPage;
  const paged = filtered.slice(start, start + state.perPage);

  count.textContent = filtered.length
    ? `Menampilkan ${start + 1}-${Math.min(start + state.perPage, filtered.length)} dari ${filtered.length} anggota (${teamMembers.length} total).`
    : `Belum ada data yang cocok dari ${teamMembers.length} anggota.`;
  list.className = `fade-up ${state.layout === 'list' ? 'team-public-list' : 'team-public-grid'} in-view`;
  list.innerHTML = paged.length ? paged.map((member) => state.layout === 'list' ? renderListCard(member) : renderGridCard(member)).join('') : `<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada data yang cocok dengan filter.</div>`;

  list.querySelectorAll('.open-member').forEach((button) => {
    button.addEventListener('click', () => openMember(Number(button.dataset.id)));
  });
  renderPagination(totalPages);
}

function renderGridCard(member) {
  return `
    <article class="team-public-card">
      <button class="team-public-photo open-member" data-id="${member.id}" aria-label="Detail ${member.name}">
        ${member.photo ? `<img src="${safeImage(member.photo)}" alt="${member.name}" loading="lazy" onerror="this.replaceWith(document.createTextNode('${formatInitials(member.name)}'))" />` : `<span>${formatInitials(member.name)}</span>`}
      </button>
      <div>
        <p class="eyebrow">${member.year || 'GenBI Jambi'}</p>
        <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${member.name}</h3>
        <p class="mt-2 text-sm font-semibold text-blue-800">${member.role}</p>
        <p class="mt-3 text-sm leading-6 text-neutral-600">${member.division}</p>
        <p class="text-sm leading-6 text-neutral-500">${member.campus}</p>
      </div>
      <button class="btn btn-secondary open-member" data-id="${member.id}">Detail</button>
    </article>
  `;
}

function renderListCard(member) {
  return `
    <article class="team-public-row">
      <button class="team-public-row-photo open-member" data-id="${member.id}" aria-label="Detail ${member.name}">
        ${member.photo ? `<img src="${safeImage(member.photo)}" alt="${member.name}" loading="lazy" onerror="this.replaceWith(document.createTextNode('${formatInitials(member.name)}'))" />` : `<span>${formatInitials(member.name)}</span>`}
      </button>
      <div>
        <h3 class="text-base font-bold text-neutral-950">${member.name}</h3>
        <p class="mt-1 text-sm leading-6 text-neutral-600">${member.role}</p>
      </div>
      <div class="text-sm leading-6 text-neutral-600">
        <p class="font-semibold text-neutral-800">${member.division}</p>
        <p>${member.campus}</p>
      </div>
      <div class="text-sm font-semibold text-neutral-500">${member.year}</div>
      <button class="btn btn-secondary open-member" data-id="${member.id}">Detail</button>
    </article>
  `;
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
      renderTeam();
      list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
}

function openMember(id) {
  const member = teamMembers.find((item) => item.id === id);
  if (!member) return;
  memberModal.open({ content: `
    <div class="public-modal-panel team-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="member-title">
      <div class="flex items-start justify-between gap-5">
        <div class="flex items-center gap-4">
          <span class="team-public-row-photo h-16 w-16">${member.photo ? `<img src="${safeImage(member.photo)}" alt="${member.name}" />` : formatInitials(member.name)}</span>
          <div>
            <p class="eyebrow">Detail anggota</p>
            <h3 id="member-title" class="serif mt-1 text-3xl font-semibold tracking-tight text-neutral-950">${member.name}</h3>
          </div>
        </div>
        <button class="btn-icon modal-close" aria-label="Tutup detail anggota">×</button>
      </div>
      <div class="mt-6 grid gap-3 text-sm leading-7 text-neutral-700">
        <p><strong>Jabatan:</strong> ${member.role}</p>
        <p><strong>Divisi:</strong> ${member.division}</p>
        <p><strong>Komisariat/Kampus:</strong> ${member.campus}</p>
        <p><strong>Tahun:</strong> ${member.year}</p>
        <p><strong>Status:</strong> ${member.status}</p>
        <p class="rounded-2xl bg-white p-4">${member.bio}</p>
      </div>
    </div>
  ` });
}

})();
