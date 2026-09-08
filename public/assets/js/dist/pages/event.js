(function(){"use strict";const{renderShell:h}=window.GenBIApp,{createModalController:g,observeFadeUp:v,safeImage:y}=window.GenBIUI,$=window.GenBIAPI;h("event");const f=document.querySelector('#event-list[data-ssr="true"]');if(document.body.dataset.ssr==="true"&&!document.querySelector("#event-list"))document.body.classList.add("page-ready"),v();else if(f){const e=document.querySelector("#event-search");e&&e.addEventListener("keydown",s=>{s.key==="Enter"&&(s.preventDefault(),document.querySelector("#event-filter-form")?.submit())});const t=g(document.querySelector("#event-modal"));f.querySelectorAll(".open-event").forEach(s=>{s.addEventListener("click",r=>{r.preventDefault();const d=Number(s.dataset.id),i=s.dataset.slug||"";d>0&&m({id:d,slug:i},t)})})}else{let q=function(){const a=t.query.toLowerCase().trim();return e.filter(l=>{const o=`${l.title} ${l.excerpt} ${l.location} ${l.status}`.toLowerCase();return!a||o.includes(a)})},p=function(){const a=q(),l=Math.max(1,Math.ceil(a.length/t.perPage));t.page=Math.min(t.page,l);const o=(t.page-1)*t.perPage,c=a.slice(o,o+t.perPage);r&&(r.textContent=a.length?`Menampilkan ${o+1}-${Math.min(o+t.perPage,a.length)} dari ${a.length} event.`:"Tidak ada event yang cocok."),d&&(d.innerHTML=c.length?c.map(n=>`
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
      `).join(""):'<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada event yang cocok.</div>',d.querySelectorAll(".open-event").forEach(n=>{n.addEventListener("click",()=>S({id:Number(n.dataset.id),slug:n.dataset.slug||""},w))})),k(l)},k=function(a){if(!i)return;if(a<=1){i.innerHTML="";return}const l=Array.from({length:a},(o,c)=>c+1);i.innerHTML=`
      <button class="pager-button" type="button" data-page="${Math.max(1,t.page-1)}" ${t.page===1?"disabled":""}>Sebelumnya</button>
      ${l.map(o=>`<button class="pager-button ${o===t.page?"is-active":""}" type="button" data-page="${o}">${o}</button>`).join("")}
      <button class="pager-button" type="button" data-page="${Math.min(a,t.page+1)}" ${t.page===a?"disabled":""}>Berikutnya</button>
    `,i.querySelectorAll("[data-page]").forEach(o=>{o.addEventListener("click",()=>{t.page=Number(o.dataset.page)||1,p(),d&&d.scrollIntoView({behavior:"smooth",block:"start"})})})},S=function(a,l){const o=e.find(c=>c.id===a.id||c.slug===a.slug);o&&b(o,l)};v();let e=[],t={query:"",page:1,perPage:9};const s=document.querySelector("#event-search"),r=document.querySelector("#event-count"),d=document.querySelector("#event-list"),i=document.querySelector("#event-pagination"),w=g(document.querySelector("#event-modal"));x();async function x(){r&&(r.textContent="Memuat data event..."),e=await $.getEventList(),s&&s.addEventListener("input",a=>{t.query=a.target.value,t.page=1,p()}),document.querySelector("#event-reset")?.addEventListener("click",()=>{s&&(s.value=""),t.query="",t.page=1,p()}),p()}}function u(e){return`/event/${encodeURIComponent(e.slug||e.id||"")}`}async function m(e,t){try{const s=await fetch(u(e),{headers:{Accept:"application/json"}});if(!s.ok){window.location.href=u(e);return}const d=(await s.json()).data;if(!d){window.location.href=u(e);return}b(d,t)}catch{window.location.href=u(e)}}function b(e,t){const s=window.GenBIUI?.safeImage||(r=>r||"https://genbijambi.com/public/uploads/slider-1.png");t.open({content:`
    <div class="public-modal-panel event-detail-panel modal-panel is-open" role="dialog" aria-modal="true" aria-labelledby="event-title">
      <button class="btn-icon modal-close" aria-label="Tutup detail event">\xD7</button>
      <div class="event-detail-image">
        <img src="${s(e.banner||e.image)}" alt="${e.title}" />
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
