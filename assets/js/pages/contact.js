(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { observeFadeUp } = window.GenBIUI;
const { site } = window.GenBIData;

renderShell('contact');
renderContactInfo();
setupForm();
observeFadeUp();

function renderContactInfo() {
  const root = document.querySelector('#contact-info');
  root.innerHTML = `
    <div class="soft-card p-6">
      <p class="eyebrow">Alamat</p>
      <p class="mt-4 text-base leading-7 text-neutral-700">${site.address}</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
      <a href="tel:${site.phone}" class="soft-card block p-6 hover:bg-blue-50/60">
        <p class="eyebrow">Telepon</p>
        <p class="mt-4 font-semibold text-neutral-950">${site.phone}</p>
      </a>
      <a href="mailto:${site.email}" class="soft-card block p-6 hover:bg-blue-50/60">
        <p class="eyebrow">Email</p>
        <p class="mt-4 break-all font-semibold text-neutral-950">${site.email}</p>
      </a>
    </div>
    <div class="relative min-h-72 overflow-hidden rounded-[1.75rem] border border-neutral-900/10 bg-blue-50">
      <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(17,75,154,0.10)_1px,transparent_1px),linear-gradient(rgba(17,75,154,0.10)_1px,transparent_1px)] bg-[size:26px_26px]"></div>
      <div class="absolute inset-5 rounded-[1.5rem] bg-white/75 p-5 backdrop-blur-sm">
        <span class="blue-badge">Map preview</span>
        <h3 class="serif mt-4 text-3xl font-semibold tracking-tight text-neutral-950">Bank Indonesia Jambi</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">Area peta dibuat dummy agar prototipe tetap ringan. Embed Google Maps bisa ditambahkan saat deploy.</p>
      </div>
    </div>
  `;
}

function setupForm() {
  const form = document.querySelector('#contact-form');
  const message = document.querySelector('#form-message');
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(form);
    const missing = ['name', 'phone', 'email', 'subject', 'message'].filter((field) => !String(formData.get(field) || '').trim());
    message.classList.remove('hidden');
    if (missing.length) {
      message.textContent = 'Lengkapi semua kolom terlebih dahulu.';
      return;
    }
    message.textContent = 'Pesan berhasil disiapkan dalam mode prototipe. Integrasi backend bisa ditambahkan nanti.';
    form.reset();
  });
}

})();
