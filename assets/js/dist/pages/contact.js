(function(){"use strict";const{renderShell:n}=window.GenBIApp,{observeFadeUp:s}=window.GenBIUI,t={...window.GenBIData.site||{},...window.GenBISiteSettings||{}};n("contact"),o(),r(),s();function o(){const e=document.querySelector("#contact-info");e&&(e.innerHTML=`
    <div class="soft-card p-6">
      <p class="eyebrow">Alamat</p>
      <p class="mt-4 text-base leading-7 text-neutral-700">${t.address}</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
      <a href="tel:${t.phone}" class="soft-card block p-6 hover:bg-blue-50/60">
        <p class="eyebrow">Telepon</p>
        <p class="mt-4 font-semibold text-neutral-950">${t.phone}</p>
      </a>
      <a href="mailto:${t.email}" class="soft-card block p-6 hover:bg-blue-50/60">
        <p class="eyebrow">Email</p>
        <p class="mt-4 break-all font-semibold text-neutral-950">${t.email}</p>
      </a>
    </div>
  `)}function r(){const e=document.querySelector("#contact-form"),a=document.querySelector("#form-message");!e||!a||e.addEventListener("submit",i=>{i.preventDefault();const l=new FormData(e),c=["name","phone","email","subject","message"].filter(d=>!String(l.get(d)||"").trim());if(a.classList.remove("hidden"),c.length){a.textContent="Lengkapi semua kolom terlebih dahulu.";return}a.textContent="Pesan berhasil disiapkan dalam mode prototipe. Integrasi backend bisa ditambahkan nanti.",e.reset()})}})();
