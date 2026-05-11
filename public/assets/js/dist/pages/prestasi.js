(function(){"use strict";const{renderShell:f}=window.GenBIApp,{createModalController:c,observeFadeUp:p}=window.GenBIUI,b=window.GenBIAPI,{news:r}=window.GenBIData;f("prestasi");const d=document.querySelector('#prestasi-list[data-ssr="true"]');if(d){document.body.classList.add("page-ready"),p(),x();const t=c(document.querySelector("#prestasi-modal"));document.querySelectorAll("[data-prestasi-layout]").forEach(e=>{e.addEventListener("click",()=>{const a=e.dataset.prestasiLayout||"list";document.querySelectorAll("[data-prestasi-layout]").forEach(s=>s.classList.toggle("is-active",s===e)),d.className=a==="grid"?"prestasi-grid":"soft-card overflow-hidden prestasi-list"})}),d.querySelectorAll("[data-id]").forEach(e=>{e.addEventListener("click",()=>{const a={title:e.dataset.title||"",name:e.dataset.name||"",campus:e.dataset.campus||"",category:e.dataset.category||"",year:e.dataset.year||"",description:e.dataset.description||"",detail:e.dataset.detail||e.dataset.description||"",image:e.dataset.image||"",institution:e.dataset.institution||""},s=r[Number(e.dataset.index)%r.length]?.image||"https://genbijambi.com/public/uploads/slider-1.png",i=a.image||s;t.open({content:`
        <div class="public-modal-panel prestasi-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="prestasi-title">
          <button class="btn-icon modal-close" aria-label="Tutup detail prestasi">\xD7</button>
          <figure class="prestasi-detail-image">
            <img src="${i}" alt="Dokumentasi ${a.title}" onerror="this.src='${s}'" />
          </figure>
          <div class="prestasi-detail-copy">
            <p class="eyebrow">Detail prestasi</p>
            <h2 id="prestasi-title" class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">${a.title}</h2>
            <p class="mt-4 text-base leading-7 text-neutral-600">${a.description}</p>
            <div class="prestasi-detail-meta">
              <p><span>Nama</span><strong>${a.name}</strong></p>
              <p><span>Kampus</span><strong>${a.campus}</strong></p>
              <p><span>Kategori</span><strong>${a.category}</strong></p>
              <p><span>Tahun</span><strong>${a.year}</strong></p>
            </div>
            <p class="mt-5 rounded-2xl bg-blue-50 p-4 text-sm leading-7 text-blue-950">${a.detail||a.description}</p>
          </div>
        </div>
      `})})});return}x();let n=[],o="list";const l=document.querySelector("#prestasi-list"),y=c(document.querySelector("#prestasi-modal"));v(),document.querySelectorAll("[data-prestasi-layout]").forEach(t=>{t.addEventListener("click",()=>{o=t.dataset.prestasiLayout||"list",document.querySelectorAll("[data-prestasi-layout]").forEach(e=>e.classList.toggle("is-active",e===t)),m()})});async function v(){l.innerHTML='<div class="p-8 text-center text-sm text-neutral-600">Memuat data prestasi...</div>',n=await b.getPrestasiList(),m()}function u(t,e){return t.image||r[e%r.length]?.image||"https://genbijambi.com/public/uploads/slider-1.png"}function g(t,e,a){const s=e?.raw?.photo||e?.raw?.image||"",i=h(s),I=r[a%r.length]?.image||"https://genbijambi.com/public/uploads/slider-1.png";if(i&&!t.dataset.driveFallback){t.dataset.driveFallback="thumbnail",t.src=`https://drive.google.com/uc?export=view&id=${encodeURIComponent(i)}`;return}if(i&&t.dataset.driveFallback==="thumbnail"){t.dataset.driveFallback="download",t.src=`https://drive.google.com/uc?export=download&id=${encodeURIComponent(i)}`;return}t.onerror=null,t.src=I}function h(t=""){const e=String(t||"");return/(drive\.google\.com|docs\.google\.com)/i.test(e)&&(e.match(/[?&]id=([-\w]{10,})/i)?.[1]||e.match(/\/file\/d\/([-\w]{10,})/i)?.[1]||e.match(/[-\w]{25,}/)?.[0])||""}function m(){l.className=o==="grid"?"prestasi-grid":"soft-card overflow-hidden prestasi-list",l.innerHTML=n.map((t,e)=>o==="grid"?w(t,e):$(t,e)).join(""),l.querySelectorAll(".prestasi-grid-image img").forEach(t=>{const e=Number(t.closest("[data-index]")?.dataset.index||0),a=n[e];t.addEventListener("error",()=>g(t,a,e))}),l.querySelectorAll("[data-id]").forEach(t=>t.addEventListener("click",()=>k(Number(t.dataset.id)))),p()}function $(t,e){return`
    <button type="button" class="prestasi-row soft-row" data-id="${t.id}" data-index="${e}">
      <span class="serif prestasi-number">${String(e+1).padStart(2,"0")}</span>
      <div class="prestasi-row-copy">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
          <span>${t.category}</span><span>\u2022</span><span>${t.year}</span>
        </div>
        <h3 class="serif mt-2 text-2xl font-semibold leading-tight tracking-tight text-neutral-950">${t.title}</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">${t.description}</p>
      </div>
      <div class="prestasi-person">
        <strong>${t.name}</strong><br />${t.campus||t.institution||""}
      </div>
    </button>
  `}function w(t,e){return`
    <button type="button" class="prestasi-grid-card soft-card" data-id="${t.id}" data-index="${e}">
      <figure class="prestasi-grid-image">
        <img src="${u(t,e)}" alt="Dokumentasi ${t.title}" loading="lazy" />
      </figure>
      <div class="prestasi-grid-copy">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
          <span>${t.category}</span><span>\u2022</span><span>${t.year}</span>
        </div>
        <h3 class="serif mt-3 text-2xl font-semibold leading-tight tracking-tight text-neutral-950">${t.title}</h3>
        <p class="mt-3 text-sm leading-7 text-neutral-600">${t.description}</p>
        <p class="mt-4 rounded-2xl bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-950"><strong>${t.name}</strong><br />${t.campus||t.institution||""}</p>
      </div>
    </button>
  `}function x(){if(document.querySelector("#prestasi-modal"))return;const t=document.createElement("div");t.id="prestasi-modal",t.className="public-fixed-modal hidden",document.body.appendChild(t)}function k(t){const e=n.find(i=>i.id===t)||n[0],a=n.findIndex(i=>i.id===t);y.open({content:`
    <div class="public-modal-panel prestasi-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="prestasi-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail prestasi">\xD7</button>
      <figure class="prestasi-detail-image">
        <img src="${u(e,Math.max(a,0))}" alt="Dokumentasi ${e.title}" />
      </figure>
      <div class="prestasi-detail-copy">
        <p class="eyebrow">Detail prestasi</p>
        <h2 id="prestasi-title" class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">${e.title}</h2>
        <p class="mt-4 text-base leading-7 text-neutral-600">${e.description}</p>
        <div class="prestasi-detail-meta">
          <p><span>Nama</span><strong>${e.name}</strong></p>
          <p><span>Kampus</span><strong>${e.campus}</strong></p>
          <p><span>Kategori</span><strong>${e.category}</strong></p>
          <p><span>Tahun</span><strong>${e.year}</strong></p>
        </div>
        <p class="mt-5 rounded-2xl bg-blue-50 p-4 text-sm leading-7 text-blue-950">${e.detail||e.description}</p>
      </div>
    </div>
  `});const s=document.querySelector("#prestasi-modal .prestasi-detail-image img");s?.addEventListener("error",()=>g(s,e,Math.max(a,0)))}})();
