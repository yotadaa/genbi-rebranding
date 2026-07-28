(function(){"use strict";const{getParam:p,newsDetailUrl:$,pageUrl:k,renderShell:S}=window.GenBIApp,{observeFadeUp:C}=window.GenBIUI,c=window.GenBIAPI,l=window.GenBIAPICore;if(S("news"),C(),document.querySelector("#news-detail-root")?.dataset.ssr==="true"){L(),document.body.classList.add("page-ready");return}N(),window.addEventListener("error",v),window.addEventListener("unhandledrejection",v);function L(){g(document),w(document,{slug:window.location.pathname.split("/").filter(Boolean).pop()||""})}async function N(){const t=document.querySelector("#news-detail-root");t.innerHTML='<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Memuat detail berita...</div></section>';const a=p("slug")||p("id")||document.body.dataset.routeSlug||"";if(!a){t.innerHTML='<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Berita tidak ditemukan.</div></section>';return}let e,r,n;try{e=await c.getNewsDetail(a),r=await c.getRelatedNews(e.id,e.category),n=await c.getNewsComments(e)}catch{t.innerHTML='<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Gagal memuat berita. Silakan coba lagi.</div></section>';return}document.title=`${e.title} | GenBI Provinsi Jambi`,t.dataset.loaded="true",t.innerHTML=`
    <section class="news-detail-hero">
      <img class="news-detail-hero-img" src="${e.image}" alt="${e.title}" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
      <div class="news-detail-hero-overlay"></div>
      <div class="news-detail-hero-content article-container fade-up in-view">
        <a data-transition href="${k("news")}" class="chip chip-light mb-7">\u2190 Kembali ke News</a>
        <div class="news-detail-meta flex flex-wrap items-center gap-3 text-xs font-semibold text-white/80">
          <span class="text-white">${e.category}</span><span>${e.date}</span><span>${e.readTime}</span>
        </div>
        <h1 class="page-title mt-5 text-amber-100">${e.title}</h1>
        <p class="lead mt-7 text-amber-50">${e.excerpt}</p>
      </div>
    </section>
    <section class="bg-cream py-10 md:py-16">
      <article class="article-container fade-up in-view">
        <div class="prose-soft news-detail-content">
          <img class="news-detail-inline-image" src="${e.image}" alt="${e.title}" onerror="this.remove()" />
          ${E(e.raw&&(e.raw.content||e.raw.news_content)?e.raw.content||e.raw.news_content:e.body.map(s=>`<p>${s}</p>`).join(""))}
        </div>
        ${U(e)}
      </article>
    </section>
    <section class="bg-[var(--surface-soft)] py-12 md:py-16">
      <div class="article-container fade-up in-view">
        <div class="news-engagement-grid">
          <section class="share-card">
            <p class="eyebrow">Bagikan artikel</p>
            <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950">Bantu sebarkan kabar baik GenBI Jambi.</h2>
            <div class="mt-6 flex flex-wrap gap-2">
              <button class="share-button" data-share-url="https://wa.me/?text=${encodeURIComponent(e.title)}%20${encodeURIComponent(l.canonicalNewsUrl(e))}">WhatsApp</button>
              <button class="share-button" data-share-url="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(l.canonicalNewsUrl(e))}">Facebook</button>
              <button class="share-button" data-share-url="https://twitter.com/intent/tweet?text=${encodeURIComponent(e.title)}&url=${encodeURIComponent(l.canonicalNewsUrl(e))}">X</button>
              <button class="share-button" data-copy data-canonical="${l.canonicalNewsUrl(e)}">Copy Link</button>
            </div>
          </section>
          <section class="comment-card">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <p class="eyebrow">Komentar</p>
                <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950">Diskusi pembaca</h2>
              </div>
              <span class="blue-badge">Moderasi aktif</span>
            </div>
            ${q(n.policy)}
            <div class="mt-7 grid gap-3" id="comments-list">${B(n)}</div>
          </section>
        </div>
      </div>
    </section>
    ${T(e)?I(r):""}
  `,g(t),w(t,e,n)}function q(t={}){return t.comments_enabled===!1?'<div class="comment-disabled-note mt-6">Komentar dinonaktifkan untuk artikel ini.</div>':'<form class="comment-form mt-6" id="comment-form"><input class="input-soft" name="name" placeholder="Nama" required /><input class="input-soft" name="email" type="email" placeholder="Email" required /><textarea class="input-soft" name="comment" rows="4" placeholder="Tulis komentar singkat..." required></textarea><button type="submit" class="btn btn-primary w-fit">Kirim komentar</button><p class="text-sm leading-6 text-neutral-500">Komentar akan tampil setelah disetujui admin.</p></form>'}function B(t={}){const a=Array.isArray(t.data)?t.data:[];return a.length?a.map(e=>b(e,t.policy||{})).join(""):'<div class="rounded-2xl border border-neutral-900/10 bg-white p-5 text-sm text-neutral-600">Belum ada komentar.</div>'}function b(t,a={}){const e=Number(t.depth||0),r=Number(a.max_reply_depth||3),n=a.voting_enabled!==!1,s=a.replies_enabled!==!1&&e<r;return`<article class="comment-item comment-node comment-depth-${Math.min(e,6)}" data-comment-id="${t.id}"><div class="flex items-start justify-between gap-3"><div><h3>${f(t.name||"Pembaca")}</h3><p>${e>0?"Balasan pembaca":"Pembaca"}</p></div><span class="comment-status">Disetujui</span></div><p class="comment-text">${f(t.text||"")}</p><div class="comment-meta-row">${n?`<div class="comment-vote-group"><button type="button" class="comment-vote-btn" data-vote-up="${t.id}">\u2191 <span data-vote-up-count>${Number(t.upVotes||0)}</span></button><button type="button" class="comment-vote-btn" data-vote-down="${t.id}">\u2193 <span data-vote-down-count>${Number(t.downVotes||0)}</span></button><span class="comment-score" data-vote-score>${Number(t.score||0)}</span></div>`:""}${s?`<button type="button" class="comment-reply-toggle" data-reply-toggle="${t.id}">Balas</button>`:""}</div>${s?`<form class="comment-form comment-reply-form hidden" data-reply-form="${t.id}"><input type="hidden" name="parent_id" value="${t.id}" /><input class="input-soft" name="name" placeholder="Nama" required /><input class="input-soft" name="email" type="email" placeholder="Email" required /><textarea class="input-soft" name="comment" rows="3" placeholder="Tulis balasan..." required></textarea><button type="submit" class="btn btn-primary w-fit">Kirim balasan</button></form>`:""}${Array.isArray(t.children)&&t.children.length?`<div class="comment-children">${t.children.map(o=>b(o,a)).join("")}</div>`:""}</article>`}function g(t){t.querySelectorAll("[data-share-url]").forEach(a=>{a.addEventListener("click",()=>window.open(a.dataset.shareUrl,"_blank","noopener,noreferrer"))}),t.querySelector("[data-copy]")?.addEventListener("click",async a=>{const e=a.currentTarget.dataset.canonical||location.href;try{await navigator.clipboard.writeText(e),i("Link artikel disalin")}catch{i("Copy link disimulasikan")}})}function w(t,a){t.querySelector("#comment-form")?.addEventListener("submit",async e=>{e.preventDefault();const r=e.currentTarget,n=new FormData(r);try{await c.submitNewsComment(a,{name:n.get("name"),email:n.get("email"),comment:n.get("comment")}),r.reset(),i("Komentar masuk antrean moderasi")}catch{i("Gagal mengirim komentar")}}),t.addEventListener("click",async e=>{const r=e.target.closest("[data-reply-toggle]");if(r){const d=t.querySelector(`[data-reply-form="${r.dataset.replyToggle}"]`);d&&d.classList.toggle("hidden");return}const n=e.target.closest("[data-vote-up]"),s=e.target.closest("[data-vote-down]"),o=n||s;if(!o)return;const A=o.dataset.voteUp||o.dataset.voteDown,D=o.dataset.voteUp?1:-1;try{const d=await c.voteComment(a,A,D),m=o.closest(".comment-node");if(m){const u=d?.data||{},h=m.querySelector("[data-vote-up-count]"),y=m.querySelector("[data-vote-down-count]"),x=m.querySelector("[data-vote-score]");h&&(h.textContent=String(Number(u.up||0))),y&&(y.textContent=String(Number(u.down||0))),x&&(x.textContent=String(Number(u.score||0)))}}catch{i("Gagal menyimpan vote")}}),t.querySelectorAll("[data-reply-form]").forEach(e=>{e.addEventListener("submit",async r=>{r.preventDefault();const n=r.currentTarget,s=new FormData(n);try{await c.submitNewsComment(a,{parentId:s.get("parent_id"),name:s.get("name"),email:s.get("email"),comment:s.get("comment")}),n.reset(),n.classList.add("hidden"),i("Balasan masuk antrean moderasi")}catch{i("Gagal mengirim balasan")}})})}function f(t){return String(t||"").replace(/[&<>"']/g,a=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"})[a])}function T(t){return String(t?.related||t?.raw?.related||"").trim()!==""}function I(t){return t.length?`
    <section class="bg-[var(--surface-soft)] pb-14 md:pb-20">
      <div class="article-container fade-up in-view">
        <p class="eyebrow">Artikel terkait</p>
        <div class="mt-6 related-news-list">
          ${t.map(a=>`
            <a data-transition href="${$(a)}" class="related-news-card">
              <figure><img src="${a.image}" alt="${a.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" /></figure>
              <div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
                  <span class="text-blue-800">${a.category}</span><span>${a.date}</span>
                </div>
                <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${a.title}</h3>
                <p class="mt-2 text-sm leading-6 text-neutral-600">${a.excerpt}</p>
              </div>
            </a>
          `).join("")}
        </div>
      </div>
    </section>
  `:""}function E(t){const a=document.createElement("div");return a.innerHTML=String(t||""),a.querySelectorAll("[style]").forEach(e=>e.removeAttribute("style")),a.innerHTML}function U(t){const a=String(t.raw?.contributor_pewarta||t.author||"").trim(),e=String(t.raw?.contributor_editor||t.editor||"").trim();return!a&&!e?"":`
    <div class="news-detail-contributors mt-10 rounded-[1.5rem] border border-neutral-900/10 bg-white/80 p-5 text-sm leading-7 text-neutral-700">
      ${a?`<p><strong>Pewarta:</strong> ${a}</p>`:""}
      ${e?`<p><strong>Editor:</strong> ${e}</p>`:""}
    </div>
  `}function v(){const t=document.querySelector("#news-detail-root");!t||t.dataset.loaded==="true"||(t.innerHTML='<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Gagal memuat berita. Silakan muat ulang halaman.</div></section>',document.body.classList.add("page-ready"))}function i(t){let a=document.querySelector("#public-mini-toast");a||(a=document.createElement("div"),a.id="public-mini-toast",a.className="public-mini-toast",document.body.appendChild(a)),a.textContent=t,a.classList.add("is-visible"),window.clearTimeout(i.timer),i.timer=window.setTimeout(()=>a.classList.remove("is-visible"),1800)}})();
