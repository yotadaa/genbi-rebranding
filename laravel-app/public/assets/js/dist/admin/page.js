(function(){"use strict";const{site:_}=window.GenBIData,r={..._,...window.GenBISiteSettings||{}},{renderAdminShell:T,showToast:g,icon:A}=window.GenBIAdmin,{routeUrl:C}=window.GenBIApp,h=document.querySelector('meta[name="csrf-token"]')?.content||"",b=[{key:"home",label:"Home",title:"Home Page",description:"Atur meta, hero, welcome section, video, progress, program, counter, team, dan news dari satu canvas editor."},{key:"about",label:"About",title:"About Page",description:"Konten tentang GenBI, visi, misi, tujuan, dan narasi organisasi."},{key:"faq",label:"FAQ",title:"FAQ Page",description:"Kelola pertanyaan umum dengan blok tanya jawab yang mudah dibaca."},{key:"service",label:"Service",title:"Service Page",description:"Modul bawaan CMS tetap disiapkan, tetapi tampil sebagai section editor yang rapi."},{key:"testimonial",label:"Testimonial",title:"Testimonial Page",description:"Konten testimonial dapat diaktifkan atau disembunyikan dari halaman publik."},{key:"news",label:"News",title:"News Page",description:"Atur heading, meta, dan pengantar halaman news."},{key:"event",label:"Event",title:"Event Page",description:"Atur heading dan meta untuk halaman event."},{key:"contact",label:"Contact",title:"Contact Page",description:"Atur alamat, email, telepon, dan peta lokasi Google Maps."},{key:"search",label:"Search",title:"Search Page",description:"Atur heading halaman pencarian."},{key:"terms",label:"Terms",title:"Terms Page",description:"Kelola terms and conditions dengan editor panjang."},{key:"privacy",label:"Privacy",title:"Privacy Page",description:"Kelola privacy policy dengan editor panjang."},{key:"team",label:"Team",title:"Team Page",description:"Atur heading dan meta direktori anggota."},{key:"portfolio",label:"Portfolio",title:"Portfolio Page",description:"Modul portfolio tetap ada untuk kompatibilitas CMS lama."}],I=[["Hero","Settings \u2192 Banner / Page \u2192 Home","Mengatur badge, headline, deskripsi, dan gambar hero landing page."],["Pengumuman","News CMS","Berita berkategori Pengumuman otomatis tampil di landing page."],["Program utama","Program Utama CMS","Item published + tampil di beranda muncul pada carousel landing."],["BPI / Team","Team Member CMS","Anggota yang ditandai tampil di beranda muncul pada carousel pengurus."],["Agenda utama","Agenda CMS","Agenda published terbaru muncul pada carousel landing."],["Berita terbaru","News CMS","Berita published terbaru muncul di landing page."],["Kontak","Page \u2192 Contact","Alamat, email, telepon, koordinat, dan map dipakai halaman /contact."]],y={home:[],about:m("About Heading","ABOUT US","About Us - GenBI Provinsi Jambi"),faq:m("FAQ Heading","FAQ","FAQ - GenBI Provinsi Jambi"),service:m("Service Heading","Our Services","Our Services - GenBI Provinsi Jambi"),testimonial:m("Testimonial Heading","TESTIMONIAL","Testimonial - GenBI Provinsi Jambi"),news:m("News Heading","NEWS","News - GenBI Provinsi Jambi"),event:m("Event Heading","EVENTS","Events - GenBI Provinsi Jambi"),search:m("Search Heading","SEARCH BY:","Search - GenBI Provinsi Jambi"),terms:w("Term & Condition Heading","TERMS & CONDITIONS","Terms and Conditions - GenBI Provinsi Jambi"),privacy:w("Privacy Policy Heading","PRIVACY POLICY","Privacy Policy - GenBI Provinsi Jambi"),team:m("Team Heading","Our Team","Team - GenBI Provinsi Jambi"),portfolio:m("Portfolio Heading","PORTFOLIO","Portfolio - GenBI Provinsi Jambi")},v={place_name:"Bank Indonesia Jambi",address:r.address,email:r.email,phone:r.phone,coordinates_label:"9HRM+74 Telanaipura, Kota Jambi, Jambi",maps_url:"https://www.google.com/maps/place/Bank+Indonesia+Jambi/@-1.6092871,103.5827899,17z/data=!3m1!4b1!4m6!3m5!1s0x2e25885c04515687:0xe424228e0264e09a!8m2!3d-1.6092871!4d103.5827899!16s%2Fg%2F1pzr95__x?hl=id&entry=ttu",latitude:"-1.609287",longitude:"103.582790",meta_title:"Contact | GenBI Provinsi Jambi",meta_keyword:"GenBI Jambi, Contact",meta_description:"Hubungi GenBI Provinsi Jambi untuk kolaborasi, informasi kegiatan, dan kebutuhan komunikasi resmi."};let d=window.location.hash?.replace("#","")||"home";b.some(e=>e.key===d)||(d="home"),T("page"),f(),k(),document.querySelector("#save-page")?.addEventListener("click",()=>{if(d==="contact"){P();return}B()});function m(e,a,t="GenBI Provinsi Jambi"){return[{type:"group",title:"Meta Items",blocks:[[e,a],["Meta Title",t],["Meta Keyword","GenBI Jambi"],["Meta Description","GenBI Provinsi Jambi"]]}]}function w(e,a,t){return[{type:"group",title:"Legal Content",blocks:[[e,a],["Content","<p>Tulis kebijakan resmi di sini. Gunakan paragraf pendek agar mudah dibaca pengunjung.</p><p>Bagian ini dapat diganti dengan dokumen legal final ketika sudah tersedia.</p>",{rich:!0,long:!0}],["Meta Title",t],["Meta Keyword","GenBI Jambi"],["Meta Description","GenBI Provinsi Jambi"]]}]}function u(e,a={}){return C(`admin.${e}`,a)}function f(){const e=document.querySelector("#page-tabs");e&&(e.innerHTML=b.map(a=>`
      <button type="button" class="admin-tab ${a.key===d?"is-active":""}" data-tab="${a.key}">${a.label}</button>
    `).join(""),e.querySelectorAll("button").forEach(a=>{a.addEventListener("click",()=>{d=a.dataset.tab,history.replaceState(null,"",`#${d}`),f(),k()})}))}async function k(){const e=document.querySelector("#page-editor-canvas"),a=document.querySelector("#page-editor-overview"),t=b.find(i=>i.key===d)||b[0];if(!e)return;if(d==="home"){E(e,a,t);return}if(d==="contact"){J(e,a,t);return}let o=y[d]||y.home;try{const i=await fetch(u("pageContent",{page:d}),{headers:{Accept:"application/json"},credentials:"same-origin"}),n=await i.json();i.ok&&Array.isArray(n.data)&&n.data.length&&(o=n.data)}catch{}e.innerHTML=`
      <section class="block-page-hero slide-in">
        <p class="eyebrow">Admin Page</p>
        <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${t.title}</h2>
        <p class="mt-4 max-w-3xl text-base leading-7 text-[rgb(var(--text-secondary))]">${t.description}</p>
      </section>
      ${o.map(M).join("")}
    `,a&&(a.innerHTML=`
        <p class="eyebrow">Outline</p>
        <div class="mt-4 grid gap-2">
          ${o.map((i,n)=>`
            <button type="button" class="outline-link" data-target="group-${n}">
              <span>${i.title}</span>
              <span>${i.blocks.length}</span>
            </button>
          `).join("")}
        </div>
        <div class="mt-6 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950 dark-theme-note">
          Editor ini memakai blok editable agar pengetikan terasa seperti area konten, bukan form sempit.
        </div>
      `,a.querySelectorAll(".outline-link").forEach(i=>{i.addEventListener("click",()=>document.querySelector(`#${i.dataset.target}`)?.scrollIntoView({behavior:"smooth",block:"start"}))})),H(e)}async function B(){const e=document.querySelector("#page-editor-canvas");if(!e)return;const a=Array.from(e.querySelectorAll(".block-editor-group")).map(i=>({type:"group",title:i.querySelector(".block-group-header h3")?.textContent?.trim()||"Content",blocks:Array.from(i.querySelectorAll(".block-editor-item")).map(n=>[n.querySelector(".block-editor-label")?.textContent?.trim()||"Content",n.querySelector(".block-editor-input")?.innerHTML||"",{rich:!0,long:n.classList.contains("is-long")}])})),t=await fetch(u("pageContent",{page:d}),{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":h},credentials:"same-origin",body:JSON.stringify({content:a})}),o=await t.json().catch(()=>({}));if(!t.ok)return g(o.error||"Gagal menyimpan halaman.");y[d]=a,g("Halaman berhasil disimpan.")}function l(e,a=""){return(r.home||{})[e]||a}function E(e,a,t){e.innerHTML=`
      <section class="block-page-hero slide-in">
        <p class="eyebrow">Admin Page</p>
        <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${t.title}</h2>
        <p class="mt-4 max-w-3xl text-base leading-7 text-[rgb(var(--text-secondary))]">${t.description}</p>
      </section>
      <section class="admin-card p-5 md:p-6 slide-in">
        <h3 class="serif text-2xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">Feature map landing page</h3>
        <p class="mt-2 text-sm leading-6 text-[rgb(var(--text-secondary))]">Daftar fitur admin yang sekarang menjadi sumber konten landing page.</p>
        <div class="admin-responsive-table mt-5">
          <table class="cms-table">
            <thead><tr><th>Feature</th><th>Admin source</th><th>Definition</th></tr></thead>
            <tbody>${I.map(([o,i,n])=>`<tr><td><strong>${c(o)}</strong></td><td>${c(i)}</td><td>${c(n)}</td></tr>`).join("")}</tbody>
          </table>
        </div>
      </section>
      <section class="admin-contact-grid mt-6 slide-in">
        <form class="admin-contact-form admin-card p-5 md:p-6" id="admin-home-form">
          <h3 class="serif text-2xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">Landing page text</h3>
          <p class="mt-2 text-sm leading-6 text-[rgb(var(--text-secondary))]">Field ini langsung dipakai oleh halaman publik <strong>/</strong>. Data itemnya tetap berasal dari News, Program Utama, Team, dan Agenda CMS.</p>
          <div class="mt-5 grid gap-4">
            ${s("Hero Badge","site.banner_badge",r.heroSlides?.[0]?.eyebrow||"GenBI Provinsi Jambi")}
            ${p("Hero Title","site.banner_headline",r.heroSlides?.[0]?.title||"")}
            ${p("Hero Subtitle","site.banner_subtitle",r.heroSlides?.[0]?.caption||"")}
            ${s("Announcement Eyebrow","home.announcement_eyebrow",l("announcementEyebrow","Pengumuman"))}
            ${s("Announcement Title","home.announcement_title",l("announcementTitle","Info penting untuk anggota dan publik."))}
            ${p("Announcement Description","home.announcement_description",l("announcementDescription","Pembaruan resmi, agenda penting, dan kabar prioritas GenBI Jambi ditampilkan dalam format ringkas agar mudah dipantau."))}
            ${s("Program Eyebrow","home.program_eyebrow",l("programEyebrow","Program utama"))}
            ${s("Program Title","home.program_title",l("programTitle","Program yang dekat dengan anggota dan masyarakat."))}
            ${p("Program Description","home.program_description",l("programDescription","Setiap program dirancang sebagai ruang belajar, ruang kolaborasi, dan ruang kontribusi agar anggota GenBI Jambi tumbuh sekaligus memberi manfaat."))}
            ${s("Team Eyebrow","home.team_eyebrow",l("teamEyebrow","GenBI Provinsi Jambi"))}
            ${s("Team Title","home.team_title",l("teamTitle","Wajah pengurus yang menjaga arah gerak organisasi."))}
            ${p("Team Description","home.team_description",l("teamDescription","Badan Pengurus Inti menghubungkan ide, anggota, dan agenda kerja agar GenBI Jambi tetap solid, aktif, dan relevan bagi lingkungan sekitar."))}
            ${s("Event Eyebrow","home.event_eyebrow",l("eventEyebrow","Agenda utama"))}
            ${s("Event Title","home.event_title",l("eventTitle","Kegiatan yang lahir dari kebutuhan sekitar."))}
            ${p("Event Description","home.event_description",l("eventDescription","Agenda GenBI Jambi tidak berhenti di seremoni. Setiap kegiatan menjadi kesempatan untuk belajar, melayani, dan membangun jejaring kebaikan."))}
            ${s("News Eyebrow","home.news_eyebrow",l("newsEyebrow","Latest news"))}
            ${s("News Title","home.news_title",l("newsTitle","Berita terbaru"))}
          </div>
          <div class="mt-6"><button type="submit" class="btn btn-primary">Simpan Landing Page</button></div>
        </form>
        <aside class="admin-contact-preview admin-card p-5 md:p-6">
          <p class="eyebrow">Connected modules</p>
          <div class="mt-4 grid gap-3 text-sm leading-6 text-[rgb(var(--text-secondary))]">
            <p><strong>Program Utama:</strong> kelola kartu carousel dari menu Program Utama.</p>
            <p><strong>Team Member:</strong> tombol Add/Remove Beranda menentukan pengurus yang tampil.</p>
            <p><strong>News:</strong> kategori Pengumuman dan berita terbaru otomatis diambil dari data published.</p>
            <p><strong>Agenda:</strong> data agenda published otomatis tampil pada section Agenda utama.</p>
          </div>
        </aside>
      </section>
    `,a&&(a.innerHTML='<p class="eyebrow">Landing map</p><div class="mt-4 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950 dark-theme-note">Admin/Page Home sekarang menyimpan teks section landing page. Data card tetap dikelola di modul masing-masing.</div>'),e.querySelector("#admin-home-form")?.addEventListener("submit",N)}function M(e,a){return`
      <section id="group-${a}" class="block-editor-group slide-in">
        <div class="block-group-header">
          <span>${A("documentText")}</span>
          <h3>${e.title}</h3>
        </div>
        <div class="grid gap-4">
          ${e.blocks.map(t=>G(t[0],t[1],t[2]||{})).join("")}
        </div>
      </section>
    `}function G(e,a,t={}){const o=t.long||String(a).length>90,i=t.rich||t.code?a:c(a).replaceAll(`
`,"<br>");return`
      <section class="block-editor-item ${o?"is-long":""} ${t.code?"is-code":""}" data-block>
        <div class="block-editor-toolbar" aria-hidden="true">
          <button type="button" data-command="bold"><strong>B</strong></button>
          <button type="button" data-command="italic"><em>I</em></button>
          <button type="button" data-command="insertUnorderedList">&#8226; List</button>
        </div>
        <label class="block-editor-label">${e}</label>
        <div class="block-editor-input" contenteditable="true" spellcheck="false" data-placeholder="Tulis ${e.toLowerCase()}...">${i}</div>
      </section>
    `}function c(e=""){return String(e).replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function H(e){e.querySelectorAll(".block-editor-toolbar button").forEach(a=>{a.addEventListener("mousedown",t=>t.preventDefault()),a.addEventListener("click",()=>{const t=a.closest("[data-block]")?.querySelector(".block-editor-input");t&&(t.focus(),document.execCommand(a.dataset.command,!1,null))})})}function L(e){const a=String(e.latitude||"").trim(),t=String(e.longitude||"").trim();return!a||!t?"":`https://www.google.com/maps?q=${encodeURIComponent(`${a},${t}`)}&z=17&output=embed`}async function j(){try{const e=await fetch(u("contactSetting"),{headers:{Accept:"application/json"},credentials:"same-origin"});if(!e.ok)throw new Error("Fetch failed");const a=await e.json();return{...v,...a.data||{}}}catch{return{...v}}}function J(e,a,t){e.innerHTML=`
      <section class="block-page-hero slide-in">
        <p class="eyebrow">Admin Page</p>
        <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${t.title}</h2>
        <p class="mt-4 max-w-3xl text-base leading-7 text-[rgb(var(--text-secondary))]">${t.description}</p>
      </section>
      <section class="admin-contact-grid mt-6 slide-in" id="admin-contact-grid">
        <form class="admin-contact-form admin-card p-5 md:p-6" id="admin-contact-form"></form>
        <aside class="admin-contact-preview admin-card p-5 md:p-6" id="admin-contact-preview"></aside>
      </section>
    `,a&&(a.innerHTML=`
        <p class="eyebrow">Contact</p>
        <div class="mt-4 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950 dark-theme-note">
          Data kontak ini dipakai langsung oleh halaman publik <strong>/contact</strong>.
        </div>
      `);const o=e.querySelector("#admin-contact-form"),i=e.querySelector("#admin-contact-preview");!o||!i||j().then(n=>{o.innerHTML=q(n),D(o,i),x(o,i)})}function q(e){return`
      <h3 class="serif text-2xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">Contact Settings</h3>
      <p class="mt-2 text-sm leading-6 text-[rgb(var(--text-secondary))]">Kelola lokasi dan metadata kontak untuk halaman publik.</p>
      <div class="mt-5 grid gap-4">
        ${s("Place Name","place_name",e.place_name)}
        ${p("Address","address",e.address)}
        <div class="grid gap-4 md:grid-cols-2">
          ${s("Email","email",e.email,"email")}
          ${s("Phone","phone",e.phone)}
        </div>
        ${s("Coordinates Label","coordinates_label",e.coordinates_label)}
        ${s("Google Maps Link","maps_url",e.maps_url)}
        <div class="grid gap-4 md:grid-cols-2">
          ${s("Latitude","latitude",e.latitude,"text","Contoh: -1.609287")}
          ${s("Longitude","longitude",e.longitude,"text","Contoh: 103.582790")}
        </div>
        ${s("Meta Title","meta_title",e.meta_title)}
        ${s("Meta Keyword","meta_keyword",e.meta_keyword)}
        ${p("Meta Description","meta_description",e.meta_description)}
      </div>
      <div class="mt-6">
        <button type="submit" class="btn btn-primary">Simpan Contact Settings</button>
      </div>
    `}function s(e,a,t,o="text",i=""){return`
      <label class="config-field">
        <span>${e}</span>
        <input class="config-input" type="${o}" name="${a}" value="${c(t||"")}" placeholder="${c(i)}" />
      </label>
    `}function p(e,a,t){return`
      <label class="config-field">
        <span>${e}</span>
        <textarea class="config-input min-h-24" name="${a}" rows="3">${c(t||"")}</textarea>
      </label>
    `}function S(e){return{place_name:e.elements.place_name.value.trim(),address:e.elements.address.value.trim(),email:e.elements.email.value.trim(),phone:e.elements.phone.value.trim(),coordinates_label:e.elements.coordinates_label.value.trim(),maps_url:e.elements.maps_url.value.trim(),latitude:e.elements.latitude.value.trim(),longitude:e.elements.longitude.value.trim(),meta_title:e.elements.meta_title.value.trim(),meta_keyword:e.elements.meta_keyword.value.trim(),meta_description:e.elements.meta_description.value.trim()}}function x(e,a){const t=S(e),o=L(t);a.innerHTML=`
      <p class="eyebrow">Live Preview</p>
      <article class="admin-contact-preview-card">
        <span class="blue-badge">Map preview</span>
        <h4 class="serif mt-4 text-2xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${c(t.place_name||"Contact Location")}</h4>
        ${t.coordinates_label?`<p class="mt-3 text-xs font-bold uppercase tracking-[0.11em] text-blue-800">${c(t.coordinates_label)}</p>`:""}
        <p class="mt-3 text-sm leading-7 text-[rgb(var(--text-secondary))]">${c(t.address||"Alamat belum diisi")}</p>
        ${t.maps_url?`<a class="btn btn-primary mt-5 w-fit" href="${c(t.maps_url)}" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>`:""}
      </article>
      <div class="admin-contact-preview-map">
        ${o?`<iframe src="${c(o)}" title="Map preview" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>`:'<div class="admin-contact-preview-empty"><strong>Map preview belum aktif.</strong><p>Isi latitude dan longitude untuk memunculkan peta.</p></div>'}
      </div>
    `}function D(e,a){e.addEventListener("input",()=>x(e,a)),e.addEventListener("submit",t=>{t.preventDefault(),P(e)})}async function P(e=document.querySelector("#admin-contact-form")){if(!e)return;const a=S(e),t=await fetch(u("contactSettingUpdate"),{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":h},credentials:"same-origin",body:JSON.stringify(a)}),o=await t.json().catch(()=>({}));if(!t.ok){g(o.error||"Gagal menyimpan contact settings.");return}g("Contact settings berhasil disimpan."),k()}async function N(e){e?.preventDefault();const a=document.querySelector("#admin-home-form");if(!a)return;const t={};a.querySelectorAll("[name]").forEach($=>{t[$.name]=$.value.trim()});const o=await fetch(u("pageHomeUpdate"),{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":h},credentials:"same-origin",body:JSON.stringify(t)}),i=await o.json().catch(()=>({}));if(!o.ok){g(i.error||"Gagal menyimpan landing page.");return}const n=r.home=r.home||{};r.heroSlides=Array.isArray(r.heroSlides)&&r.heroSlides.length?r.heroSlides:[{},{}],r.heroSlides[0]=r.heroSlides[0]||{},r.heroSlides[1]=r.heroSlides[1]||{},r.heroSlides[0].eyebrow=t["site.banner_badge"],r.heroSlides[1].eyebrow=t["site.banner_badge"],r.heroSlides[0].title=t["site.banner_headline"],r.heroSlides[0].caption=t["site.banner_subtitle"],n.announcementEyebrow=t["home.announcement_eyebrow"],n.announcementTitle=t["home.announcement_title"],n.announcementDescription=t["home.announcement_description"],n.programEyebrow=t["home.program_eyebrow"],n.programTitle=t["home.program_title"],n.programDescription=t["home.program_description"],n.teamEyebrow=t["home.team_eyebrow"],n.teamTitle=t["home.team_title"],n.teamDescription=t["home.team_description"],n.eventEyebrow=t["home.event_eyebrow"],n.eventTitle=t["home.event_title"],n.eventDescription=t["home.event_description"],n.newsEyebrow=t["home.news_eyebrow"],n.newsTitle=t["home.news_title"],window.GenBISiteSettings={...window.GenBISiteSettings||{},...r},g("Landing page berhasil disimpan. Buka Visit Website untuk melihat hasil live.")}})();
