(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { observeFadeUp } = window.GenBIUI;
const { aboutBlocks } = window.GenBIData;

renderShell('about');
if (document.body.dataset.ssr !== 'true') {
  renderAbout();
  renderRoles();
}
observeFadeUp();

function renderAbout() {
  const root = document.querySelector('#about-content');
  root.innerHTML = aboutBlocks.map((block) => `
    <section class="border-t border-neutral-900/10 py-9 first:border-t-0 first:pt-0">
      <h2 class="serif text-3xl font-semibold tracking-tight text-neutral-950">${block.title}</h2>
      <p>${block.text}</p>
    </section>
  `).join('');
}

function renderRoles() {
  const root = document.querySelector('#role-list');
  const roles = [
    { title: 'Frontliners Bank Indonesia', text: 'Mengkomunikasikan kelembagaan dan berbagai kebijakan Bank Indonesia kepada mahasiswa dan masyarakat umum.' },
    { title: 'Change Agents', text: 'Menjadi agen perubahan dan role model di kalangan pelajar, mahasiswa, dan masyarakat.' },
    { title: 'Future Leaders', text: 'Mempersiapkan anggota sebagai pemimpin masa depan di berbagai bidang dan tingkatan.' }
  ];
  root.innerHTML = roles.map((role, index) => `
    <article class="soft-row grid gap-4 p-6 md:grid-cols-[80px_1fr]">
      <span class="serif text-4xl font-semibold text-blue-800">0${index + 1}</span>
      <div>
        <h3 class="text-lg font-bold text-neutral-950">${role.title}</h3>
        <p class="mt-2 text-sm leading-7 text-neutral-600">${role.text}</p>
      </div>
    </article>
  `).join('');
}

})();
