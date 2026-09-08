(function(){
'use strict';
const { getParam, newsDetailUrl, pageUrl, renderShell } = window.GenBIApp;
const { observeFadeUp } = window.GenBIUI;
const API = window.GenBIAPI;
const Core = window.GenBIAPICore;

renderShell('news');
observeFadeUp();

// Check if SSR markup exists - if so, only bind progressive behavior
const root = document.querySelector('#news-detail-root');
if (root?.dataset.ssr === 'true') {
  bindProgressiveBehavior();
  document.body.classList.add('page-ready');
  return;
}

// Otherwise, render the full page client-side
renderDetail();

window.addEventListener('error', showDetailError);
window.addEventListener('unhandledrejection', showDetailError);

function bindProgressiveBehavior() {
  bindShareButtons(document);
  bindCommentInteractions(document, { slug: window.location.pathname.split('/').filter(Boolean).pop() || '' });
}

async function renderDetail() {
  const root = document.querySelector('#news-detail-root');
  root.innerHTML = `<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Memuat detail berita...</div></section>`;
  const identifier = getParam('slug') || getParam('id') || document.body.dataset.routeSlug || '';
  if (!identifier) {
    root.innerHTML = `<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Berita tidak ditemukan.</div></section>`;
    return;
  }
  let item, related, commentsPayload;
  try {
    item = await API.getNewsDetail(identifier);
    related = await API.getRelatedNews(item.id, item.category);
    commentsPayload = await API.getNewsComments(item);
  } catch (err) {
    root.innerHTML = `<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Gagal memuat berita. Silakan coba lagi.</div></section>`;
    return;
  }
  document.title = `${item.title} | GenBI Provinsi Jambi`;
  root.dataset.loaded = 'true';
  root.innerHTML = `
    <section class="news-detail-hero">
      <img class="news-detail-hero-img" src="${item.image}" alt="${item.title}" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
      <div class="news-detail-hero-overlay"></div>
      <div class="news-detail-hero-content article-container fade-up in-view">
        <a data-transition href="${pageUrl('news')}" class="chip chip-light mb-7">← Kembali ke News</a>
        <div class="news-detail-meta flex flex-wrap items-center gap-3 text-xs font-semibold text-white/80">
          <span class="text-white">${item.category}</span><span>${item.date}</span><span>${item.readTime}</span>
        </div>
        <h1 class="page-title mt-5 text-amber-100">${item.title}</h1>
        <p class="lead mt-7 text-amber-50">${item.excerpt}</p>
      </div>
    </section>
    <section class="bg-cream py-10 md:py-16">
      <article class="article-container fade-up in-view">
        <div class="prose-soft news-detail-content">
          <img class="news-detail-inline-image" src="${item.image}" alt="${item.title}" onerror="this.remove()" />
          ${cleanNewsContent(item.raw && (item.raw.content || item.raw.news_content) ? (item.raw.content || item.raw.news_content) : item.body.map((paragraph) => `<p>${paragraph}</p>`).join(''))}
        </div>
        ${renderContributorBox(item)}
      </article>
    </section>
    <section class="bg-[var(--surface-soft)] py-12 md:py-16">
      <div class="article-container fade-up in-view">
        <div class="news-engagement-grid">
          <section class="share-card">
            <p class="eyebrow">Bagikan artikel</p>
            <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950">Bantu sebarkan kabar baik GenBI Jambi.</h2>
            <div class="mt-6 flex flex-wrap gap-2">
              <button class="share-button" data-share-url="https://wa.me/?text=${encodeURIComponent(item.title)}%20${encodeURIComponent(Core.canonicalNewsUrl(item))}">WhatsApp</button>
              <button class="share-button" data-share-url="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(Core.canonicalNewsUrl(item))}">Facebook</button>
              <button class="share-button" data-share-url="https://twitter.com/intent/tweet?text=${encodeURIComponent(item.title)}&url=${encodeURIComponent(Core.canonicalNewsUrl(item))}">X</button>
              <button class="share-button" data-copy data-canonical="${Core.canonicalNewsUrl(item)}">Copy Link</button>
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
            ${renderMainCommentForm(commentsPayload.policy)}
            <div class="mt-7 grid gap-3" id="comments-list">${renderCommentList(commentsPayload)}</div>
          </section>
        </div>
      </div>
    </section>
    ${hasPreservedRelated(item) ? renderRelatedSection(related) : ''}
  `;

  bindShareButtons(root);
  bindCommentInteractions(root, item, commentsPayload);
}

function renderMainCommentForm(policy = {}) {
  if (policy.comments_enabled === false) {
    return '<div class="comment-disabled-note mt-6">Komentar dinonaktifkan untuk artikel ini.</div>';
  }
  return `<form class="comment-form mt-6" id="comment-form"><input class="input-soft" name="name" placeholder="Nama" required /><input class="input-soft" name="email" type="email" placeholder="Email" required /><textarea class="input-soft" name="comment" rows="4" placeholder="Tulis komentar singkat..." required></textarea><button type="submit" class="btn btn-primary w-fit">Kirim komentar</button><p class="text-sm leading-6 text-neutral-500">Komentar akan tampil setelah disetujui admin.</p></form>`;
}

function renderCommentList(payload = {}) {
  const comments = Array.isArray(payload.data) ? payload.data : [];
  if (!comments.length) return '<div class="rounded-2xl border border-neutral-900/10 bg-white p-5 text-sm text-neutral-600">Belum ada komentar.</div>';
  return comments.map((comment) => renderCommentNode(comment, payload.policy || {})).join('');
}

function renderCommentNode(comment, policy = {}) {
  const depth = Number(comment.depth || 0);
  const maxDepth = Number(policy.max_reply_depth || 3);
  const canVote = policy.voting_enabled !== false;
  const canReply = policy.replies_enabled !== false && depth < maxDepth;
  return `<article class="comment-item comment-node comment-depth-${Math.min(depth, 6)}" data-comment-id="${comment.id}"><div class="flex items-start justify-between gap-3"><div><h3>${escapeHtml(comment.name || 'Pembaca')}</h3><p>${depth > 0 ? 'Balasan pembaca' : 'Pembaca'}</p></div><span class="comment-status">Disetujui</span></div><p class="comment-text">${escapeHtml(comment.text || '')}</p><div class="comment-meta-row">${canVote ? `<div class="comment-vote-group"><button type="button" class="comment-vote-btn" data-vote-up="${comment.id}">↑ <span data-vote-up-count>${Number(comment.upVotes || 0)}</span></button><button type="button" class="comment-vote-btn" data-vote-down="${comment.id}">↓ <span data-vote-down-count>${Number(comment.downVotes || 0)}</span></button><span class="comment-score" data-vote-score>${Number(comment.score || 0)}</span></div>` : ''}${canReply ? `<button type="button" class="comment-reply-toggle" data-reply-toggle="${comment.id}">Balas</button>` : ''}</div>${canReply ? `<form class="comment-form comment-reply-form hidden" data-reply-form="${comment.id}"><input type="hidden" name="parent_id" value="${comment.id}" /><input class="input-soft" name="name" placeholder="Nama" required /><input class="input-soft" name="email" type="email" placeholder="Email" required /><textarea class="input-soft" name="comment" rows="3" placeholder="Tulis balasan..." required></textarea><button type="submit" class="btn btn-primary w-fit">Kirim balasan</button></form>` : ''}${Array.isArray(comment.children) && comment.children.length ? `<div class="comment-children">${comment.children.map((child) => renderCommentNode(child, policy)).join('')}</div>` : ''}</article>`;
}

function bindShareButtons(scope) {
  scope.querySelectorAll('[data-share-url]').forEach((button) => {
    button.addEventListener('click', () => window.open(button.dataset.shareUrl, '_blank', 'noopener,noreferrer'));
  });
  scope.querySelector('[data-copy]')?.addEventListener('click', async (event) => {
    const canonical = event.currentTarget.dataset.canonical || location.href;
    try { await navigator.clipboard.writeText(canonical); showMiniToast('Link artikel disalin'); }
    catch { showMiniToast('Copy link disimulasikan'); }
  });
}

function bindCommentInteractions(scope, item) {
  scope.querySelector('#comment-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const formData = new FormData(form);
    try {
      await API.submitNewsComment(item, { name: formData.get('name'), email: formData.get('email'), comment: formData.get('comment') });
      form.reset();
      showMiniToast('Komentar masuk antrean moderasi');
    } catch {
      showMiniToast('Gagal mengirim komentar');
    }
  });

  scope.addEventListener('click', async (event) => {
    const replyToggle = event.target.closest('[data-reply-toggle]');
    if (replyToggle) {
      const form = scope.querySelector(`[data-reply-form="${replyToggle.dataset.replyToggle}"]`);
      if (form) form.classList.toggle('hidden');
      return;
    }
    const upButton = event.target.closest('[data-vote-up]');
    const downButton = event.target.closest('[data-vote-down]');
    const button = upButton || downButton;
    if (!button) return;
    const commentId = button.dataset.voteUp || button.dataset.voteDown;
    const value = button.dataset.voteUp ? 1 : -1;
    try {
      const response = await API.voteComment(item, commentId, value);
      const container = button.closest('.comment-node');
      if (container) {
        const data = response?.data || {};
        const upCount = container.querySelector('[data-vote-up-count]');
        const downCount = container.querySelector('[data-vote-down-count]');
        const score = container.querySelector('[data-vote-score]');
        if (upCount) upCount.textContent = String(Number(data.up || 0));
        if (downCount) downCount.textContent = String(Number(data.down || 0));
        if (score) score.textContent = String(Number(data.score || 0));
      }
    } catch {
      showMiniToast('Gagal menyimpan vote');
    }
  });

  scope.querySelectorAll('[data-reply-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const replyForm = event.currentTarget;
      const formData = new FormData(replyForm);
      try {
        await API.submitNewsComment(item, { parentId: formData.get('parent_id'), name: formData.get('name'), email: formData.get('email'), comment: formData.get('comment') });
        replyForm.reset();
        replyForm.classList.add('hidden');
        showMiniToast('Balasan masuk antrean moderasi');
      } catch {
        showMiniToast('Gagal mengirim balasan');
      }
    });
  });
}

function escapeHtml(value) {
  return String(value || '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
}

function hasPreservedRelated(item) {
  return String(item?.related || item?.raw?.related || '').trim() !== '';
}

function renderRelatedSection(related) {
  if (!related.length) return '';
  return `
    <section class="bg-[var(--surface-soft)] pb-14 md:pb-20">
      <div class="article-container fade-up in-view">
        <p class="eyebrow">Artikel terkait</p>
        <div class="mt-6 related-news-list">
          ${related.map((post) => `
            <a data-transition href="${newsDetailUrl(post)}" class="related-news-card">
              <figure><img src="${post.image}" alt="${post.title}" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" /></figure>
              <div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
                  <span class="text-blue-800">${post.category}</span><span>${post.date}</span>
                </div>
                <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">${post.title}</h3>
                <p class="mt-2 text-sm leading-6 text-neutral-600">${post.excerpt}</p>
              </div>
            </a>
          `).join('')}
        </div>
      </div>
    </section>
  `;
}

function cleanNewsContent(content) {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = String(content || '');
  wrapper.querySelectorAll('[style]').forEach((node) => node.removeAttribute('style'));
  return wrapper.innerHTML;
}

function renderContributorBox(item) {
  const pewarta = String(item.raw?.contributor_pewarta || item.author || '').trim();
  const editor = String(item.raw?.contributor_editor || item.editor || '').trim();

  if (!pewarta && !editor) return '';

  return `
    <div class="news-detail-contributors mt-10 rounded-[1.5rem] border border-neutral-900/10 bg-white/80 p-5 text-sm leading-7 text-neutral-700">
      ${pewarta ? `<p><strong>Pewarta:</strong> ${pewarta}</p>` : ''}
      ${editor ? `<p><strong>Editor:</strong> ${editor}</p>` : ''}
    </div>
  `;
}

function showDetailError() {
  const root = document.querySelector('#news-detail-root');
  if (!root || root.dataset.loaded === 'true') return;
  root.innerHTML = `<section class="bg-stone py-16"><div class="article-container text-sm text-neutral-600">Gagal memuat berita. Silakan muat ulang halaman.</div></section>`;
  document.body.classList.add('page-ready');
}

function showMiniToast(message) {
  let toast = document.querySelector('#public-mini-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'public-mini-toast';
    toast.className = 'public-mini-toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('is-visible');
  window.clearTimeout(showMiniToast.timer);
  showMiniToast.timer = window.setTimeout(() => toast.classList.remove('is-visible'), 1800);
}

})();
