(function(){"use strict";const{newsDetailUrl:g,renderShell:b}=window.GenBIApp,{createCustomSelect:y,observeFadeUp:m,unique:h}=window.GenBIUI,f=window.GenBIAPI;b("news"),m();const i=document.querySelector("#news-list");if(i?.dataset.ssr==="true"){document.body.classList.add("page-ready");const t=document.querySelector("#news-filter-form"),r=document.querySelector("#news-search");r&&t&&r.addEventListener("keydown",a=>{a.key==="Enter"&&(a.preventDefault(),t.submit())});return}let e={query:"",category:"Semua",page:1,perPage:12},l=[];const w=document.querySelector("#news-search"),u=document.querySelector("#news-count"),o=document.querySelector("#news-pagination");x(),w.addEventListener("input",t=>{e.query=t.target.value,e.page=1,c()});async function x(){u.textContent="Memuat berita...",l=await f.getNewsList(),y(document.querySelector("#news-category"),{label:"Kategori",options:h(l.map(t=>t.category)),onChange:t=>{e.category=t,e.page=1,c()}}),c()}function c(){const t=e.query.toLowerCase().trim(),r=l.filter(n=>{const d=`${n.title} ${n.category} ${n.excerpt}`.toLowerCase();return(!t||d.includes(t))&&(e.category==="Semua"||n.category===e.category)}),a=Math.max(1,Math.ceil(r.length/e.perPage));e.page=Math.min(e.page,a);const s=(e.page-1)*e.perPage,p=r.slice(s,s+e.perPage);u.textContent=r.length?`Menampilkan ${s+1}-${Math.min(s+e.perPage,r.length)} dari ${r.length} berita.`:"Tidak ada berita yang cocok.",i.innerHTML=p.length?p.map((n,d)=>`
    <a data-transition href="${g(n)}" class="article-link ${d===0?"pt-0 border-t-0":""}">
      <div class="grid gap-5 md:grid-cols-[170px_1fr] md:items-start">
        <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-blue-50">
          <img src="${n.image}" alt="${n.title}" class="h-full w-full object-cover" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
            <span class="text-blue-800">${n.category}</span><span>${n.date}</span><span>${n.readTime}</span>
          </div>
          <h3 class="serif mt-3 text-3xl font-semibold leading-tight tracking-tight text-neutral-950">${n.title}</h3>
          <p class="mt-4 text-base leading-7 text-neutral-600">${n.excerpt}</p>
          <span class="btn btn-secondary mt-5 w-fit">Detail</span>
        </div>
      </div>
    </a>
  `).join(""):'<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Belum ada data berita yang cocok.</div>',$(a)}function $(t){if(!o)return;if(t<=1){o.innerHTML="";return}const r=Array.from({length:t},(a,s)=>s+1);o.innerHTML=`
    <button class="pager-button" type="button" data-page="${Math.max(1,e.page-1)}" ${e.page===1?"disabled":""}>Sebelumnya</button>
    ${r.map(a=>`<button class="pager-button ${a===e.page?"is-active":""}" type="button" data-page="${a}">${a}</button>`).join("")}
    <button class="pager-button" type="button" data-page="${Math.min(t,e.page+1)}" ${e.page===t?"disabled":""}>Berikutnya</button>
  `,o.querySelectorAll("[data-page]").forEach(a=>{a.addEventListener("click",()=>{e.page=Number(a.dataset.page)||1,c(),i.scrollIntoView({behavior:"smooth",block:"start"})})})}})();
