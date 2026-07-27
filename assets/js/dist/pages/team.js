(function(){"use strict";const{formatInitials:d,renderShell:x}=window.GenBIApp,{createCustomSelect:m,createModalController:q,observeFadeUp:L,safeImage:g,unique:y}=window.GenBIUI,k=window.GenBIAPI;x("team"),L();const v=document.querySelector('#team-list[data-ssr="true"]');if(v){document.body.classList.add("page-ready");const t=document.querySelector("#team-layout-grid"),a=document.querySelector("#team-layout-list");t?.addEventListener("click",()=>{v.className="fade-up team-public-grid in-view",t.classList.add("is-active"),a?.classList.remove("is-active")}),a?.addEventListener("click",()=>{v.className="fade-up team-public-list in-view",a.classList.add("is-active"),t?.classList.remove("is-active")});const i=document.querySelector("#team-filter-form"),c=document.querySelector("#team-search");c&&i&&c.addEventListener("keydown",o=>{o.key==="Enter"&&(o.preventDefault(),i.submit())});return}let n=[],s={divisions:[],campuses:[],years:[]},e={query:"",division:"Semua",campus:"Semua",year:"Semua",page:1,perPage:12,layout:"grid"};E();const h=document.querySelector("#team-search"),$=document.querySelector("#team-count"),u=document.querySelector("#team-list"),p=document.querySelector("#team-pagination"),f=document.querySelector("#team-layout-grid"),b=document.querySelector("#team-layout-list"),M=q(document.querySelector("#member-modal"));B();async function B(){$.textContent="Memuat data anggota...",h.value=e.query,w();const t=await k.getTeamList();n=t.members,s=t.filters||s,m(document.querySelector("#team-division"),{label:"Divisi",options:y(s.divisions.length?s.divisions:n.map(a=>a.division)),value:e.division,onChange:a=>{e.division=a,e.page=1,l(),r()}}),m(document.querySelector("#team-campus"),{label:"Komisariat/Kampus",options:y(s.campuses.length?s.campuses:n.map(a=>a.campus)),value:e.campus,onChange:a=>{e.campus=a,e.page=1,l(),r()}}),m(document.querySelector("#team-year"),{label:"Tahun",options:y(s.years.length?s.years:n.map(a=>a.year)),value:e.year,onChange:a=>{e.year=a,e.page=1,l(),r()}}),h.addEventListener("input",a=>{e.query=a.target.value,e.page=1,l(),r()}),f?.addEventListener("click",()=>S("grid")),b?.addEventListener("click",()=>S("list")),document.querySelector("#team-reset").addEventListener("click",()=>{window.location.href=window.location.pathname}),r()}function S(t){e.layout=t,l(),w(),r()}function w(){f?.classList.toggle("is-active",e.layout==="grid"),b?.classList.toggle("is-active",e.layout==="list")}function E(){const t=new URLSearchParams(window.location.search),a=t.get("view")||t.get("layout");(a==="grid"||a==="list")&&(e.layout=a),e.query=t.get("q")||"",e.division=t.get("division")||"Semua",e.campus=t.get("campus")||"Semua",e.year=t.get("year")||"Semua",e.page=Math.max(1,Number(t.get("page"))||1)}function l(){const t=new URLSearchParams;e.layout!=="grid"&&t.set("view",e.layout),e.query.trim()&&t.set("q",e.query.trim()),e.division!=="Semua"&&t.set("division",e.division),e.campus!=="Semua"&&t.set("campus",e.campus),e.year!=="Semua"&&t.set("year",e.year),e.page>1&&t.set("page",String(e.page));const a=t.toString(),i=a?`${window.location.pathname}?${a}`:window.location.pathname;window.history.replaceState({},"",i)}function C(){const t=e.query.toLowerCase().trim();return n.filter(a=>{const i=`${a.name} ${a.role} ${a.division} ${a.campus} ${a.commission} ${a.year}`.toLowerCase();return(!t||i.includes(t))&&(e.division==="Semua"||a.division===e.division)&&(e.campus==="Semua"||a.campus===e.campus||a.commission===e.campus)&&(e.year==="Semua"||String(a.year)===String(e.year))})}function r(){const t=C(),a=Math.max(1,Math.ceil(t.length/e.perPage));e.page=Math.min(e.page,a),l();const i=(e.page-1)*e.perPage,c=t.slice(i,i+e.perPage);$.textContent=t.length?`Menampilkan ${i+1}-${Math.min(i+e.perPage,t.length)} dari ${t.length} anggota (${n.length} total).`:`Belum ada data yang cocok dari ${n.length} anggota.`,u.className=`fade-up ${e.layout==="list"?"team-public-list":"team-public-grid"} in-view`,u.innerHTML=c.length?c.map(o=>e.layout==="list"?T(o):I(o)).join(""):'<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada data yang cocok dengan filter.</div>',u.querySelectorAll(".open-member").forEach(o=>{o.addEventListener("click",()=>N(Number(o.dataset.id)))}),D(a)}function I(t){return`
    <article class="team-public-card">
      <button class="team-public-photo open-member" data-id="${t.id}" aria-label="Detail ${t.name}">
        ${t.photo?`<img src="${g(t.photo)}" alt="${t.name}" loading="lazy" onerror="this.replaceWith(document.createTextNode('${d(t.name)}'))" />`:`<span>${d(t.name)}</span>`}
      </button>
      <div>
        <p class="eyebrow">${t.year||"GenBI Jambi"}</p>
        <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${t.name}</h3>
        <p class="mt-2 text-sm font-semibold text-blue-800">${t.role}</p>
        <p class="mt-3 text-sm leading-6 text-neutral-600">${t.division}</p>
        <p class="text-sm leading-6 text-neutral-500">${t.campus}</p>
      </div>
      <button class="btn btn-secondary open-member" data-id="${t.id}">Detail</button>
    </article>
  `}function T(t){return`
    <article class="team-public-row">
      <button class="team-public-row-photo open-member" data-id="${t.id}" aria-label="Detail ${t.name}">
        ${t.photo?`<img src="${g(t.photo)}" alt="${t.name}" loading="lazy" onerror="this.replaceWith(document.createTextNode('${d(t.name)}'))" />`:`<span>${d(t.name)}</span>`}
      </button>
      <div>
        <h3 class="text-base font-bold text-neutral-950">${t.name}</h3>
        <p class="mt-1 text-sm leading-6 text-neutral-600">${t.role}</p>
      </div>
      <div class="text-sm leading-6 text-neutral-600">
        <p class="font-semibold text-neutral-800">${t.division}</p>
        <p>${t.campus}</p>
      </div>
      <div class="text-sm font-semibold text-neutral-500">${t.year}</div>
      <button class="btn btn-secondary open-member" data-id="${t.id}">Detail</button>
    </article>
  `}function D(t){if(!p)return;if(t<=1){p.innerHTML="";return}const a=Array.from({length:t},(i,c)=>c+1);p.innerHTML=`
    <button class="pager-button" type="button" data-page="${Math.max(1,e.page-1)}" ${e.page===1?"disabled":""}>Sebelumnya</button>
    ${a.map(i=>`<button class="pager-button ${i===e.page?"is-active":""}" type="button" data-page="${i}">${i}</button>`).join("")}
    <button class="pager-button" type="button" data-page="${Math.min(t,e.page+1)}" ${e.page===t?"disabled":""}>Berikutnya</button>
  `,p.querySelectorAll("[data-page]").forEach(i=>{i.addEventListener("click",()=>{e.page=Number(i.dataset.page)||1,l(),r(),u.scrollIntoView({behavior:"smooth",block:"start"})})})}function N(t){const a=n.find(i=>i.id===t);a&&M.open({content:`
    <div class="public-modal-panel team-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="member-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail anggota">\xD7</button>
      <div class="team-detail-photo">
        ${a.photo?`<img src="${g(a.photo)}" alt="${a.name}" />`:`<span>${d(a.name)}</span>`}
      </div>
      <div class="team-detail-content">
        <div class="team-detail-heading">
          <p class="eyebrow">Detail anggota</p>
          <h3 id="member-title" class="serif mt-1 text-3xl font-semibold tracking-tight text-neutral-950">${a.name}</h3>
        </div>
        <div class="team-detail-info-grid">
          <div class="team-detail-info-card"><span>Jabatan</span><strong>${a.role}</strong></div>
          <div class="team-detail-info-card"><span>Divisi</span><strong>${a.division}</strong></div>
          <div class="team-detail-info-card"><span>Komisariat/Kampus</span><strong>${a.campus}</strong></div>
          <div class="team-detail-info-card"><span>Tahun</span><strong>${a.year}</strong></div>
        </div>
        ${a.bio?`<div class="team-detail-bio">${a.bio}</div>`:""}
      </div>
    </div>
  `})}})();
