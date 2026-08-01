(function(){"use strict";const{renderShell:n}=window.GenBIApp,{observeFadeUp:i}=window.GenBIUI,{aboutBlocks:s}=window.GenBIData;n("about"),o(),r(),i();function o(){const e=document.querySelector("#about-content");e.innerHTML=s.map(t=>`
    <section class="border-t border-neutral-900/10 py-9 first:border-t-0 first:pt-0">
      <h2 class="serif text-3xl font-semibold tracking-tight text-neutral-950">${t.title}</h2>
      <p>${t.text}</p>
    </section>
  `).join("")}function r(){const e=document.querySelector("#role-list"),t=[{title:"Frontliners Bank Indonesia",text:"Mengkomunikasikan kelembagaan dan berbagai kebijakan Bank Indonesia kepada mahasiswa dan masyarakat umum."},{title:"Change Agents",text:"Menjadi agen perubahan dan role model di kalangan pelajar, mahasiswa, dan masyarakat."},{title:"Future Leaders",text:"Mempersiapkan anggota sebagai pemimpin masa depan di berbagai bidang dan tingkatan."}];e.innerHTML=t.map((a,l)=>`
    <article class="soft-row grid gap-4 p-6 md:grid-cols-[80px_1fr]">
      <span class="serif text-4xl font-semibold text-blue-800">0${l+1}</span>
      <div>
        <h3 class="text-lg font-bold text-neutral-950">${a.title}</h3>
        <p class="mt-2 text-sm leading-7 text-neutral-600">${a.text}</p>
      </div>
    </article>
  `).join("")}})();
