(function(){"use strict";const{renderShell:I,getParam:j}=window.GenBIApp,T=window.GenBIAPI,P=window.GenBIAPICore;I("prestasi");const f=document.querySelector("#prestasi-submit-root"),L=B();L?F(L):w("Token tidak ditemukan di URL.");function B(){const t=window.location.pathname.match(/\/prestasi\/submit\/([^/?#]+)/i);return t?decodeURIComponent(t[1]):j("token")||""}async function F(e){try{const t=await fetch(P.routeUrl("public.prestasiSubmit",{token:e}),{headers:{Accept:"application/json"},credentials:"same-origin"});if(!t.ok){const u=await t.json().catch(()=>({}));w(u.error||"Token tidak valid atau sudah digunakan.");return}const i=await t.json();i.data?.valid?$(e,i.data.label||""):w("Token tidak valid.")}catch{w("Gagal memvalidasi token. Periksa koneksi internet.")}}function w(e){f.innerHTML=`
      <section class="prestasi-submit-hero bg-stone py-16 md:py-24">
        <div class="site-container fade-up text-center">
          <p class="eyebrow text-red-600">Token Tidak Valid</p>
          <h1 class="page-title mt-5">Akses Ditolak</h1>
          <p class="lead mt-7 mx-auto max-w-2xl text-red-700">${S(e)}</p>
          <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6 max-w-lg mx-auto">
            <p class="text-sm text-red-800">Token ini mungkin sudah digunakan, kedaluwarsa, atau tidak valid. Hubungi admin GenBI Jambi untuk mendapatkan token baru.</p>
          </div>
        </div>
      </section>
    `}function $(e,t){const i=["QRIS","KTI","Essay","Inovasi Desa","Kreativitas","Ekonomi Syariah"],u=["Universitas Jambi","UIN Sultan Thaha","Alumni"],v=new Date().getFullYear(),k=Array.from({length:10},(o,c)=>v-c);f.innerHTML=`
      <section class="prestasi-submit-hero bg-stone py-14 md:py-20">
        <div class="site-container fade-up">
          <div class="mx-auto max-w-2xl text-center">
            <p class="eyebrow">Form Prestasi</p>
            <h1 class="page-title mt-5">Pengajuan Prestasi</h1>
            ${t?`<p class="mt-4 text-sm text-neutral-600">Token: <strong>${S(t)}</strong></p>`:""}
            <p class="lead mt-5">Isi form di bawah untuk mengajukan prestasi. Form ini hanya bisa digunakan sekali.</p>
          </div>
        </div>
      </section>
      <section class="prestasi-submit-body bg-cream py-12 md:py-16">
        <div class="site-container">
          <form id="prestasi-submit-form" class="mx-auto max-w-2xl">
            <div class="soft-card p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Informasi Prestasi</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Judul Prestasi <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="title" placeholder="Nama prestasi yang diraih" required />
                </label>
                <label class="config-field">
                  <span>Kategori <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="category" list="prestasi-category-options" placeholder="Tulis kategori atau pilih rekomendasi" required />
                  <datalist id="prestasi-category-options">
                    ${i.map(o=>`<option value="${o}"></option>`).join("")}
                  </datalist>
                  <p class="config-hint mt-2">Bisa isi kategori baru jika tidak ada di pilihan rekomendasi.</p>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                  <label class="config-field">
                    <span>Tahun <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="year" required>
                      ${k.map(o=>`<option value="${o}">${o}</option>`).join("")}
                    </select>
                  </label>
                  <label class="config-field">
                    <span>Komisariat <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="campus" required>
                      ${u.map(o=>`<option value="${o}">${o}</option>`).join("")}
                    </select>
                  </label>
                </div>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Informasi Anggota</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Nama Anggota <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="name" placeholder="Nama lengkap penerima prestasi" required />
                </label>
                <label class="config-field">
                  <span>Institusi (opsional)</span>
                  <input class="input-soft" name="institution" placeholder="Nama institusi penyelenggara" />
                </label>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Deskripsi</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Deskripsi Singkat</span>
                  <textarea class="input-soft" name="description" rows="3" placeholder="Ringkasan singkat prestasi (maks 500 karakter)"></textarea>
                </label>
                <label class="config-field">
                  <span>Detail Prestasi</span>
                  <textarea class="input-soft" name="content" rows="6" placeholder="Ceritakan detail prestasi yang diraih..."></textarea>
                </label>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Foto Prestasi</h2>
              <div class="mt-6 grid gap-4">
                <div class="public-upload-field public-prestasi-photo-card prestasi-photo-uploader" data-public-upload-field>
                  <input id="prestasi-photo-input" class="hidden" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple />
                  <div class="public-upload-empty" id="prestasi-photo-empty">
                    <strong>Belum ada foto</strong>
                  </div>
                  <button type="button" class="btn btn-secondary w-full" id="prestasi-photo-trigger">Upload Foto</button>
                  <input class="input-soft" id="prestasi-photo-url" name="image_url" placeholder="URL gambar (opsional)" />
                  <p id="prestasi-photo-gallery-status" class="text-sm text-neutral-500">Belum ada galeri tambahan.</p>
                  <div class="public-upload-list hidden" id="prestasi-photo-list" aria-live="polite"></div>
                    <div class="public-upload-preview hidden" id="prestasi-photo-preview">
                      <div class="public-upload-preview-controls">
                        <strong>Preview foto terpilih</strong>
                        <span id="prestasi-photo-counter">1 / 1</span>
                      </div>
                      <div class="public-upload-preview-slider">
                        <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-left" aria-label="Geser preview ke kiri">\u2039</button>
                        <div class="public-upload-preview-strip" id="prestasi-photo-preview-strip" aria-label="Preview foto terpilih"></div>
                        <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-right" aria-label="Geser preview ke kanan">\u203A</button>
                      </div>
                    </div>
                    <div class="public-upload-actions">
                      <button type="button" class="chip chip-dark hidden" id="prestasi-photo-clear">Hapus Semua</button>
                    </div>
                </div>
                <p class="text-sm text-neutral-500">Bisa unggah 1 atau lebih foto. Maksimal 6 foto, masing-masing maksimal 5MB. Format: JPG, PNG, WebP, atau GIF.</p>
              </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
              <p class="text-sm text-neutral-500">Form hanya bisa disubmit sekali.</p>
              <button type="submit" class="btn btn-primary">Submit Prestasi</button>
            </div>
          </form>
        </div>
      </section>
    `,A(),window.GenBIUI?.enhanceProjectSelects?window.GenBIUI.enhanceProjectSelects(f):window.GenBIUI?.enhanceNativeSelects?.(f,"select.js-custom-select",{iconHtml:'<span aria-hidden="true">\u2304</span>',portal:!1,wrapperClass:"custom-select custom-select-root"}),document.querySelector("#prestasi-submit-form")?.addEventListener("submit",async o=>{o.preventDefault();const c=o.currentTarget,a=new FormData(c),d=c.querySelector('[type="submit"]'),b=Array.from(a.getAll("photos[]")).filter(n=>n instanceof File&&n.name),r={title:String(a.get("title")||"").trim(),category:a.get("category")||"",year:a.get("year")||"",campus:a.get("campus")||"",name:String(a.get("name")||"").trim(),institution:String(a.get("institution")||"").trim(),description:String(a.get("description")||"").trim(),content:String(a.get("content")||"").trim()};if(!r.title||!r.category||!r.year||!r.campus||!r.name){g("Mohon lengkapi semua field yang wajib diisi.","error");return}if(b.length>6){g("Maksimal 6 foto dapat diunggah.","error");return}for(const n of b)if(n.size>5*1024*1024){g(`Ukuran foto ${n.name} melebihi batas 5MB.`,"error");return}a.set("title",r.title),a.set("name",r.name),a.set("institution",r.institution),a.set("description",r.description),a.set("content",r.content),d.disabled=!0,d.textContent="Mengirim...";try{const n=T.getCsrfToken?T.getCsrfToken():"";n&&a.set("_token",n);const y=await fetch(P.routeUrl("public.prestasiSubmit",{token:e}),{method:"POST",headers:{Accept:"application/json","X-CSRF-TOKEN":n},credentials:"same-origin",body:a}),x=await y.text();let p={};try{p=x?JSON.parse(x):{}}catch{throw new Error("Server tidak mengembalikan respons JSON yang valid.")}if(y.ok&&p.data)q();else{const h=Array.isArray(p.details)?p.details.join(", "):"",s=[p.detail,p.code,p.message].filter(Boolean).join(" \xB7 ");g((p.error||"Gagal mengirim prestasi.")+(h?" "+h:"")+(s?" ("+s+")":""),"error"),d.disabled=!1,d.textContent="Submit Prestasi"}}catch{g("Gagal mengirim. Periksa koneksi internet.","error"),d.disabled=!1,d.textContent="Submit Prestasi"}})}function q(){f.innerHTML=`
      <section class="bg-stone py-16 md:py-24">
        <div class="site-container fade-up text-center">
          <p class="eyebrow text-green-600">Berhasil</p>
          <h1 class="page-title mt-5">Prestasi Terkirim</h1>
          <p class="lead mt-7 mx-auto max-w-2xl">Data prestasi berhasil dikirim dan sedang menunggu review admin. Terima kasih atas kontribusinya!</p>
          <div class="mt-8 rounded-2xl border border-green-200 bg-green-50 p-6 max-w-lg mx-auto">
            <p class="text-sm text-green-800">Token ini sudah tidak bisa digunakan lagi. Jika perlu mengajukan prestasi lain, hubungi admin untuk token baru.</p>
          </div>
        </div>
      </section>
    `}function A(){const e=document.querySelector("#prestasi-photo-input"),t=document.querySelector("#prestasi-photo-trigger"),i=document.querySelector("#prestasi-photo-clear"),u=document.querySelector("#prestasi-photo-list"),v=document.querySelector("#prestasi-photo-empty"),k=document.querySelector("#prestasi-photo-preview"),o=document.querySelector("#prestasi-photo-counter"),c=document.querySelector("#prestasi-photo-preview-strip"),a=document.querySelector("#prestasi-photo-scroll-left"),d=document.querySelector("#prestasi-photo-scroll-right"),b=document.querySelector("#prestasi-photo-gallery-status");if(!e||!t||!i||!u||!v||!k||!o||!c||!a||!d||!b)return;let r=[],n=[];const y=()=>{const s=new DataTransfer;n.forEach(l=>s.items.add(l)),e.files=s.files},x=()=>{if(!r.length){k.classList.add("hidden"),o.textContent="0 / 0",c.innerHTML="";return}k.classList.remove("hidden"),o.textContent=`${r.length} foto`,c.innerHTML=r.map((l,m)=>`
        <article class="public-upload-preview-card">
          <img src="${l.src}" alt="Preview ${S(l.name)}" />
          <div>
            <strong>${S(l.name)}</strong>
            <span>${G(l.size)}</span>
            <button type="button" class="public-upload-remove" data-remove-photo-index="${m}">Hapus foto ini</button>
          </div>
        </article>
      `).join(""),c.querySelectorAll("[data-remove-photo-index]").forEach(l=>{l.addEventListener("click",()=>{const m=Number(l.dataset.removePhotoIndex);Number.isInteger(m)&&(n=n.filter((E,M)=>M!==m),y(),h())})});const s=r.length>1;a.disabled=!s,d.disabled=!s},p=s=>new Promise(l=>{if(!C(s)){l(null);return}const m=new FileReader;m.onload=()=>l({src:String(m.result||""),name:s.name||"foto",size:s.size||0}),m.onerror=()=>l(null),m.readAsDataURL(s)}),h=async()=>{const s=n;if(s.length===0){v.classList.remove("hidden"),u.classList.add("hidden"),u.innerHTML="",i.classList.add("hidden"),r=[],b.textContent="Belum ada galeri tambahan.",x();return}v.classList.add("hidden"),u.classList.add("hidden"),i.classList.remove("hidden"),r=(await Promise.all(s.map(p))).filter(Boolean),b.textContent=`${s.length} foto dipilih. Geser horizontal untuk melihat semua foto.`,x(),u.innerHTML=""};t.addEventListener("click",()=>e.click()),a.addEventListener("click",()=>c.scrollBy({left:-220,behavior:"smooth"})),d.addEventListener("click",()=>c.scrollBy({left:220,behavior:"smooth"})),i.addEventListener("click",()=>{n=[],e.value="",h()}),e.addEventListener("change",()=>{const s=Array.from(e.files||[]);s.length&&(n=[...n,...s].slice(0,6),y(),h())}),h()}function C(e){return e instanceof File?e.type&&e.type.startsWith("image/")?!0:/\.(jpe?g|png|webp|gif)$/i.test(e.name||""):!1}function G(e=0){const t=Number(e)||0;return t>=1024*1024?`${(t/(1024*1024)).toFixed(1)} MB`:t>=1024?`${Math.round(t/1024)} KB`:`${t} B`}function g(e,t="info"){let i=document.querySelector("#public-mini-toast");i||(i=document.createElement("div"),i.id="public-mini-toast",i.className="public-mini-toast",document.body.appendChild(i)),i.textContent=e,i.classList.toggle("toast-error",t==="error"),i.classList.add("is-visible"),clearTimeout(g.timer),g.timer=setTimeout(()=>i.classList.remove("is-visible"),3e3)}function S(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}})();
