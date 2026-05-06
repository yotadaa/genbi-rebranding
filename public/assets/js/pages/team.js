(function(){
'use strict';
const { formatInitials, renderShell } = window.GenBIApp;
const { createCustomSelect, createModalController, observeFadeUp, unique } = window.GenBIUI;
const API = window.GenBIAPI;

renderShell('team');
observeFadeUp();

let teamMembers = [];
let state = {
  query: '',
  division: 'Semua',
  campus: 'Semua',
  year: 'Semua'
};

const search = document.querySelector('#team-search');
const count = document.querySelector('#team-count');
const list = document.querySelector('#team-list');
const memberModal = createModalController(document.querySelector('#member-modal'));

init();

async function init() {
  count.textContent = 'Memuat data anggota...';
  const result = await API.getTeamList();
  teamMembers = result.members;

  createCustomSelect(document.querySelector('#team-division'), {
    label: 'Divisi',
    options: unique(teamMembers.map((member) => member.division)),
    onChange: (value) => { state.division = value; renderTeam(); }
  });
  createCustomSelect(document.querySelector('#team-campus'), {
    label: 'Kampus',
    options: unique(teamMembers.map((member) => member.campus)),
    onChange: (value) => { state.campus = value; renderTeam(); }
  });
  createCustomSelect(document.querySelector('#team-year'), {
    label: 'Tahun',
    options: unique(teamMembers.map((member) => member.year)),
    onChange: (value) => { state.year = value; renderTeam(); }
  });

  search.addEventListener('input', (event) => {
    state.query = event.target.value;
    renderTeam();
  });

  document.querySelector('#team-reset').addEventListener('click', () => {
    window.location.reload();
  });

  renderTeam();
}

function renderTeam() {
  const query = state.query.toLowerCase().trim();
  const filtered = teamMembers.filter((member) => {
    const haystack = `${member.name} ${member.role} ${member.division} ${member.campus} ${member.commission}`.toLowerCase();
    return (!query || haystack.includes(query))
      && (state.division === 'Semua' || member.division === state.division)
      && (state.campus === 'Semua' || member.campus === state.campus)
      && (state.year === 'Semua' || member.year === state.year);
  });
  count.textContent = `Menampilkan ${filtered.length} anggota dari ${teamMembers.length} data.`;
  list.innerHTML = filtered.length ? filtered.map((member) => `
    <article class="soft-row grid gap-4 p-5 md:grid-cols-[70px_1fr_170px_110px] md:items-center md:p-6">
      <button class="avatar open-member" data-id="${member.id}" aria-label="Detail ${member.name}">${formatInitials(member.name)}</button>
      <div>
        <h3 class="text-base font-bold text-neutral-950">${member.name}</h3>
        <p class="mt-1 text-sm leading-6 text-neutral-600">${member.role}</p>
      </div>
      <div class="text-sm leading-6 text-neutral-600">
        <p class="font-semibold text-neutral-800">${member.division}</p>
        <p>${member.campus}</p>
      </div>
      <button class="btn btn-secondary open-member" data-id="${member.id}">Detail</button>
    </article>
  `).join('') : `<div class="p-8 text-center text-sm text-neutral-600">Belum ada data yang cocok dengan filter.</div>`;

  list.querySelectorAll('.open-member').forEach((button) => {
    button.addEventListener('click', () => openMember(Number(button.dataset.id)));
  });
}

function openMember(id) {
  const member = teamMembers.find((item) => item.id === id);
  memberModal.open({ content: `
    <div class="public-modal-panel team-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="member-title">
      <div class="flex items-start justify-between gap-5">
        <div class="flex items-center gap-4">
          <span class="avatar h-16 w-16">${formatInitials(member.name)}</span>
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
        <p><strong>Kampus:</strong> ${member.campus}</p>
        <p><strong>Komisariat:</strong> ${member.commission}</p>
        <p><strong>Status:</strong> ${member.status}</p>
        <p class="rounded-2xl bg-white p-4">${member.bio}</p>
      </div>
    </div>
  ` });
}

})();
