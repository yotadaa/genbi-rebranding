(function(){"use strict";const f=window.GenBIData||{},v=window.GenBISettingsBootstrap||{},E=v.site||window.GenBISiteSettings||f.site||{},b=v.theme||{publicKey:"genbi",adminKey:"genbi",themes:window.GenBIThemeRegistry?.themes||[]},T=f.settingTabs||[],{renderAdminShell:P,icon:A,showToast:h,escapeHtml:n}=window.GenBIAdmin,y=window.GenBIAPI,e={active:window.location.hash==="#theme"?"theme":"logo",site:E,theme:{publicKey:b.publicKey||"genbi",adminKey:b.adminKey||"genbi",themes:b.themes||[],savedAdminKey:b.adminKey||"genbi"}};P(e.active==="theme"?"theme":"settings"),I(),w(),u();function w(){const i=document.querySelector("#settings-tabs");i&&(i.innerHTML=T.map(t=>`
      <button type="button" class="admin-tab ${t.key===e.active?"is-active":""}" data-tab="${t.key}">
        ${A(t.icon)}
        ${t.label}
      </button>
    `).join(""),i.querySelectorAll("button").forEach(t=>{t.addEventListener("click",()=>{e.active=t.dataset.tab,e.active==="theme"&&history.replaceState(null,"","#theme"),w(),u()})}))}function _(i,t,a){return`
      <div class="admin-editor-shell slide-in">
        <header class="admin-editor-head">
          <div>
            <p class="eyebrow">${i}</p>
            <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${i}</h2>
            <p class="mt-4 max-w-3xl text-base leading-7 text-[rgb(var(--text-secondary))]">${t}</p>
          </div>
        </header>
        <div class="admin-editor-canvas">${a}</div>
      </div>
    `}function s(i,t,a,r="text"){const o=r==="textarea"?`<textarea class="input-soft settings-input min-h-32" data-field="${t}">${n(a||"")}</textarea>`:`<input class="input-soft settings-input" data-field="${t}" type="${r}" value="${n(a||"")}">`;return`<label class="settings-field"><span class="settings-field-label">${i}</span>${o}</label>`}function g(i,t,a){return`
      <div class="settings-upload-card">
        <div class="settings-upload-head">
          ${a?`<img src="${n(a)}" alt="${n(i)}" class="settings-upload-preview">`:""}
          <div>
            <p class="settings-upload-title">${i}</p>
            <p class="settings-upload-note">Unggah gambar lalu simpan tab ini.</p>
          </div>
        </div>
        <input class="input-soft settings-input" data-field="${t}" type="text" value="${n(a||"")}" placeholder="/uploads/branding/...">
        <input class="admin-file-input settings-file-input" type="file" data-upload-for="${t}" accept="image/*,.ico,.svg">
      </div>
    `}function k(i,t,a=""){return`
      <article class="settings-banner-preview">
        <div class="settings-banner-preview-media">
          ${t?`<img src="${n(t)}" alt="${n(i)}" loading="lazy">`:'<div class="settings-banner-preview-empty">Belum ada gambar</div>'}
        </div>
        <div class="settings-banner-preview-copy">
          <p class="settings-banner-preview-label">${n(i)}</p>
          <p class="settings-banner-preview-caption">${n(a||"Preview banner saat ini dari pengaturan live.")}</p>
        </div>
      </article>
    `}function B(){const i=e.site.heroSlides?.[0]||{},t=e.site.heroSlides?.[1]||{},a=i.image||t.image||"",r=i.eyebrow||"Energi untuk Negeri",o=i.title||"Bersama GenBI, tumbuh dan berdampak untuk Jambi.",d=i.caption||"Preview hero halaman utama.";return`
      <section class="settings-hero-preview" style="--settings-hero-image: url('${n(a)}')">
        <div class="settings-hero-preview-overlay"></div>
        <div class="settings-hero-preview-copy">
          <span>${n(r)}</span>
          <h3>${n(o)}</h3>
          <p>${n(d)}</p>
          <div class="settings-hero-preview-actions"><i></i><i></i></div>
        </div>
      </section>
    `}function u(){const i=document.querySelector("#settings-panel");if(!i)return;const t={logo:()=>c("Logo","Logo website untuk publik dan admin.","logo",[g("Logo URL","site.logo_url",e.site.logo)]),favicon:()=>c("Favicon","Favicon browser mengikuti setting live.","favicon",[g("Favicon URL","site.favicon_url",e.site.favicon)]),topbar:()=>c("Top Bar","Kontak cepat pada bagian atas website publik.","topbar",[s("Email","site.topbar_email",e.site.email,"email"),s("Phone","site.topbar_phone",e.site.phone)]),footer:()=>c("Footer","Konten footer dan identitas organisasi.","footer",[s("Site Name","site.name",e.site.name),s("Tagline","site.tagline",e.site.tagline),s("Footer Copyright","site.footer_copyright",e.site.footerCopyright),s("Footer Address","site.footer_address",e.site.address,"textarea"),s("Footer Email","site.footer_email",e.site.footerEmail||e.site.email,"email"),s("Footer Phone","site.footer_phone",e.site.footerPhone||""),s("Recent News Count","site.footer_recent_news_count",e.site.footerRecentNewsCount||3,"number")]),email:()=>c("Email","Alamat pengirim dan penerima email website.","email",[s("Email From","site.email_from",e.site.email,"email"),s("Email To","site.email_to",e.site.footerEmail||e.site.email,"email")]),banner:()=>c("Banner","Copy dan background hero halaman utama.","banner",[B(),`<div class="settings-banner-preview-grid">${k("Banner Image 1",e.site.heroSlides?.[0]?.image||"",e.site.heroSlides?.[0]?.caption||"")}${k("Banner Image 2",e.site.heroSlides?.[1]?.image||"",e.site.heroSlides?.[1]?.caption||"")}</div>`,s("Hero Badge","site.banner_badge",e.site.heroSlides?.[0]?.eyebrow||""),s("Hero Title","site.banner_headline",e.site.heroSlides?.[0]?.title||"","textarea"),s("Hero Subtitle","site.banner_subtitle",e.site.heroSlides?.[0]?.caption||"","textarea"),g("Banner Image 1","site.banner_image_1",e.site.heroSlides?.[0]?.image||""),s("Hero Title Alt","site.banner_headline_alt",e.site.heroSlides?.[1]?.title||"","textarea"),s("Hero Subtitle Alt","site.banner_subtitle_alt",e.site.heroSlides?.[1]?.caption||"","textarea"),g("Banner Image 2","site.banner_image_2",e.site.heroSlides?.[1]?.image||"")]),sidebar:()=>c("Sidebar","Heading sidebar untuk halaman publik.","sidebar",[s("News Heading","site.sidebar_heading_news",e.site.sidebar?.news||""),s("Recent Heading","site.sidebar_heading_recent",e.site.sidebar?.recent||""),s("Upcoming Heading","site.sidebar_heading_upcoming",e.site.sidebar?.upcoming||""),s("Past Heading","site.sidebar_heading_past",e.site.sidebar?.past||""),s("Contact Heading","site.sidebar_heading_contact",e.site.sidebar?.contact||"")]),color:()=>c("Color","Override warna brand utama yang disimpan di settings.","color",[s("Primary","site.color_primary",e.site.colors?.primary||"#114b9a"),s("Primary Hover","site.color_primary_hover",e.site.colors?.primaryHover||"#0c3572"),s("Primary Soft","site.color_primary_soft",e.site.colors?.primarySoft||"#eef6ff")]),theme:H};i.innerHTML=(t[e.active]||t.logo)(),j(i)}function c(i,t,a,r){return _(i,t,`
      <form class="settings-form" data-endpoint="${a}">
        <div class="settings-form-grid ${a==="banner"?"is-banner":""}">
          ${r.join("")}
        </div>
        <div class="pt-2"><button type="submit" class="btn btn-primary">Save ${i}</button></div>
      </form>
    `)}function H(){return _("Theme","Tema publik dan admin disimpan terpisah. Preview admin hanya berubah lokal sampai disimpan.",`
      <div class="grid gap-8">
        <section>
          <div class="mb-4 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-[rgb(var(--text-primary))]">Public site</p>
              <p class="text-sm text-[rgb(var(--text-secondary))]">Tema untuk halaman publik.</p>
            </div>
            <span class="editor-status-pill">${n(e.theme.publicKey)}</span>
          </div>
          <div class="theme-card-grid">${S("public")}</div>
        </section>
        <section>
          <div class="mb-4 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-[rgb(var(--text-primary))]">Admin panel</p>
              <p class="text-sm text-[rgb(var(--text-secondary))]">Tema untuk shell admin.</p>
            </div>
            <span class="editor-status-pill">${n(e.theme.adminKey)}</span>
          </div>
          <div class="theme-card-grid">${S("admin")}</div>
        </section>
        <div class="flex flex-wrap items-center gap-3">
          <button type="button" class="btn btn-primary" data-save-theme>Save themes</button>
          <button type="button" class="btn btn-secondary" data-reset-theme>Reset to GenBI</button>
        </div>
      </div>
    `)}function S(i){return e.theme.themes.map(t=>`
        <button type="button" class="theme-card ${(i==="public"?e.theme.publicKey===t.key:e.theme.adminKey===t.key)?"is-selected":""}" data-theme-scope="${i}" data-theme-key="${t.key}">
          <div class="theme-card-head">
            <strong class="theme-card-name">${n(t.name)}</strong>
            <span class="theme-card-mode">${n(t.mode)}</span>
          </div>
          <div class="theme-card-swatches">${(t.swatches||[]).map(r=>`<span class="theme-card-swatch" style="background:${n(r)}"></span>`).join("")}</div>
          <p class="theme-card-note">${n(t.personality||"")}</p>
        </button>
      `).join("")}function j(i){i.querySelectorAll("form[data-endpoint]").forEach(t=>{t.addEventListener("submit",async a=>{a.preventDefault();const r=t.dataset.endpoint,o={};t.querySelectorAll("[data-field]").forEach(p=>{o[p.dataset.field]=p.value});const d=await x(`/admin/settings/${r}`,o);d&&d.ok&&(F(o),h("Settings berhasil disimpan."))})}),i.querySelectorAll("[data-upload-for]").forEach(t=>{t.addEventListener("change",async()=>{const a=t.files&&t.files[0];if(!a)return;const r=new FormData;r.append("image",a);const o=await fetch("/admin/settings/upload",{method:"POST",headers:K(!1),body:r}),d=await o.json();if(!o.ok||!d.data?.url){h(d.error||"Upload gagal.");return}const p=i.querySelector(`[data-field="${t.dataset.uploadFor}"]`);p&&(p.value=d.data.url)})}),i.querySelectorAll("[data-theme-scope]").forEach(t=>{t.addEventListener("mouseenter",()=>$(t.dataset.themeScope,t.dataset.themeKey)),t.addEventListener("focus",()=>$(t.dataset.themeScope,t.dataset.themeKey)),t.addEventListener("click",()=>{t.dataset.themeScope==="public"&&(e.theme.publicKey=t.dataset.themeKey),t.dataset.themeScope==="admin"&&(e.theme.adminKey=t.dataset.themeKey),u()})}),i.querySelector("[data-save-theme]")?.addEventListener("click",L),i.querySelector("[data-reset-theme]")?.addEventListener("click",()=>{e.theme.publicKey="genbi",e.theme.adminKey="genbi",document.documentElement.dataset.theme="genbi",m("genbi"),u()}),i.addEventListener("mouseleave",C)}function $(i,t){i==="admin"&&(document.documentElement.dataset.theme=t,m(t))}function C(){document.documentElement.dataset.theme=e.theme.savedAdminKey,m(e.theme.savedAdminKey)}async function L(){const i=await x("/admin/settings/theme",{"theme.public_key":e.theme.publicKey,"theme.admin_key":e.theme.adminKey});i&&i.ok&&(e.theme.savedAdminKey=e.theme.adminKey,document.documentElement.dataset.theme=e.theme.adminKey,m(e.theme.adminKey),h("Theme berhasil disimpan."))}function I(){if(document.querySelector("#admin-theme-preview-style"))return;const i=document.createElement("style");i.id="admin-theme-preview-style",document.head.appendChild(i),m(e.theme.savedAdminKey)}function m(i){const t=document.querySelector("#admin-theme-preview-style"),a=e.theme.themes.find(o=>o.key===i);if(!t||!a||!a.tokens)return;const r=Object.entries(a.tokens).map(([o,d])=>`--${o}:${d};`).join("");t.textContent=`:root,html[data-theme="${i}"]{${r}}`}async function x(i,t){const a=await fetch(i,{method:"POST",headers:{...K(!0),"Content-Type":"application/json"},body:JSON.stringify(t)}),r=await a.json();return a.ok?r:(h(r.error||"Gagal menyimpan settings."),null)}function K(i){const t=y&&y.getCsrfToken&&y.getCsrfToken()||document.querySelector('meta[name="csrf-token"]')?.content||"";return i?{"X-CSRF-TOKEN":t,Accept:"application/json"}:{"X-CSRF-TOKEN":t,Accept:"application/json"}}function F(i){Object.entries(i).forEach(([t,a])=>{switch(t){case"site.logo_url":e.site.logo=a;break;case"site.favicon_url":e.site.favicon=a;break;case"site.topbar_email":e.site.email=a;break;case"site.topbar_phone":e.site.phone=a;break;case"site.name":e.site.name=a;break;case"site.tagline":e.site.tagline=a;break;case"site.footer_address":e.site.address=a;break;case"site.footer_email":e.site.footerEmail=a;break;case"site.footer_phone":e.site.footerPhone=a;break;case"site.footer_copyright":e.site.footerCopyright=a;break;case"site.footer_recent_news_count":e.site.footerRecentNewsCount=a;break;case"site.banner_badge":l(0).eyebrow=a,l(1).eyebrow=a;break;case"site.banner_headline":l(0).title=a;break;case"site.banner_headline_alt":l(1).title=a;break;case"site.banner_subtitle":l(0).caption=a;break;case"site.banner_subtitle_alt":l(1).caption=a;break;case"site.banner_image_1":l(0).image=a;break;case"site.banner_image_2":l(1).image=a;break;case"site.sidebar_heading_news":e.site.sidebar.news=a;break;case"site.sidebar_heading_recent":e.site.sidebar.recent=a;break;case"site.sidebar_heading_upcoming":e.site.sidebar.upcoming=a;break;case"site.sidebar_heading_past":e.site.sidebar.past=a;break;case"site.sidebar_heading_contact":e.site.sidebar.contact=a;break;case"site.color_primary":e.site.colors.primary=a;break;case"site.color_primary_hover":e.site.colors.primaryHover=a;break;case"site.color_primary_soft":e.site.colors.primarySoft=a;break}}),window.GenBISiteSettings={...window.GenBISiteSettings||{},...e.site}}function l(i){return e.site.heroSlides=Array.isArray(e.site.heroSlides)?e.site.heroSlides:[],e.site.heroSlides[i]=e.site.heroSlides[i]||{image:"",eyebrow:"",title:"",caption:""},e.site.sidebar=e.site.sidebar||{},e.site.colors=e.site.colors||{},e.site.heroSlides[i]}})();
