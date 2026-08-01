(function(){"use strict";const{renderShell:F,getParam:q}=window.GenBIApp,w=window.GenBIAPI,P=window.GenBIAPICore;F("prestasi");const f=document.querySelector("#prestasi-submit-root"),I=A(),j=f?.querySelector('#prestasi-submit-form[data-ssr="true"]');if(j){B(),window.GenBIUI?.enhanceProjectSelects?window.GenBIUI.enhanceProjectSelects(f):window.GenBIUI?.enhanceNativeSelects?.(f,"select.js-custom-select",{iconHtml:'<span aria-hidden="true">\u2304</span>',portal:!1,wrapperClass:"custom-select custom-select-root"}),G(j,I);return}I?C(I):x("Token tidak ditemukan di URL.");function A(){const t=window.location.pathname.match(/\/prestasi\/submit\/([A-Za-z0-9_-]+)/);return t?decodeURIComponent(t[1]):q("token")||""}async function C(e){try{const t=await fetch(P.routeUrl("public.prestasiSubmit",{token:e}),{headers:{Accept:"application/json"},credentials:"same-origin"});if(!t.ok){const d=await t.json().catch(()=>({}));x(d.error||"Token tidak valid atau sudah digunakan.");return}const i=await t.json();i.data?.valid?$(e,i.data.label||""):x("Token tidak valid.")}catch{x("Gagal memvalidasi token. Periksa koneksi internet.")}}function x(e){f.innerHTML=`
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
    `}function $(e,t){const i=["QRIS","KTI","Essay","Inovasi Desa","Kreativitas","Ekonomi Syariah"],d=["Universitas Jambi","UIN Sultan Thaha","Alumni"],m=new Date().getFullYear(),b=Array.from({length:10},(l,a)=>m-a);f.innerHTML=`
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
                    ${i.map(l=>`<option value="${l}"></option>`).join("")}
                  </datalist>
                  <p class="config-hint mt-2">Bisa isi kategori baru jika tidak ada di pilihan rekomendasi.</p>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                  <label class="config-field">
                    <span>Tahun <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="year" required>
                      ${b.map(l=>`<option value="${l}">${l}</option>`).join("")}
                    </select>
                  </label>
                  <label class="config-field">
                    <span>Komisariat <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="campus" required>
                      ${d.map(l=>`<option value="${l}">${l}</option>`).join("")}
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
    `,B(),window.GenBIUI?.enhanceProjectSelects?window.GenBIUI.enhanceProjectSelects(f):window.GenBIUI?.enhanceNativeSelects?.(f,"select.js-custom-select",{iconHtml:'<span aria-hidden="true">\u2304</span>',portal:!1,wrapperClass:"custom-select custom-select-root"}),document.querySelector("#prestasi-submit-form")?.addEventListener("submit",async l=>{l.preventDefault();const a=l.currentTarget,s=new FormData(a),c=a.querySelector('[type="submit"]'),v=Array.from(s.getAll("photos[]")).filter(n=>n instanceof File&&n.name),r={title:String(s.get("title")||"").trim(),category:s.get("category")||"",year:s.get("year")||"",campus:s.get("campus")||"",name:String(s.get("name")||"").trim(),institution:String(s.get("institution")||"").trim(),description:String(s.get("description")||"").trim(),content:String(s.get("content")||"").trim()};if(!r.title||!r.category||!r.year||!r.campus||!r.name){u("Mohon lengkapi semua field yang wajib diisi.","error");return}if(v.length>6){u("Maksimal 6 foto dapat diunggah.","error");return}for(const n of v)if(n.size>5*1024*1024){u(`Ukuran foto ${n.name} melebihi batas 5MB.`,"error");return}s.set("title",r.title),s.set("name",r.name),s.set("institution",r.institution),s.set("description",r.description),s.set("content",r.content),c.disabled=!0,c.textContent="Mengirim...";try{const n=w.getCsrfToken?w.getCsrfToken():"";n&&s.set("_csrf_token",n);const y=await fetch(P.routeUrl("public.prestasiSubmit",{token:e}),{method:"POST",headers:{Accept:"application/json","X-CSRF-TOKEN":n},credentials:"same-origin",body:s}),g=await y.json();if(y.ok&&g.data)L();else{const T=Array.isArray(g.details)?g.details.join(", "):"",k=[g.detail,g.code,g.message].filter(Boolean).join(" \xB7 ");u((g.error||"Gagal mengirim prestasi.")+(T?" "+T:"")+(k?" ("+k+")":""),"error"),c.disabled=!1,c.textContent="Submit Prestasi"}}catch{u("Gagal mengirim. Periksa koneksi internet.","error"),c.disabled=!1,c.textContent="Submit Prestasi"}})}function L(){f.innerHTML=`
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
    `}function G(e,t){e.addEventListener("submit",async i=>{i.preventDefault();const d=new FormData(e),m=e.querySelector('[type="submit"]'),b=Array.from(d.getAll("photos[]")).filter(a=>a instanceof File&&a.name);if(["title","category","year","campus","name"].some(a=>!String(d.get(a)||"").trim())){u("Mohon lengkapi semua field yang wajib diisi.","error");return}if(b.length>6||b.some(a=>a.size>5*1024*1024)){u("Maksimal 6 foto dapat diunggah, masing-masing maksimal 5MB.","error");return}m.disabled=!0,m.textContent="Mengirim...";try{const a=w.getCsrfToken?w.getCsrfToken():"";a&&d.set("_csrf_token",a);const s=await fetch(P.routeUrl("public.prestasiSubmit",{token:t}),{method:"POST",headers:{Accept:"application/json","X-CSRF-TOKEN":a},credentials:"same-origin",body:d}),c=await s.json().catch(()=>({}));if(s.ok&&c.data){L();return}const v=Array.isArray(c.details)?` ${c.details.join(", ")}`:"";u((c.error||"Gagal mengirim prestasi.")+v,"error")}catch{u("Gagal mengirim. Periksa koneksi internet.","error")}m.disabled=!1,m.textContent="Submit Prestasi"})}function B(){const e=document.querySelector("#prestasi-photo-input"),t=document.querySelector("#prestasi-photo-trigger"),i=document.querySelector("#prestasi-photo-clear"),d=document.querySelector("#prestasi-photo-list"),m=document.querySelector("#prestasi-photo-empty"),b=document.querySelector("#prestasi-photo-preview"),l=document.querySelector("#prestasi-photo-counter"),a=document.querySelector("#prestasi-photo-preview-strip"),s=document.querySelector("#prestasi-photo-scroll-left"),c=document.querySelector("#prestasi-photo-scroll-right"),v=document.querySelector("#prestasi-photo-gallery-status");if(!e||!t||!i||!d||!m||!b||!l||!a||!s||!c||!v)return;let r=[],n=[];const y=()=>{const o=new DataTransfer;n.forEach(p=>o.items.add(p)),e.files=o.files},g=()=>{if(!r.length){b.classList.add("hidden"),l.textContent="0 / 0",a.innerHTML="";return}b.classList.remove("hidden"),l.textContent=`${r.length} foto`,a.innerHTML=r.map((p,h)=>`
        <article class="public-upload-preview-card">
          <img src="${p.src}" alt="Preview ${S(p.name)}" />
          <div>
            <strong>${S(p.name)}</strong>
            <span>${U(p.size)}</span>
            <button type="button" class="public-upload-remove" data-remove-photo-index="${h}">Hapus foto ini</button>
          </div>
        </article>
      `).join(""),a.querySelectorAll("[data-remove-photo-index]").forEach(p=>{p.addEventListener("click",()=>{const h=Number(p.dataset.removePhotoIndex);Number.isInteger(h)&&(n=n.filter((D,E)=>E!==h),y(),k())})});const o=r.length>1;s.disabled=!o,c.disabled=!o},T=o=>new Promise(p=>{if(!M(o)){p(null);return}const h=new FileReader;h.onload=()=>p({src:String(h.result||""),name:o.name||"foto",size:o.size||0}),h.onerror=()=>p(null),h.readAsDataURL(o)}),k=async()=>{const o=n;if(o.length===0){m.classList.remove("hidden"),d.classList.add("hidden"),d.innerHTML="",i.classList.add("hidden"),r=[],v.textContent="Belum ada galeri tambahan.",g();return}m.classList.add("hidden"),d.classList.add("hidden"),i.classList.remove("hidden"),r=(await Promise.all(o.map(T))).filter(Boolean),v.textContent=`${o.length} foto dipilih. Geser horizontal untuk melihat semua foto.`,g(),d.innerHTML=""};t.addEventListener("click",()=>e.click()),s.addEventListener("click",()=>a.scrollBy({left:-220,behavior:"smooth"})),c.addEventListener("click",()=>a.scrollBy({left:220,behavior:"smooth"})),i.addEventListener("click",()=>{n=[],e.value="",k()}),e.addEventListener("change",()=>{const o=Array.from(e.files||[]);o.length&&(n=[...n,...o].slice(0,6),y(),k())}),k()}function M(e){return e instanceof File?e.type&&e.type.startsWith("image/")?!0:/\.(jpe?g|png|webp|gif)$/i.test(e.name||""):!1}function U(e=0){const t=Number(e)||0;return t>=1024*1024?`${(t/(1024*1024)).toFixed(1)} MB`:t>=1024?`${Math.round(t/1024)} KB`:`${t} B`}function u(e,t="info"){let i=document.querySelector("#public-mini-toast");i||(i=document.createElement("div"),i.id="public-mini-toast",i.className="public-mini-toast",document.body.appendChild(i)),i.textContent=e,i.classList.toggle("toast-error",t==="error"),i.classList.add("is-visible"),clearTimeout(u.timer),u.timer=setTimeout(()=>i.classList.remove("is-visible"),3e3)}function S(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}})();
