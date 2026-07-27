(function(){"use strict";const{newsDetailUrl:m,pageUrl:b,renderShell:S}=window.GenBIApp,{observeFadeUp:v}=window.GenBIUI,{site:u,stats:$,programs:f,news:w,bpiMembers:k,publicEvents:x}=window.GenBIData,y=window.GenBIAPI,a={...u,...window.GenBISiteSettings||{},heroSlides:Array.isArray(window.GenBISiteSettings?.heroSlides)&&window.GenBISiteSettings.heroSlides.length?window.GenBISiteSettings.heroSlides:u.heroSlides,sidebar:{...u.sidebar||{},...window.GenBISiteSettings?.sidebar||{}},colors:{...u.colors||{},...window.GenBISiteSettings?.colors||{}}};S("home"),L(),B(),q(),I(),j(),C(),T(),H(),G(),E(),v();function L(){const t=document.querySelector("#hero-slider"),e=document.querySelector("#hero-dots"),r=document.querySelector("#hero-eyebrow"),n=document.querySelector("#hero-title"),s=document.querySelector("#hero-caption");if(!t)return;(t.dataset.ssr!=="true"||!t.children.length)&&(t.innerHTML=a.heroSlides.map((i,c)=>`
      <img src="${i.image}" alt="${i.caption}" class="hero-image hero-bg-image ${c===0?"is-active":""}" />
    `).join("")),e&&(e.dataset.ssr!=="true"||!e.children.length)&&(e.innerHTML=a.heroSlides.map((i,c)=>`<button class="h-2.5 w-2.5 rounded-full bg-white/40 transition hover:bg-white ${c===0?"bg-white":""}" aria-label="Slide ${c+1}" data-slide="${c}"></button>`).join(""));let o=0;const l=i=>{o=i;const c=a.heroSlides[o];t.querySelectorAll("img").forEach((g,h)=>g.classList.toggle("is-active",h===o)),e.querySelectorAll("button").forEach((g,h)=>{g.classList.toggle("bg-white",h===o),g.classList.toggle("bg-white/40",h!==o)}),r.innerHTML=`${window.GenBIApp.icon("sparkles","h-4 w-4")} ${c.eyebrow}`,n.textContent=c.title,s.textContent=c.caption};e.querySelectorAll("button").forEach(i=>i.addEventListener("click",()=>l(Number(i.dataset.slide)))),l(0),window.setInterval(()=>l((o+1)%a.heroSlides.length),6500)}function A(t){if(!t)return"Terbaru";const e=new Date(t);return Number.isNaN(e.getTime())?t:e.toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}function M(t="",e=150){const r=String(t).replace(/<[^>]*>/g,"").replace(/\s+/g," ").trim();return r.length>e?`${r.slice(0,e).trim()}\u2026`:r}function B(){const t=document.querySelector("#home-announcements"),e=document.querySelector("[data-announcement-section]")||t?.closest(".announcement-hero-section");if(!t||t.dataset.ssr==="true"&&t.children.length)return;const r=w.filter(n=>String(n.category||"").toLowerCase()==="pengumuman").slice(0,8);if(!r.length){e&&(e.hidden=!0);return}t.classList.toggle("is-centered",r.length<=2),t.innerHTML=r.map((n,s)=>`
    <a data-transition href="${m(n)}" class="announcement-card" aria-label="Baca pengumuman: ${n.title}">
      <span class="announcement-number">${String(s+1).padStart(2,"0")}</span>
      <span class="announcement-date">${A(n.date)}</span>
      <h3>${n.title}</h3>
      <p>${M(n.excerpt||(Array.isArray(n.body)?n.body.join(" "):""))}</p>
    </a>
  `).join("")}function q(){const t=document.querySelector("#stats-row");!t||t.dataset.ssr==="true"&&t.children.length||(t.innerHTML=$.map(e=>`
    <div class="fade-up">
      <p class="serif text-4xl font-semibold tracking-tight text-neutral-950">${e.value}</p>
      <p class="mt-2 text-sm leading-6 text-neutral-600">${e.label}</p>
    </div>
  `).join(""))}function P(t=""){const e=t.toLowerCase();return e.includes("siginjai")?d("bank"):e.includes("gentala")?d("chart"):e.includes("ggtc")?d("academic"):e.includes("leadership")?d("spark"):d("users")}function I(){const t=document.querySelector("#program-list");if(t){if(t.dataset.ssr==="true"&&t.children.length){p(t);return}t.innerHTML=f.map((e,r)=>{const n=Array.isArray(e.images)&&e.images.length?e.images:[a.heroSlides[0]?.image||"https://genbijambi.com/public/uploads/slider-1.png"],s=e.focus?`<span class="blue-badge mx-auto mt-5">${e.focus}</span>`:"";return`
      <article class="editorial-slide-card program-slide-card" role="group" aria-roledescription="slide" aria-label="Program ${r+1} dari ${f.length}" data-program-slides='${JSON.stringify(n)}' style="--program-bg-image: url('${n[0]}');">
        <span class="slide-index">${String(r+1).padStart(2,"0")}</span>
        <span class="program-icon mx-auto">${window.GenBIApp.icon(e.icon_key||"sparkles","program-icon-svg")}</span>
        <p class="slide-kicker">${e.title}</p>
        <h3>${e.name}</h3>
        <p>${e.description}</p>
        ${s}
      </article>
    `}).join(""),p(t)}}function p(t){t.querySelectorAll(".program-slide-card").forEach(e=>{const r=e.querySelector("[data-program-icon]");if(r){const o=r.dataset.programIcon||"sparkles";r.innerHTML=window.GenBIApp.icon(o,"program-icon-svg")}else{const o=e.querySelector(".program-icon");o&&!o.innerHTML.trim()&&(o.innerHTML=window.GenBIApp.icon("sparkles","program-icon-svg"))}const n=JSON.parse(e.dataset.programSlides||"[]").filter(Boolean);if(!n.length||(e.style.setProperty("--program-bg-image",`url('${n[0]}')`),n.length<=1))return;let s=0;window.setInterval(()=>{s=(s+1)%n.length,e.style.setProperty("--program-bg-image",`url('${n[s]}')`)},4200)})}async function j(){const t=document.querySelector("#bpi-list");if(!t||t.dataset.ssr==="true"&&t.children.length)return;let e=k;try{const r=await y.getTeamList({per_page:200});e=Array.isArray(r?.bpi)&&r.bpi.length?r.bpi:e}catch{}t.innerHTML=e.map((r,n)=>`
    <article class="editorial-slide-card bpi-slide-card" role="group" aria-roledescription="slide" aria-label="Anggota BPI ${n+1} dari ${e.length}">
      <figure class="bpi-slide-photo">
        <span class="member-photo-skeleton" aria-hidden="true"></span>
        <img src="${r.photo||r.image}" alt="Foto ${r.name}" loading="lazy" onload="this.previousElementSibling.classList.add('is-hidden')" onerror="this.classList.add('is-hidden'); this.previousElementSibling.classList.remove('is-hidden')" />
      </figure>
      <div class="bpi-slide-content">
        <span class="bpi-number mx-auto">${String(n+1).padStart(2,"0")}</span>
        <h3>${r.name}</h3>
        <p>${r.role}</p>
        <span class="blue-badge mx-auto mt-5">${r.commission}</span>
      </div>
    </article>
  `).join("")}function d(t){const e={heart:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',users:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',chart:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',calendar:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',bank:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',academic:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',spark:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.76 5.45h5.74l-4.64 3.37 1.77 5.45L12 13.9l-4.63 3.37 1.77-5.45L4.5 8.45h5.74L12 3Z"/></svg>'};return e[t]||e.calendar}async function C(){const t=document.querySelector("#home-events");if(!t)return;if(t.dataset.ssr==="true"&&t.children.length){p(t);return}let e=x;try{const r=await y.getEventList();Array.isArray(r)&&r.length&&(e=r)}catch{}t.innerHTML=e.map((r,n)=>{const s=Array.isArray(r.images)&&r.images.length?r.images:[a.heroSlides[n%a.heroSlides.length]?.image||a.heroSlides[0]?.image||"https://genbijambi.com/public/uploads/slider-1.png"],o=r.type||r.category||"Agenda Komunitas",l=r.date||r.start_date||r.start||"-",i=r.description||r.excerpt||"";return`
    <article class="editorial-slide-card program-slide-card agenda-slide-card" role="group" aria-roledescription="slide" aria-label="Agenda ${n+1} dari ${e.length}" data-program-slides='${JSON.stringify(s)}' style="--program-bg-image: url('${s[0]}');">
      <span class="slide-index">${String(n+1).padStart(2,"0")}</span>
      <span class="program-icon mx-auto">${d(r.icon||"calendar")}</span>
      <p class="slide-kicker">${o}</p>
      <h3>${r.title}</h3>
      <p>${i}</p>
      <span class="blue-badge mx-auto mt-5">${l}</span>
    </article>
  `}).join(""),p(t)}function H(){const t=document.querySelector("#home-contact-card");!t||t.dataset.ssr==="true"&&t.children.length||(t.innerHTML=`
    <div>
      <p class="eyebrow">Contact us</p>
      <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">Mau berkolaborasi dengan GenBI Jambi?</h2>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Hubungi kami untuk informasi kegiatan, publikasi, kolaborasi, dan agenda komunitas.</p>
    </div>
    <div class="contact-prefooter-card">
      <p class="contact-label">Address</p>
      <p>${a.address}</p>
      <div class="mt-5 grid gap-2 text-sm">
        <a href="mailto:${a.email}">${a.email}</a>
        <a href="tel:${a.phone}">${a.phone}</a>
      </div>
      <a data-transition href="${b("contact")}" class="btn btn-primary mt-6 w-fit">Contact Us</a>
    </div>
  `)}function T(){const t=document.querySelector("#home-news");t&&(t.dataset.ssr==="true"&&t.children.length||(t.innerHTML=w.slice(0,3).map(e=>`
    <a data-transition href="${m(e)}" class="home-news-card">
      <figure class="home-news-media"><img src="${e.image}" alt="${e.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" /></figure>
      <div class="home-news-copy">
        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
          <span class="text-blue-800">${e.category}</span><span>${e.date}</span><span>${e.readTime}</span>
        </div>
        <h3 class="serif text-2xl font-semibold leading-tight tracking-tight text-neutral-950 md:text-3xl">${e.title}</h3>
        <p class="text-base leading-7 text-neutral-600">${e.excerpt}</p>
      </div>
    </a>
  `).join("")))}function E(){document.querySelectorAll("[data-carousel]").forEach(t=>{const e=t.querySelector(".horizontal-carousel"),r=t.querySelector("[data-carousel-prev]"),n=t.querySelector("[data-carousel-next]");if(!e||!r||!n)return;const s=()=>{const o=e.querySelector(".editorial-slide-card, .announcement-card");if(!o)return e.clientWidth*.85;const l=window.getComputedStyle(e),i=parseFloat(l.columnGap||l.gap||"0")||0;return o.getBoundingClientRect().width+i};r.addEventListener("click",()=>e.scrollBy({left:-s(),behavior:"smooth"})),n.addEventListener("click",()=>e.scrollBy({left:s(),behavior:"smooth"}))})}function G(){const t=document.querySelector("#video-modal"),e=t?.querySelector(".modal-panel"),r=document.querySelector("#open-video"),n=document.querySelector("#close-video"),s=document.querySelector("#profile-video");if(!t||!r||!n||!s)return;const o=()=>{s.src=a.videoResourceUrl,t.classList.remove("hidden"),window.setTimeout(()=>e.classList.add("is-open"),20)},l=()=>{e.classList.remove("is-open"),window.setTimeout(()=>{t.classList.add("hidden"),s.src=""},160)};r.addEventListener("click",o),n.addEventListener("click",l),t.addEventListener("click",i=>{i.target===t&&l()})}})();
