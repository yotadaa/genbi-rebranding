(function(){"use strict";const{navItems:u,site:w}=window.GenBIData,a=window.GenBIAPICore,n=k(w,window.GenBISiteSettings||{}),g={menu:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',x:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>',mail:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.62a2.25 2.25 0 0 1-2.36 0l-7.5-4.62a2.25 2.25 0 0 1-1.07-1.92v-.24"/></svg>',phone:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z"/></svg>',arrowRight:'<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>',sparkles:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 3.7 8.4 8.1 4 9.5l4.4 1.4 1.4 4.4 1.4-4.4 4.4-1.4-4.4-1.4-1.4-4.4Zm7.4 6.7-.9 2.8-2.8.9 2.8.9.9 2.8.9-2.8 2.8-.9-2.8-.9-.9-2.8Z"/></svg>',users:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',bank:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',chart:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',academic:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',calendar:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',heart:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',news:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h13.5A1.5 1.5 0 0 1 19.5 8.25v9A2.25 2.25 0 0 0 21.75 15V6.75H19.5m-15 0A1.5 1.5 0 0 0 3 8.25v9A2.25 2.25 0 0 0 5.25 19.5h14.25M7.5 10.5h6M7.5 13.5h6M7.5 16.5h3"/></svg>',grid:'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"/></svg>'};function l(e,t=""){return(g[e]||"").replace('class="',`class="${t} `)}function f(e="home"){b(e),x(),$(),y(),M(),L()}function k(e,t){return{...e,...t,heroSlides:Array.isArray(t.heroSlides)&&t.heroSlides.length?t.heroSlides:e.heroSlides,sidebar:{...e.sidebar||{},...t.sidebar||{}},colors:{...e.colors||{},...t.colors||{}}}}function b(e){const t=document.querySelector("#site-header");if(!t)return;const o=u.map(s=>{const r=s.key===e;return`<a data-transition href="${d(s.key)}" class="nav-link ${r?"nav-link-active":""}">${s.label}</a>`}).join("");t.innerHTML=`
    <div id="site-header-shell" class="site-header-shell">
      <div class="top-strip hidden md:block">
        <div class="site-container flex h-9 items-center justify-between text-[13px] text-white/90">
          <div class="flex items-center gap-4">
            <a href="mailto:${n.email}" class="inline-flex items-center gap-2 hover:text-white">${l("mail")}${n.email}</a>
            <span class="h-4 w-px bg-white/30"></span>
            <a href="tel:${n.phone}" class="inline-flex items-center gap-2 hover:text-white">${l("phone")}${n.phone}</a>
          </div>
          <div class="flex items-center gap-3" aria-label="Social links">
            <a href="#" class="social-mini">Fb</a>
            <a href="#" class="social-mini">Ig</a>
            <a href="#" class="social-mini">Yt</a>
            <a href="#" class="social-mini">Wa</a>
          </div>
        </div>
      </div>
      <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
        <div class="site-container flex h-20 items-center justify-between">
          <a data-transition href="${d("home")}" class="flex items-center gap-3" aria-label="Go to home">
            <span class="logo-shell"><img src="${n.logo}" alt="${n.name}" class="h-9 w-auto" /></span>
            <span class="leading-tight">
              <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
              <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
            </span>
          </a>
          <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
            ${o}
          </nav>
          <div class="hidden items-center gap-3 lg:flex">
            <a data-transition href="${v("dashboard")}" class="btn btn-secondary">Admin Preview</a>
            <a data-transition href="${d("contact")}" class="btn btn-primary">Hubungi Kami ${l("arrowRight")}</a>
          </div>
          <button id="open-menu" class="btn-icon lg:hidden" aria-label="Open menu">${l("menu")}</button>
        </div>
      </header>
    </div>
    <div id="site-header-spacer" aria-hidden="true"></div>
    <div id="mobile-panel" class="fixed inset-0 z-[70] hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden">
      <div class="mobile-sheet">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="logo-shell"><img src="${n.logo}" alt="${n.name}" class="h-8 w-auto" /></span>
            <span class="font-semibold text-neutral-950">Menu</span>
          </div>
          <button id="close-menu" class="btn-icon" aria-label="Close menu">${l("x")}</button>
        </div>
        <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
          ${u.map(s=>`<a data-transition href="${d(s.key)}" class="mobile-link ${s.key===e?"mobile-link-active":""}">${s.label}<span>\u203A</span></a>`).join("")}
          <a data-transition href="${v("dashboard")}" class="mobile-link">Admin Preview<span>\u203A</span></a>
        </nav>
        <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
          <strong>${n.name}</strong><br />${n.tagline}
        </div>
      </div>
    </div>
  `}function x(){const e=document.querySelector("#site-footer");e&&(e.innerHTML=`
    <section class="border-t border-neutral-900/10 bg-blue-950 text-white">
      <div class="site-container grid gap-10 py-14 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
        <div>
          <div class="flex items-center gap-3">
            <span class="logo-shell logo-shell-light"><img src="${n.logo}" alt="${n.name}" class="h-10 w-auto" /></span>
            <div>
              <p class="font-semibold">${n.name}</p>
              <p class="text-sm text-blue-100/80">${n.tagline}</p>
            </div>
          </div>
          <p class="mt-5 max-w-md text-sm leading-7 text-blue-100/80">Website publik untuk profil komunitas, kegiatan, prestasi, berita, anggota, dan kontak resmi GenBI Jambi.</p>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Navigasi</h3>
          <div class="mt-4 grid gap-2 text-sm text-blue-100/80">
            ${u.map(t=>`<a data-transition href="${d(t.key)}" class="w-fit hover:text-white">${t.label}</a>`).join("")}
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-white">Kontak</h3>
          <div class="mt-4 grid gap-2 text-sm leading-6 text-blue-100/80">
            <a href="mailto:${n.footerEmail||n.email}" class="hover:text-white">${n.footerEmail||n.email}</a>
            <a href="tel:${n.footerPhone||n.phone}" class="hover:text-white">${n.footerPhone||n.phone}</a>
            <p>${n.address}</p>
          </div>
        </div>
      </div>
      <div class="border-t border-white/10 py-5 text-center text-xs text-blue-100/70">${n.footerCopyright||"Copyright \xA9 2026, GenBI Provinsi Jambi"}</div>
    </section>
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">\u2191</button>
  `)}function $(){const e=document.querySelector("#mobile-panel"),t=document.querySelector("#open-menu"),o=document.querySelector("#close-menu");if(!e||!t||!o)return;const s=()=>{e.classList.remove("hidden"),document.body.classList.add("modal-lock"),t.setAttribute("aria-expanded","true"),window.addEventListener("keydown",h)},r=()=>{e.classList.add("hidden"),document.body.classList.remove("modal-lock"),t.setAttribute("aria-expanded","false"),window.removeEventListener("keydown",h)},h=i=>{i.key==="Escape"&&r()};t.setAttribute("aria-expanded","false"),t.setAttribute("aria-controls","mobile-panel"),t.addEventListener("click",s),o.addEventListener("click",r),e.addEventListener("click",i=>{i.target===e&&r()}),e.querySelectorAll("a").forEach(i=>i.addEventListener("click",r))}function y(){const e=document.querySelector("#site-header-shell"),t=document.querySelector("#site-header-spacer");if(!e||!t)return;let o=window.scrollY,s=!1;const r=24,h=96,i=()=>{t.style.height=`${e.offsetHeight}px`},p=()=>{const c=Math.max(window.scrollY,0),m=c-o,j=c<=r,E=m<0,U=m>0;e.classList.toggle("is-scrolled",c>0),j||E?e.classList.remove("is-hidden"):c>h&&U&&e.classList.add("is-hidden"),o=c,s=!1};i(),p(),window.addEventListener("resize",i),window.addEventListener("load",i),window.addEventListener("scroll",()=>{s||(s=!0,window.requestAnimationFrame(p))},{passive:!0})}function M(){document.body.classList.add("page-ready"),window.addEventListener("pageshow",function(e){e.persisted&&(document.body.classList.remove("page-leaving"),document.body.classList.add("page-ready"))}),document.addEventListener("click",e=>{const t=e.target.closest("a[data-transition]");if(!t)return;const o=new URL(t.href,window.location.href),s=new URL(window.location.href);o.origin!==s.origin||t.target||e.metaKey||e.ctrlKey||(e.preventDefault(),document.body.classList.add("page-leaving"),window.setTimeout(()=>{window.location.href=o.href},130))})}function L(){const e=document.querySelector("#back-to-top");if(!e)return;const t=()=>e.classList.toggle("is-visible",window.scrollY>500);t(),window.addEventListener("scroll",t,{passive:!0}),e.addEventListener("click",()=>window.scrollTo({top:0,behavior:"smooth"}))}function S(e){return e.split(" ").slice(0,2).map(t=>t[0]).join("").toUpperCase()}function A(e){const o=new URLSearchParams(window.location.search).get(e);return o||(e==="slug"&&window.location.pathname.startsWith("/news/")?decodeURIComponent(window.location.pathname.split("/").filter(Boolean).pop()||""):null)}function d(e){return a?.pageUrl?a.pageUrl(e,window.location):`/${e}`}function v(e){return a?.adminUrl?a.adminUrl(e,window.location):`/admin/${e}`}function B(e,t={}){return a?.routeUrl?a.routeUrl(e,t,window.location):""}function C(e){return a?.newsDetailUrl?a.newsDetailUrl(e,window.location):`/news/${e.slug||e.id}`}window.GenBIApp={adminUrl:v,renderShell:f,formatInitials:S,getParam:A,icon:l,newsDetailUrl:C,pageUrl:d,routeUrl:B}})();
