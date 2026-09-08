(()=>{(function(){"use strict";let b=window.GenBIData||{},{navItems:p=[],site:x={}}=b,r=window.GenBIAPICore||{},o=L(x,window.GenBISiteSettings||{}),$={menu:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',x:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>',mail:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.62a2.25 2.25 0 0 1-2.36 0l-7.5-4.62a2.25 2.25 0 0 1-1.07-1.92v-.24"/></svg>',phone:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z"/></svg>',arrowRight:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>',arrowUp:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75"/></svg>',chevronRight:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>',chevronDown:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>',sparkles:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 3.7 8.4 8.1 4 9.5l4.4 1.4 1.4 4.4 1.4-4.4 4.4-1.4-4.4-1.4-1.4-4.4Zm7.4 6.7-.9 2.8-2.8.9 2.8.9.9 2.8.9-2.8 2.8-.9-2.8-.9-.9-2.8Z"/></svg>',users:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',bank:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',chart:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',academic:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',calendar:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',heart:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',news:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h13.5A1.5 1.5 0 0 1 19.5 8.25v9A2.25 2.25 0 0 0 21.75 15V6.75H19.5m-15 0A1.5 1.5 0 0 0 3 8.25v9A2.25 2.25 0 0 0 5.25 19.5h14.25M7.5 10.5h6M7.5 13.5h6M7.5 16.5h3"/></svg>',grid:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"/></svg>'};function a(e,t=""){return($[e]||"").replace('class="',`class="${t} `)}function l(e){return e.href?e.href:h(e.key)}function g(e,t){return e.key===t?!0:Array.isArray(e.children)&&e.children.some(n=>n.key===t)}function y(e,t){let n=g(e,t);return Array.isArray(e.children)&&e.children.length?`
      <div class="nav-dropdown ${n?"is-active":""}">
        <a data-transition href="${l(e)}" class="nav-link nav-dropdown-trigger ${n?"nav-link-active":""}" aria-haspopup="true" aria-expanded="false">
          ${e.label}${a("chevronDown")}
        </a>
        <div class="nav-dropdown-menu" role="menu">
          ${e.children.map(i=>`<a data-transition href="${l(i)}" role="menuitem" class="${i.key===t?"is-active":""}">${i.label}</a>`).join("")}
        </div>
      </div>
    `:`<a data-transition href="${l(e)}" class="nav-link ${n?"nav-link-active":""}">${e.label}</a>`}function M(e,t){let n=g(e,t);return Array.isArray(e.children)&&e.children.length?`
      <div class="mobile-link-group">
        <a data-transition href="${l(e)}" class="mobile-link ${n?"mobile-link-active":""}">${e.label}<span class="mobile-link-icon">${a("chevronRight")}</span></a>
        <div class="mobile-sub-links">
          ${e.children.map(i=>`<a data-transition href="${l(i)}" class="mobile-sub-link ${i.key===t?"is-active":""}">${i.label}</a>`).join("")}
        </div>
      </div>
    `:`<a data-transition href="${l(e)}" class="mobile-link ${n?"mobile-link-active":""}">${e.label}<span class="mobile-link-icon">${a("chevronRight")}</span></a>`}function A(e="home"){S(e),j(),u()}function u(){U(),E(),R(),I()}function L(e,t){return{...e,...t,heroSlides:Array.isArray(t.heroSlides)&&t.heroSlides.length?t.heroSlides:e.heroSlides,socials:Array.isArray(t.socials)?t.socials:e.socials||[],sidebar:{...e.sidebar||{},...t.sidebar||{}},colors:{...e.colors||{},...t.colors||{}}}}function m(e=""){return String(e).replace(/[&<>"']/g,t=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"})[t])}function C(e="",t=""){let n=`${e} ${t}`.toLowerCase();return n.includes("youtube")||n.includes("youtu.be")?'<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M21 8.25a3 3 0 0 0-2.1-2.13C17.06 5.62 12 5.62 12 5.62s-5.06 0-6.9.5A3 3 0 0 0 3 8.25a31.9 31.9 0 0 0-.38 3.75A31.9 31.9 0 0 0 3 15.75a3 3 0 0 0 2.1 2.13c1.84.5 6.9.5 6.9.5s5.06 0 6.9-.5a3 3 0 0 0 2.1-2.13 31.9 31.9 0 0 0 .38-3.75A31.9 31.9 0 0 0 21 8.25Z" stroke="currentColor" stroke-width="1.65"/><path d="m10.25 15.15 4.5-3.15-4.5-3.15v6.3Z" fill="currentColor"/></svg>':n.includes("instagram")?'<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" focusable="false"><rect x="3.25" y="3.25" width="17.5" height="17.5" rx="5"/><circle cx="12" cy="12" r="4.1"/><circle cx="17.4" cy="6.7" r=".8" fill="currentColor" stroke="none"/></svg>':n.includes("whatsapp")||n.includes("wa.me")?'<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.4 11.75a8.4 8.4 0 0 1-12.45 7.38L3.6 20.4l1.27-4.22A8.4 8.4 0 1 1 20.4 11.75Z"/><path d="M8.15 7.8c.2-.45.42-.46.73-.47h.62c.2 0 .4.03.53.36l.78 1.89c.1.28.03.49-.1.68l-.59.75c-.15.18-.12.36 0 .54.67 1.14 1.57 2.03 2.72 2.69.18.1.36.14.54-.02l.84-.98c.17-.2.38-.24.62-.14l1.85.87c.26.12.43.3.39.57-.16 1.08-.69 1.78-1.55 2.12-.7.28-1.58.2-2.49-.18a12.6 12.6 0 0 1-5.82-5.08c-.52-.84-.74-1.66-.64-2.35.07-.48.32-.9.57-1.25Z"/></svg>':'<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M13.5 4.5h6v6"/><path d="m19.5 4.5-9 9"/><path d="M10.5 6.75H6A1.5 1.5 0 0 0 4.5 8.25V18A1.5 1.5 0 0 0 6 19.5h9.75a1.5 1.5 0 0 0 1.5-1.5v-4.5"/></svg>'}function B(){return(Array.isArray(o.socials)?o.socials:[]).filter(t=>String(t?.url||"").trim()).map(t=>`
      <a href="${m(t.url)}" class="social-mini" aria-label="Buka ${m(t.name||"social media")} GenBI di tab baru" title="${m(t.name||"Social media")}" target="_blank" rel="noopener noreferrer">${C(t.name,t.url)}</a>
    `).join("")}function S(e){let t=document.querySelector("#site-header");if(!t)return;let n=p.map(i=>y(i,e)).join("");t.innerHTML=`
    <div id="site-header-shell" class="site-header-shell">
      <div class="top-strip hidden md:block">
        <div class="site-container flex h-9 items-center justify-between text-[13px] text-white/90">
          <div class="flex items-center gap-4">
            <a href="mailto:${o.email}" class="inline-flex items-center gap-2 hover:text-white">${a("mail")}${o.email}</a>
            <span class="h-4 w-px bg-white/30"></span>
            <a href="tel:${o.phone}" class="inline-flex items-center gap-2 hover:text-white">${a("phone")}${o.phone}</a>
          </div>
          <div class="flex items-center gap-3" aria-label="Social links">${B()}</div>
        </div>
      </div>
      <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
        <div class="site-container flex h-20 items-center justify-between">
          <a data-transition href="${h("home")}" class="flex items-center gap-3" aria-label="Go to home">
            <span class="logo-shell"><img src="${o.logo}" alt="${o.name}" class="h-9 w-auto" /></span>
            <span class="leading-tight">
              <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
              <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
            </span>
          </a>
          <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
            ${n}
          </nav>
          <div class="hidden items-center gap-3 lg:flex">
            <a data-transition href="${w("dashboard")}" class="btn btn-secondary">Admin Preview</a>
            <a data-transition href="${h("contact")}" class="btn btn-primary">Hubungi Kami ${a("arrowRight")}</a>
          </div>
          <button id="open-menu" class="btn-icon lg:hidden" aria-label="Open menu">${a("menu")}</button>
        </div>
      </header>
    </div>
    <div id="site-header-spacer" aria-hidden="true"></div>
    <div id="mobile-panel" style="z-index: 9999;" class="fixed inset-0 hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden">
      <div class="mobile-sheet" style="z-index: 999;">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="logo-shell"><img src="${o.logo}" alt="${o.name}" class="h-8 w-auto" /></span>
            <span class="font-semibold text-neutral-950">Menu</span>
          </div>
          <button id="close-menu" class="btn-icon" aria-label="Close menu">${a("x")}</button>
        </div>
        <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
          ${p.map(i=>M(i,e)).join("")}
          <a data-transition href="${w("dashboard")}" class="mobile-link">Admin Preview<span class="mobile-link-icon">${a("chevronRight")}</span></a>
        </nav>
        <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
          <strong>${o.name}</strong><br />${o.tagline}
        </div>
      </div>
    </div>
  `}function j(){let e=document.querySelector("#site-footer");e&&(e.innerHTML=`
    <section class="border-t border-neutral-900/10 bg-blue-950 text-white">
      <div class="site-container grid gap-10 py-14 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
        <div>
          <div class="flex items-center gap-3">
            <span class="logo-shell logo-shell-light"><img src="${o.logo}" alt="${o.name}" class="h-10 w-auto" /></span>
            <div>
              <p class="font-semibold">${o.name}</p>
              <p class="text-sm text-blue-100/80">${o.tagline}</p>
            </div>
          </div>
          <p class="mt-5 max-w-md text-sm leading-7 text-blue-100/80">Website publik untuk profil komunitas, kegiatan, prestasi, berita, anggota, dan kontak resmi GenBI Jambi.</p>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Navigasi</h3>
          <div class="mt-4 grid gap-2 text-sm text-blue-100/80">
            ${p.map(t=>`<a data-transition href="${l(t)}" class="w-fit hover:text-white">${t.label}</a>`).join("")}
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Kontak</h3>
          <div class="mt-4 grid gap-2 text-sm leading-6 text-blue-100/80">
            <a href="mailto:${o.footerEmail||o.email}" class="hover:text-white">${o.footerEmail||o.email}</a>
            <a href="tel:${o.footerPhone||o.phone}" class="hover:text-white">${o.footerPhone||o.phone}</a>
            <p>${o.address}</p>
          </div>
        </div>
      </div>
      <div class="border-t border-white/10 py-5 text-center text-xs text-blue-100/70">${o.footerCopyright||"Copyright &copy; 2026, GenBI Provinsi Jambi"}</div>
    </section>
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">${a("arrowUp")}</button>
  `)}function U(){let e=document.querySelector("#mobile-panel"),t=document.querySelector("#open-menu"),n=document.querySelector("#close-menu");if(!e||!t||!n||e.dataset.mobileMenuReady==="1")return;e.dataset.mobileMenuReady="1";let i=()=>{e.classList.remove("hidden"),document.body.classList.add("modal-lock"),t.setAttribute("aria-expanded","true"),window.addEventListener("keydown",v)},d=()=>{e.classList.add("hidden"),document.body.classList.remove("modal-lock"),t.setAttribute("aria-expanded","false"),window.removeEventListener("keydown",v)},v=s=>{s.key==="Escape"&&d()};t.setAttribute("aria-expanded","false"),t.setAttribute("aria-controls","mobile-panel"),t.addEventListener("click",i),n.addEventListener("click",d),e.addEventListener("click",s=>{s.target===e&&d()}),e.querySelectorAll("a").forEach(s=>s.addEventListener("click",d))}function E(){let e=document.querySelector("#site-header-shell"),t=document.querySelector("#site-header-spacer");if(!e||!t||e.dataset.autoHideReady==="1")return;e.dataset.autoHideReady="1";let n=window.scrollY,i=!1,d=24,v=96,s=()=>{t.style.height=`${e.offsetHeight}px`},f=()=>{let c=Math.max(window.scrollY,0),k=c-n,D=c<=d,P=k<0,G=k>0;e.classList.toggle("is-scrolled",c>0),D||P?e.classList.remove("is-hidden"):c>v&&G&&e.classList.add("is-hidden"),n=c,i=!1};s(),f(),window.addEventListener("resize",s),window.addEventListener("load",s),window.addEventListener("scroll",()=>{i||(i=!0,window.requestAnimationFrame(f))},{passive:!0})}function R(){document.body.dataset.pageTransitionsReady!=="1"&&(document.body.dataset.pageTransitionsReady="1",document.body.classList.add("page-ready"),window.addEventListener("pageshow",function(e){e.persisted&&(document.body.classList.remove("page-leaving"),document.body.classList.add("page-ready"))}),document.addEventListener("click",e=>{let t=e.target.closest("a[data-transition]");if(!t)return;let n=new URL(t.href,window.location.href),i=new URL(window.location.href);n.origin!==i.origin||t.target||e.metaKey||e.ctrlKey||(e.preventDefault(),document.body.classList.add("page-leaving"),window.setTimeout(()=>{window.location.href=n.href},130))}))}function I(){let e=document.querySelector("#back-to-top");if(!e||e.dataset.backToTopReady==="1")return;e.dataset.backToTopReady="1";let t=()=>e.classList.toggle("is-visible",window.scrollY>500);t(),window.addEventListener("scroll",t,{passive:!0}),e.addEventListener("click",()=>window.scrollTo({top:0,behavior:"smooth"}))}function H(e){return e.split(" ").slice(0,2).map(t=>t[0]).join("").toUpperCase()}function T(e){let n=new URLSearchParams(window.location.search).get(e);return n||(e==="slug"&&window.location.pathname.startsWith("/news/")?decodeURIComponent(window.location.pathname.split("/").filter(Boolean).pop()||""):null)}function h(e){return r?.pageUrl?r.pageUrl(e,window.location):`/${e}`}function w(e){return r?.adminUrl?r.adminUrl(e,window.location):`/admin/${e}`}function Z(e,t={}){return r?.routeUrl?r.routeUrl(e,t,window.location):""}function q(e){return r?.newsDetailUrl?r.newsDetailUrl(e,window.location):`/news/${e.slug||e.id}`}window.GenBIApp={adminUrl:w,renderShell:A,setupShellInteractions:u,formatInitials:H,getParam:T,icon:a,newsDetailUrl:q,pageUrl:h,routeUrl:Z},document.readyState==="loading"?document.addEventListener("DOMContentLoaded",u):u()})();})();
