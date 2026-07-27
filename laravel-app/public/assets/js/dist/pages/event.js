(function(){"use strict";const{renderShell:b}=window.GenBIApp,{createModalController:g,observeFadeUp:h,safeImage:y}=window.GenBIUI,$=window.GenBIAPI;b("event");const v=document.querySelector('#event-list[data-ssr="true"]');if(v){const e=document.querySelector("#event-search");e&&e.addEventListener("keydown",o=>{o.key==="Enter"&&(o.preventDefault(),document.querySelector("#event-filter-form")?.submit())});const t=g(document.querySelector("#event-modal"));v.querySelectorAll(".open-event").forEach(o=>{o.addEventListener("click",d=>{d.preventDefault();const l=Number(o.dataset.id),c=o.dataset.slug||"";l>0&&m({id:l,slug:c},t)})})}else{let q=function(){const a=t.query.toLowerCase().trim();return e.filter(r=>{const s=`${r.title} ${r.excerpt} ${r.location} ${r.status}`.toLowerCase();return!a||s.includes(a)})},p=function(){const a=q(),r=Math.max(1,Math.ceil(a.length/t.perPage));t.page=Math.min(t.page,r);const s=(t.page-1)*t.perPage,i=a.slice(s,s+t.perPage);d&&(d.textContent=a.length?`Menampilkan ${s+1}-${Math.min(s+t.perPage,a.length)} dari ${a.length} event.`:"Tidak ada event yang cocok."),l&&(l.innerHTML=i.length?i.map(n=>`
        <article class="event-card">
          <div class="event-card-image">
            <img src="${y(n.image)}" alt="${n.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
            <span class="event-card-badge ${n.status==="Upcoming"?"upcoming":""}">${n.status}</span>
          </div>
          <div class="event-card-body">
            <p class="eyebrow">${n.start_date}${n.end_date&&n.end_date!==n.start_date?" \u2013 "+n.end_date:""}</p>
            <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${n.title}</h3>
            <p class="mt-3 text-sm leading-6 text-neutral-600">${n.excerpt}</p>
            ${n.location?`<p class="mt-2 text-sm font-semibold text-blue-800">${n.location}</p>`:""}
          </div>
          <button class="btn btn-secondary open-event" data-id="${n.id}" data-slug="${n.slug||""}">Detail</button>
        </article>
      `).join(""):'<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada event yang cocok.</div>',l.querySelectorAll(".open-event").forEach(n=>{n.addEventListener("click",()=>M({id:Number(n.dataset.id),slug:n.dataset.slug||""},w))})),k(r)},k=function(a){if(!c)return;if(a<=1){c.innerHTML="";return}const r=Array.from({length:a},(s,i)=>i+1);c.innerHTML=`
      <button class="pager-button" type="button" data-page="${Math.max(1,t.page-1)}" ${t.page===1?"disabled":""}>Sebelumnya</button>
      ${r.map(s=>`<button class="pager-button ${s===t.page?"is-active":""}" type="button" data-page="${s}">${s}</button>`).join("")}
      <button class="pager-button" type="button" data-page="${Math.min(a,t.page+1)}" ${t.page===a?"disabled":""}>Berikutnya</button>
    `,c.querySelectorAll("[data-page]").forEach(s=>{s.addEventListener("click",()=>{t.page=Number(s.dataset.page)||1,p(),l&&l.scrollIntoView({behavior:"smooth",block:"start"})})})},M=function(a,r){const s=e.find(i=>i.id===a.id||i.slug===a.slug);s&&f(s,r)};h();let e=[],t={query:"",page:1,perPage:9};const o=document.querySelector("#event-search"),d=document.querySelector("#event-count"),l=document.querySelector("#event-list"),c=document.querySelector("#event-pagination"),w=g(document.querySelector("#event-modal"));x();async function x(){d&&(d.textContent="Memuat data event..."),e=await $.getEventList(),o&&o.addEventListener("input",a=>{t.query=a.target.value,t.page=1,p()}),document.querySelector("#event-reset")?.addEventListener("click",()=>{o&&(o.value=""),t.query="",t.page=1,p()}),p()}}function u(e){return`/event/${encodeURIComponent(e.slug||e.id||"")}`}async function m(e,t){try{const o=await fetch(u(e),{headers:{Accept:"application/json"}});if(!o.ok){window.location.href=u(e);return}const l=(await o.json()).data;if(!l){window.location.href=u(e);return}f(l,t)}catch{window.location.href=u(e)}}function f(e,t){const o=window.GenBIUI?.safeImage||(d=>d||"https://genbijambi.com/public/uploads/slider-1.png");t.open({content:`
    <div class="public-modal-panel event-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="event-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail event">\xD7</button>
      <div class="event-detail-image">
        <img src="${o(e.banner||e.image)}" alt="${e.title}" />
      </div>
      <div class="event-detail-content">
        <div class="event-detail-heading">
          <span class="event-card-badge ${e.status==="Upcoming"?"upcoming":""}">${e.status}</span>
          <h3 id="event-title" class="serif mt-2 text-3xl font-semibold tracking-tight text-neutral-950">${e.title}</h3>
        </div>
        <div class="event-detail-info-grid">
          <div class="event-detail-info-card"><span>Tanggal</span><strong>${e.start_date}${e.end_date&&e.end_date!==e.start_date?" \u2013 "+e.end_date:""}</strong></div>
          ${e.location?`<div class="event-detail-info-card"><span>Lokasi</span><strong>${e.location}</strong></div>`:""}
        </div>
        <div class="event-detail-body">${e.content||e.excerpt}</div>
        <a href="/event/${encodeURIComponent(e.slug||e.id)}" class="btn btn-secondary mt-6">Lihat halaman detail</a>
      </div>
    </div>
  `})}})();
