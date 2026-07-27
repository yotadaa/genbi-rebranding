(function(){
'use strict';
const { renderShell } = window.GenBIApp;
const { observeFadeUp } = window.GenBIUI;
const fallbackSite = window.GenBIData.site || {};
const site = {
  ...fallbackSite,
  ...(window.GenBISiteSettings || {}),
};

renderShell('contact');
renderContactInfoFallback();
setupForm();
observeFadeUp();

function renderContactInfoFallback() {
  const root = document.querySelector('#contact-info');
  if (!root) return;
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
  `;
}

function setupForm() {
  const form = document.querySelector('#contact-form');
  const message = document.querySelector('#form-message');
  if (!form || !message) return;
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
