(function(){"use strict";const _=window.GenBIAPICore,I=window.GenBIAPI||{},b=window.GenBIAdmin||{},se=window.GenBIApp||{},i=b.escapeHtml||((e="")=>String(e).replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")),ie=se.adminUrl||(e=>`/admin/${e}`),D='<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>',Se='<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',H='<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>',oe='<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65m-1.12-4.02a1.88 1.88 0 0 1 2.49 2.49L7.5 19l-4.5 1.5L4.5 16 17.27 3.23c.34-.34.75-.52 1.12-.11Z"/></svg>';function $(e,t={}){return _?.routeUrl?_.routeUrl(e,t,window.location):""}async function q(e,t={}){if(I.requestJson)return I.requestJson(e,t);const s={Accept:"application/json",...t.headers||{}},d=(t.method||"GET").toUpperCase();if(d!=="GET"&&d!=="HEAD"){const h=document.querySelector('meta[name="csrf-token"]')?.content||"";h&&(s["X-CSRF-TOKEN"]=h)}const p=await fetch(e,{...t,headers:s,credentials:"same-origin"}),u=await p.json().catch(()=>({}));if(!p.ok)throw new Error(u.error||`Request failed: ${p.status}`);return u}function P(e=""){try{return new URL(String(e||""),window.location.origin).href}catch{return String(e||"")}}async function W(e){const t=P(e);if(t){if(navigator.clipboard?.writeText)await navigator.clipboard.writeText(t);else{const s=document.createElement("input");s.value=t,document.body.appendChild(s),s.select(),document.execCommand("copy"),s.remove()}b.showToast?.("Link presensi disalin.")}}function X(e,t,s=220){if(!(!e||!t)){if(e.innerHTML="",window.QrCreator?.render){window.QrCreator.render({text:P(t),radius:.08,ecLevel:"M",fill:"#114b9a",background:"#ffffff",size:s},e);return}e.textContent=P(t)}}function C(e,t,s){const d=document.querySelector("#admin-modal-root")||document.body,p=document.createElement("div");p.className="category-editor-modal hidden",d.appendChild(p);const u=`
      <div class="category-editor-backdrop" data-modal-close></div>
      <section class="category-editor-panel presensi-modal-panel" role="dialog" aria-modal="true" aria-labelledby="presensi-modal-title">
        <header class="category-editor-head">
          <div>
            <p class="eyebrow">Presensi</p>
            <h2 id="presensi-modal-title">${i(e)}</h2>
          </div>
          <button type="button" class="category-editor-close" data-modal-close aria-label="Tutup modal">${D}</button>
        </header>
        <div class="presensi-modal-content">${t}</div>
      </section>
    `,h=window.GenBIUI?.createModalController?.(p,{closeSelector:"[data-modal-close]",panelSelector:'[role="dialog"]',onClose:()=>p.remove()});h?h.open({content:u}):(p.innerHTML=u,p.classList.remove("hidden"),document.body.classList.add("modal-lock"),p.addEventListener("click",L=>{L.target.closest("[data-modal-close]")&&(p.remove(),document.body.classList.remove("modal-lock"))})),window.setTimeout(()=>s?.(p),30)}function le(e,t="QR Presensi"){C(t,`
      <div class="presensi-qr-modal">
        <div class="presensi-qr-box" data-modal-qr></div>
        <p>${i(P(e))}</p>
        <button type="button" class="btn btn-secondary" data-copy-modal-link="${i(e)}">Copy Link</button>
      </div>
    `,s=>{X(s.querySelector("[data-modal-qr]"),e,240),s.querySelector("[data-copy-modal-link]")?.addEventListener("click",()=>W(e))})}function R(e={}){const t=String(e.url||e.photo_url||"");if(!t)return;const s=String(e.name||e.member_name||"");C("Bukti Foto",`
      <figure class="presensi-photo-modal">
        <img class="presensi-proof-photo" src="${i(t)}" alt="Bukti presensi ${i(s)}">
        ${s?`<figcaption>${i(s)}</figcaption>`:""}
      </figure>
    `)}function ce(e={}){const t=Number(e.event_id||e.presensi_event_id||0),s=Number(e.team_id||0),d=String(e.member_name||e.name||""),p=Array.isArray(e.roles)?e.roles.map(u=>String(u||"").trim()).filter(Boolean):[];if(!t||!s||!p.length){b.showToast?.("Data approve manual tidak lengkap.");return}C("Approve Manual",`
      <form class="category-editor-form" data-presensi-manual-approve-form>
        <p class="config-hint">Buat presensi approved untuk ${i(d)} tanpa menunggu form publik.</p>
        <label class="config-field">
          <span>Role</span>
          <select class="config-input js-admin-custom-select" data-presensi-manual-role required>
            <option value="">Pilih role</option>
            ${p.map(u=>`<option value="${i(u)}">${i(u)}</option>`).join("")}
          </select>
        </label>
        <p class="config-hint" data-presensi-manual-status role="status"></p>
        <div class="category-editor-actions">
          <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
          <button type="submit" class="btn btn-primary">${H} Approve</button>
        </div>
      </form>
    `,u=>{window.GenBIUI?.enhanceProjectSelects?.(u);const h=u.querySelector("[data-presensi-manual-approve-form]"),L=u.querySelector("[data-presensi-manual-role]"),E=u.querySelector("[data-presensi-manual-status]");h?.addEventListener("submit",async k=>{k.preventDefault();const N=String(L?.value||"").trim();if(!N){E&&(E.textContent="Pilih role terlebih dahulu.");return}try{E&&(E.textContent="Memproses approve manual..."),await q($("admin.presensiMemberApprove",{eventId:t,teamId:s}),{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({role:N})}),b.showToast?.("Presensi manual disetujui."),window.setTimeout(()=>window.location.reload(),500)}catch(A){E&&(E.textContent=A.message||"Gagal approve manual."),b.showToast?.(A.message||"Gagal approve manual.")}})})}function de(){document.querySelectorAll("[data-copy-link]").forEach(e=>{e.addEventListener("click",()=>W(e.dataset.copyLink||""))}),document.querySelectorAll("[data-show-qr]").forEach(e=>{e.addEventListener("click",()=>le(e.dataset.showQr||"",e.dataset.qrTitle||"QR Presensi"))}),document.querySelectorAll("[data-delete-presensi]").forEach(e=>{e.addEventListener("click",async()=>{const t=Number(e.dataset.deletePresensi||0);!t||!await b.showConfirm?.({title:"Hapus event presensi?",message:"Event akan dihapus secara soft delete. Data tidak akan tampil di admin maupun link publik.",confirmText:"Hapus",danger:!0})||(await q($("admin.presensiDelete",{id:t}),{method:"POST"}),b.showToast?.("Event presensi dihapus."),window.setTimeout(()=>window.location.reload(),500))})})}function V(e,t=[]){try{const s=document.querySelector(e)?.value||"",d=JSON.parse(s);return Array.isArray(d)?d:t}catch{return t}}function x(e={}){return{id:Number(e.id||e.team_id||0),name:String(e.name||e.member_name||""),role:String(e.role||e.designation||e.member_role||""),divisionId:Number(e.division_id||e.divisi_id||0),division:String(e.division||""),campus:String(e.campus||e.commission||""),year:String(e.year||e.tahun||""),photo:String(e.photo||"")}}function pe(e={}){return!`${e.campus||""} ${e.commission||""} ${e.komsat||""}`.toLowerCase().includes("alumni")}function ue(e={}){if(typeof e=="string")return{name:e.trim().slice(0,120),score:0};const t=String(e.name||e.label||e.role||"").trim().slice(0,120),s=Math.max(0,Math.min(1e5,Number.parseInt(e.score??e.points??e.skor??0,10)||0));return{name:t,score:s}}function Y(e){return String(e?.name||"").trim().toLowerCase()}function me(){const e=document.querySelector("#presensi-editor-form");if(!e)return;let t=V("#presensi-roles-json").map(ue).filter(a=>a.name),s=V("#presensi-members-json").map(x).filter(a=>a.id>0),d=-1;const p=e.querySelector("#presensi-role-input"),u=e.querySelector("#presensi-role-score"),h=e.querySelector("#presensi-role-list"),L=e.querySelector("#presensi-member-search"),E=e.querySelector("#presensi-member-modal-open"),k=e.querySelector("#presensi-member-suggestions"),N=e.querySelector("#presensi-member-list"),A=e.querySelector("#presensi-form-status"),M=(a,n=!1)=>{A&&(A.textContent=a,A.classList.toggle("text-red-700",n),A.classList.toggle("text-green-700",!n&&a!==""))},T=()=>{const a=e.querySelector("#presensi-roles-json"),n=e.querySelector("#presensi-members-json");a&&(a.value=JSON.stringify(t)),n&&(n.value=JSON.stringify(s))},j=()=>{if(!h)return;h.innerHTML=t.length?`
          <div class="admin-data-table-wrap presensi-role-table-wrap">
            <table class="admin-table admin-data-table presensi-role-table">
              <colgroup>
                <col class="presensi-role-col-name">
                <col class="presensi-role-col-score">
                <col class="presensi-role-col-actions">
              </colgroup>
              <thead>
                <tr>
                  <th>Role</th>
                  <th>Skor</th>
                  <th class="presensi-role-actions-cell">Aksi</th>
                </tr>
              </thead>
              <tbody>
                ${t.map((n,r)=>`
                  <tr>
                    <td>
                      <input class="config-input presensi-role-edit-input" data-role-name="${r}" value="${i(n.name)}" maxlength="120" ${d===r?"":"disabled"}>
                    </td>
                    <td>
                      <input class="config-input presensi-role-score-edit-input" data-role-score="${r}" type="number" min="0" max="100000" step="1" value="${Number(n.score||0)}" ${d===r?"":"disabled"}>
                    </td>
                    <td class="presensi-role-actions-cell">
                      ${d===r?`<button type="button" class="btn btn-primary btn-sm presensi-icon-btn" data-save-role="${r}" aria-label="Simpan role ${i(n.name)}">${H}</button>`:`<button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-edit-role="${r}" aria-label="Edit role ${i(n.name)}">${oe}</button>`}
                      <button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-remove-role="${r}" aria-label="Hapus role ${i(n.name)}">
                        ${D}
                      </button>
                    </td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>
        `:'<p class="config-hint">Tambahkan minimal satu role untuk event ini.</p>',h.querySelectorAll("[data-edit-role]").forEach(n=>{n.addEventListener("click",()=>{d=Number(n.dataset.editRole||-1),j(),h.querySelector(`[data-role-name="${d}"]`)?.focus()})});const a=n=>{const r=h.querySelector(`[data-role-name="${n}"]`),o=h.querySelector(`[data-role-score="${n}"]`),l=String(r?.value||"").trim().slice(0,120),m=Math.max(0,Math.min(1e5,Number.parseInt(o?.value||"0",10)||0));if(!l){M("Nama role tidak boleh kosong.",!0),r?.focus();return}if(t.some((f,O)=>O!==n&&Y(f)===l.toLowerCase())){M("Nama role sudah ada.",!0),r?.focus();return}t[n]={name:l,score:m},d=-1,T(),M(""),j()};h.querySelectorAll("[data-save-role]").forEach(n=>{n.addEventListener("click",()=>{a(Number(n.dataset.saveRole||-1))})}),h.querySelectorAll("[data-role-name], [data-role-score]").forEach(n=>{n.addEventListener("keydown",r=>{if(r.key!=="Enter")return;r.preventDefault();const o=Number(n.dataset.roleName??n.dataset.roleScore??-1);a(o)})}),h.querySelectorAll("[data-remove-role]").forEach(n=>{n.addEventListener("click",()=>{const r=Number(n.dataset.removeRole||-1);t=t.filter((o,l)=>l!==r),d===r&&(d=-1),d>r&&(d-=1),T(),j()})})},B=()=>{N&&(N.innerHTML=s.length?`
          <div class="admin-data-table-wrap presensi-member-table-wrap">
            <table class="admin-table admin-data-table presensi-member-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Divisi</th>
                  <th>Kampus</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                ${s.map(a=>`
                  <tr>
                    <td><strong>${i(a.name)}</strong></td>
                    <td>${i(a.division||"-")}</td>
                    <td>${i(a.campus||"-")}</td>
                    <td class="text-right">
                      <button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-remove-member="${a.id}" aria-label="Hapus ${i(a.name)}">
                        ${D}
                      </button>
                    </td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>
        `:'<p class="config-hint">Pilih anggota dari dropdown. Input manual tidak disimpan.</p>',N.querySelectorAll("[data-remove-member]").forEach(a=>{a.addEventListener("click",()=>{const n=Number(a.dataset.removeMember||0);s=s.filter(r=>r.id!==n),T(),B()})}))},G=()=>{const a=String(p?.value||"").trim().slice(0,120),n=Math.max(0,Math.min(1e5,Number.parseInt(u?.value||"0",10)||0));a&&(t.some(r=>Y(r)===a.toLowerCase())||t.push({name:a,score:n}),p.value="",u&&(u.value=""),T(),j())},be=a=>{const n=x(a);!n.id||s.some(r=>r.id===n.id)||(s.push(n),L&&(L.value=""),k?.classList.add("hidden"),T(),B())};let te=0;const ve=()=>{window.clearTimeout(te);const a=String(L?.value||"").trim();if(!k||a.length<2){k?.classList.add("hidden");return}te=window.setTimeout(async()=>{try{const n=I.getTeamMemberOptions?await I.getTeamMemberOptions({q:a,limit:12,active_only:1}):await q(_.buildEndpoint($("admin.teamMemberOptions"),{q:a,limit:12,type:"member",active_only:1})),r=Array.isArray(n.data)?n.data:[];k.innerHTML=r.length?r.map(o=>{const l=x(o);return`<button type="button" data-member='${i(JSON.stringify(l))}'><strong>${i(l.name)}</strong><span>${i([l.role,l.division,l.campus,l.year].filter(Boolean).join(" - "))}</span></button>`}).join(""):"<p>Tidak ada anggota ditemukan.</p>",k.classList.remove("hidden"),k.querySelectorAll("[data-member]").forEach(o=>{o.addEventListener("click",()=>{try{be(JSON.parse(o.dataset.member||"{}"))}catch{}})})}catch{k.innerHTML="<p>Gagal memuat anggota.</p>",k.classList.remove("hidden")}},220)},fe=async(a={})=>{const n=I.getTeamMemberOptions?await I.getTeamMemberOptions({limit:500,active_only:1,...a}):await q(_.buildEndpoint($("admin.teamMemberOptions"),{limit:500,type:"member",active_only:1,...a}));return Array.isArray(n.data)?n.data.map(x).filter(r=>r.id>0):[]},ge=(a,n)=>{if(a<=7)return Array.from({length:a},(o,l)=>l+1);const r=new Set([1,a,n,n-1,n+1]);return n<=3&&(r.add(2),r.add(3),r.add(4)),n>=a-2&&(r.add(a-1),r.add(a-2),r.add(a-3)),Array.from(r).filter(o=>o>=1&&o<=a).sort((o,l)=>o-l).reduce((o,l,m,f)=>(m>0&&l-f[m-1]>1&&o.push("gap"),o.push(l),o),[])},ye=async()=>{try{const a=await q($("admin.teamMemberOptions"));return Array.isArray(a.data?.divisions)?a.data.divisions:[]}catch{return[]}},ke=async()=>{const n=(await ye()).map(r=>`<option value="${Number(r.id||0)}">${i(r.nama||r.name||"")}</option>`).join("");C("Pilih Anggota Event",`
        <div class="presensi-member-picker">
          <div class="presensi-picker-toolbar">
            <label class="config-field">
              <span>Filter Divisi</span>
              <select class="config-input js-admin-custom-select" data-member-picker-division>
                <option value="">Semua Divisi</option>
                ${n}
              </select>
            </label>
            <label class="config-field">
              <span>Search Nama</span>
              <input class="config-input" data-member-picker-search placeholder="Cari nama anggota..." autocomplete="off">
            </label>
            <label class="presensi-check-all">
              <input type="checkbox" data-member-picker-check-all>
              <span>Check all hasil filter</span>
            </label>
          </div>
          <div class="presensi-picker-list" data-member-picker-list>
            <p class="config-hint">Memuat anggota...</p>
          </div>
          <div class="presensi-picker-pagination" data-member-picker-pagination></div>
          <div class="presensi-picker-floating">
            <span data-member-picker-count>0 anggota dipilih</span>
            <button type="button" class="btn btn-primary" data-member-picker-apply>${H} Terapkan</button>
          </div>
        </div>
      `,r=>{r.classList.add("presensi-member-picker-modal");const o=r.querySelector("[data-member-picker-division]"),l=r.querySelector("[data-member-picker-search]"),m=r.querySelector("[data-member-picker-check-all]"),f=r.querySelector("[data-member-picker-list]"),O=r.querySelector("[data-member-picker-pagination]"),ae=r.querySelector("[data-member-picker-count]"),we=r.querySelector("[data-member-picker-apply]"),y=new Map(s.filter(pe).map(v=>[v.id,v]));let g=[],ne=0,w=1;const J=10;window.GenBIUI?.enhanceProjectSelects?.(r);const U=()=>{ae&&(ae.textContent=`${y.size} anggota dipilih`)},Q=()=>{if(!f)return;const v=Math.max(1,Math.ceil(g.length/J));w=Math.max(1,Math.min(w,v));const S=(w-1)*J,z=g.slice(S,S+J);f.innerHTML=z.length?z.map(c=>`
              <label class="presensi-picker-row">
                <input type="checkbox" data-member-picker-check="${c.id}" ${y.has(c.id)?"checked":""}>
                <span>
                  <strong>${i(c.name)}</strong>
                  <small>${i([c.role,c.division,c.campus,c.year].filter(Boolean).join(" - "))}</small>
                </span>
              </label>
            `).join(""):'<p class="config-hint">Tidak ada anggota sesuai filter.</p>',O&&(O.innerHTML=g.length?`
                <span class="presensi-picker-page-info">Menampilkan ${S+1}-${Math.min(S+z.length,g.length)} dari ${g.length} anggota</span>
                <div class="admin-pagination">
                  <button class="pager-button" type="button" data-member-page="${Math.max(1,w-1)}" ${w===1?"disabled":""}>Sebelumnya</button>
                  ${ge(v,w).map(c=>c==="gap"?'<span class="pager-button presensi-page-gap" aria-hidden="true">...</span>':`<button class="pager-button ${c===w?"is-active":""}" type="button" data-member-page="${c}">${c}</button>`).join("")}
                  <button class="pager-button" type="button" data-member-page="${Math.min(v,w+1)}" ${w===v?"disabled":""}>Berikutnya</button>
                </div>
              `:"",O.querySelectorAll("[data-member-page]").forEach(c=>{c.addEventListener("click",()=>{w=Number(c.dataset.memberPage||1),Q()})})),f.querySelectorAll("[data-member-picker-check]").forEach(c=>{c.addEventListener("change",()=>{const K=Number(c.dataset.memberPickerCheck||0),re=g.find(Z=>Z.id===K);re&&(c.checked?y.set(K,re):y.delete(K),m&&(m.checked=g.length>0&&g.every(Z=>y.has(Z.id))),U())})}),m&&(m.checked=g.length>0&&g.every(c=>y.has(c.id))),U()},F=()=>{window.clearTimeout(ne),ne=window.setTimeout(async()=>{f&&(f.innerHTML='<p class="config-hint">Memuat anggota...</p>');const v={q:String(l?.value||"").trim(),division_id:String(o?.value||"")};g=await fe(v),w=1,g.forEach(S=>{y.has(S.id)&&y.set(S.id,{...y.get(S.id),...S})}),Q()},180)};o?.addEventListener("change",F),l?.addEventListener("input",F),m?.addEventListener("change",()=>{g.forEach(v=>{m.checked?y.set(v.id,v):y.delete(v.id)}),Q()}),we?.addEventListener("click",()=>{s=Array.from(y.values()).sort((v,S)=>v.name.localeCompare(S.name)),T(),B(),r.querySelector("[data-modal-close]")?.click()}),U(),F()})};e.querySelector("#presensi-role-add")?.addEventListener("click",G),p?.addEventListener("keydown",a=>{a.key==="Enter"&&(a.preventDefault(),G())}),u?.addEventListener("keydown",a=>{a.key==="Enter"&&(a.preventDefault(),G())}),L?.addEventListener("input",ve),E?.addEventListener("click",()=>{ke().catch(()=>b.showToast?.("Gagal membuka daftar anggota."))}),document.addEventListener("click",a=>{a.target.closest("[data-presensi-member-picker]")||k?.classList.add("hidden")}),e.addEventListener("submit",async a=>{a.preventDefault(),M("");const n={event_name:e.querySelector("#presensi-event-name")?.value?.trim()||"",location:e.querySelector("#presensi-location")?.value?.trim()||"",status:e.querySelector("#presensi-status")?.value||"open",roles:t,member_ids:s.map(m=>m.id)};if(!n.event_name||!n.location||!t.length||!s.length){M("Lengkapi nama event, lokasi, minimal satu role, dan minimal satu anggota.",!0);return}const r=e.dataset.edit==="1",o=Number(e.dataset.itemId||0),l=r?$("admin.presensiUpdate",{id:o}):$("admin.presensiStore");try{const m=await q(l,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)}),f=Number(m.data?.id||m.data?.event?.id||o);M("Presensi tersimpan."),b.showToast?.("Presensi tersimpan."),f&&window.setTimeout(()=>{window.location.href=$("admin.presensiDetail",{id:f})||`${ie("presensi-detail")}?id=${f}`},500)}catch(m){M(m.message||"Gagal menyimpan presensi.",!0)}}),j(),B(),T()}function he(){document.querySelectorAll("[data-presensi-qr]").forEach(e=>X(e,e.dataset.presensiQr||"",220)),document.querySelectorAll("[data-presensi-photo]").forEach(e=>{e.addEventListener("click",()=>{try{R(JSON.parse(e.dataset.presensiPhoto||"{}"))}catch{R({url:e.dataset.presensiPhoto||""})}})}),document.querySelectorAll("[data-presensi-detail]").forEach(e=>{e.addEventListener("click",()=>{let t={};try{t=JSON.parse(e.dataset.presensiDetail||"{}")}catch{t={}}C("Detail Presensi",`
          <div class="presensi-detail-grid">
            <div>
              <p class="eyebrow">Nama</p>
              <strong>${i(t.member_name||"")}</strong>
            </div>
            <div>
              <p class="eyebrow">Role</p>
              <strong>${i(t.role||"")}</strong>
            </div>
            <div>
              <p class="eyebrow">Skor</p>
              <strong>${Number(t.role_score||0)} poin</strong>
            </div>
            <div>
              <p class="eyebrow">Status</p>
              <strong>${i(t.status||"pending")}</strong>
            </div>
            <div>
              <p class="eyebrow">Waktu</p>
              <strong>${i(t.created_at_label||t.created_at||"")}</strong>
            </div>
          </div>
          ${t.photo_url?'<button type="button" class="btn btn-secondary mt-5" data-modal-presensi-photo>Lihat Foto</button>':""}
        `,s=>{s.querySelector("[data-modal-presensi-photo]")?.addEventListener("click",()=>{R({url:t.photo_url||"",name:t.member_name||""})})})})}),document.querySelectorAll("[data-presensi-manual-approve]").forEach(e=>{e.addEventListener("click",()=>{try{ce(JSON.parse(e.dataset.presensiManualApprove||"{}"))}catch{b.showToast?.("Data approve manual tidak valid.")}})}),document.querySelectorAll("[data-approve-presensi]").forEach(e=>{e.addEventListener("click",async()=>{const t=Number(e.dataset.approvePresensi||0);!t||!await b.showConfirm?.({title:"Approve presensi?",message:"Status kehadiran akan diubah menjadi approved.",confirmText:"Approve"})||(await q($("admin.presensiApprove",{id:t}),{method:"POST"}),b.showToast?.("Presensi disetujui."),window.setTimeout(()=>window.location.reload(),500))})}),document.querySelectorAll("[data-cancel-presensi]").forEach(e=>{e.addEventListener("click",async()=>{const t=Number(e.dataset.cancelPresensi||0);!t||!await b.showConfirm?.({title:"Batalkan presensi?",message:"Data kehadiran anggota ini akan dihapus dari event sehingga bisa presensi ulang atau di-approve lagi.",confirmText:"Batalkan",danger:!0})||(await q($("admin.presensiCancel",{id:t}),{method:"POST"}),b.showToast?.("Presensi dibatalkan."),window.setTimeout(()=>window.location.reload(),500))})})}function ee(){b.renderAdminShell?.("presensi"),de(),me(),he()}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",ee):ee()})();
