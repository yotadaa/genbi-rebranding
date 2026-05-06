const test = require('node:test');
const assert = require('node:assert/strict');

const Core = require('../public/assets/js/api-core.js');
const UI = require('../public/assets/js/lib/ui.js');

test('normalizeTeamMember maps backend-shaped team member into frontend shape', () => {
  // Backend-shaped item (from tbl_team_member via TeamMember::mapRow)
  const backend = { id: 37, name: 'Ilham Jaya Kusuma', role: 'Ketua Umum', division: 'Badan Pengurus Inti', campus: 'UIN STS Jambi', commission: 'Badan Pengurus Inti', year: '2025', status: 'Pengurus', bio: 'Mengawal arah kerja organisasi.', photo: 'https://genbijambi.com/public/uploads/team-member-37.jpg' };
  const result = Core.normalizeTeamMember(backend);
  assert.strictEqual(result.id, 37);
  assert.strictEqual(result.name, 'Ilham Jaya Kusuma');
  assert.strictEqual(result.role, 'Ketua Umum');
  assert.strictEqual(result.division, 'Badan Pengurus Inti');
  assert.strictEqual(result.campus, 'UIN STS Jambi');
  assert.strictEqual(result.photo, 'https://genbijambi.com/public/uploads/team-member-37.jpg');

  // Fallback for empty item
  const empty = Core.normalizeTeamMember({});
  assert.strictEqual(empty.name, '');
  assert.strictEqual(empty.division, 'Umum');
  assert.strictEqual(empty.year, '2025');

  // normalizeTeamList handles { data: [...] } wrapper
  const list = Core.normalizeTeamList({ data: [backend] });
  assert.strictEqual(list.length, 1);
  assert.strictEqual(list[0].name, 'Ilham Jaya Kusuma');
});

test('canonicalNewsUrl returns absolute canonical slug URL', () => {
  // Backend-shaped item
  assert.strictEqual(
    Core.canonicalNewsUrl({ slug: 'talkshow-siginjai-fest', news_id: 5 }),
    'https://genbijambi.com/news/talkshow-siginjai-fest'
  );
  // Frontend-shaped item (already normalized)
  assert.strictEqual(
    Core.canonicalNewsUrl({ slug: 'genbi-peka-2025-3', id: 3 }),
    'https://genbijambi.com/news/genbi-peka-2025-3'
  );
  // Fallback when no slug - generates from title + id
  assert.strictEqual(
    Core.canonicalNewsUrl({ news_title: 'Hello World', news_id: 99 }),
    'https://genbijambi.com/news/hello-world-99'
  );
  assert.strictEqual(
    Core.canonicalNewsUrl({ slug: 'local-preview' }, { protocol: 'http:', host: '127.0.0.1:8000' }),
    'http://127.0.0.1:8000/news/local-preview'
  );
});

test('buildEndpoint appends only meaningful query parameters', () => {
  assert.equal(Core.buildEndpoint('/news', { q: 'siginjai', category: 'BI', empty: '', page: null }), '/news?q=siginjai&category=BI');
});

test('canRequestBackend allows only http and https protocols', () => {
  assert.equal(Core.canRequestBackend({ protocol: 'https:' }), true);
  assert.equal(Core.canRequestBackend({ protocol: 'http:' }), true);
  assert.equal(Core.canRequestBackend({ protocol: 'file:' }), false);
});

test('pageUrl returns clean public URLs except for direct file preview', () => {
  assert.equal(Core.pageUrl('news', { protocol: 'https:' }), '/news');
  assert.equal(Core.pageUrl('contact', { protocol: 'http:', hostname: 'genbijambi.com' }), '/contact');
  assert.equal(Core.pageUrl('news', { protocol: 'file:' }), 'news.html');
  assert.equal(Core.pageUrl('news', { protocol: 'http:', hostname: '127.0.0.1', port: '5173' }), '/news');
  assert.equal(Core.pageUrl('about', { protocol: 'http:', hostname: 'genbi.local' }), '/about');
});

test('newsDetailUrl returns canonical slug URL except for static preview', () => {
  const item = { id: 94, title: 'GenBI Jambi Hadiri Diseminasi Kajian UMKM KCBN Muara Jambi' };

  assert.equal(Core.newsDetailUrl(item, { protocol: 'https:' }), '/news/genbi-jambi-hadiri-diseminasi-kajian-umkm-kcbn-muara-jambi-94');
  assert.equal(Core.newsDetailUrl(item, { protocol: 'file:' }), 'news-detail.html?slug=genbi-jambi-hadiri-diseminasi-kajian-umkm-kcbn-muara-jambi-94&id=94');
  assert.equal(Core.newsDetailUrl(item, { protocol: 'http:', hostname: 'localhost', port: '5173' }), '/news/genbi-jambi-hadiri-diseminasi-kajian-umkm-kcbn-muara-jambi-94');
});

test('adminUrl removes extensions and returns professional admin paths', () => {
  assert.equal(Core.adminUrl('dashboard.html', { protocol: 'https:' }), '/admin/dashboard');
  assert.equal(Core.adminUrl('news-edit.php', { protocol: 'http:' }), '/admin/news-edit');
  assert.equal(Core.adminUrl('comment', { protocol: 'file:' }), 'admin/comment.html');
  assert.equal(Core.adminUrl('comment', { protocol: 'http:', hostname: '127.0.0.1', port: '5173' }), '/admin/comment');
});

test('resolveStaticRoute maps clean routes to prototype HTML files', () => {
  assert.equal(Core.resolveStaticRoute('/'), '/index.html');
  assert.equal(Core.resolveStaticRoute('/news'), '/news.html');
  assert.equal(Core.resolveStaticRoute('/news/a-first-news-1'), '/news-detail.html');
  assert.equal(Core.resolveStaticRoute('/admin'), '/admin/dashboard.html');
  assert.equal(Core.resolveStaticRoute('/admin/login'), '/admin/login.html');
  assert.equal(Core.resolveStaticRoute('/admin/comment'), '/admin/comment.html');
  assert.equal(Core.resolveStaticRoute('/assets/js/app.js'), null);
});

test('normalizeNews maps backend-shaped news into frontend shape', () => {
  const item = Core.normalizeNews({
    news_id: 100,
    news_title: 'Talkshow Siginjai Fest 2026 Dorong Generasi Muda Berkarya',
    news_content_short: 'Ringkasan berita',
    news_content: 'Paragraf satu.\n\nParagraf dua.',
    news_date: '2026-04-30',
    photo: '/uploads/news.jpg',
    category_name: 'BANK INDONESIA',
    contributor_pewarta: 'Pewarta',
    contributor_editor: 'Editor',
  });

  assert.equal(item.id, 100);
  assert.equal(item.slug, 'talkshow-siginjai-fest-2026-dorong-generasi-muda-berkarya-100');
  assert.equal(item.title, 'Talkshow Siginjai Fest 2026 Dorong Generasi Muda Berkarya');
  assert.equal(item.date, 'Kamis, 30 April 2026');
  assert.equal(item.excerpt, 'Ringkasan berita');
  assert.deepEqual(item.body, ['Paragraf satu.', 'Paragraf dua.']);
  assert.equal(item.author, 'Pewarta');
  assert.equal(item.editor, 'Editor');
});

test('findNewsByIdOrSlug finds current static id and generated slug', () => {
  const list = Core.normalizeNewsList([
    { id: 1, title: 'A First News' },
    { id: 2, title: 'Second News' },
  ]);

  assert.equal(Core.findNewsByIdOrSlug(list, '2').title, 'Second News');
  assert.equal(Core.findNewsByIdOrSlug(list, 'a-first-news-1').id, 1);
});

test('normalizeApprovedComments filters pending comments from public output', () => {
  const comments = Core.normalizeApprovedComments([
    { id: 1, name: 'Rina', status: 'Approved', comment: 'Tampil' },
    { id: 2, name: 'Dimas', status: 'Pending', comment: 'Jangan tampil' },
    { id: 3, name: 'Aulia', status: 'Disetujui', comment: 'Tampil juga' },
  ]);

  assert.deepEqual(comments.map((comment) => comment.id), [1, 3]);
  assert.deepEqual(comments.map((comment) => comment.text), ['Tampil', 'Tampil juga']);
});

test('createCommentPayload trims public comment submission fields', () => {
  assert.deepEqual(Core.createCommentPayload({ name: ' Rina ', email: ' rina@example.com ', comment: ' Halo ' }), {
    name: 'Rina',
    email: 'rina@example.com',
    comment: 'Halo',
  });
});

test('normalizePrestasi maps backend-shaped prestasi into frontend shape', () => {
  const item = Core.normalizePrestasi({
    prestasi_id: 5,
    judul_prestasi: 'Juara KTI Nasional',
    nama_anggota: 'Amalia',
    komisariat: 'Universitas Jambi',
    kategori: 'Karya Tulis Ilmiah',
    tahun: '2026',
    deskripsi_singkat: 'Ringkasan prestasi',
  });

  assert.equal(item.id, 5);
  assert.equal(item.slug, 'juara-kti-nasional-5');
  assert.equal(item.title, 'Juara KTI Nasional');
  assert.equal(item.name, 'Amalia');
  assert.equal(item.campus, 'Universitas Jambi');
  assert.equal(item.category, 'Karya Tulis Ilmiah');
});

test('createModalController locks body, traps Escape close, and restores focus', () => {
  const listeners = new Map();
  const closeButton = {
    offsetParent: {},
    hasAttribute: () => false,
    focus() { global.document.activeElement = closeButton; },
    addEventListener(type, handler) { listeners.set(`button:${type}`, handler); },
  };
  const panel = {
    offsetParent: {},
    hasAttribute: () => false,
    setAttribute(name, value) { this[name] = value; },
    focus() { global.document.activeElement = panel; },
    querySelectorAll() { return [closeButton]; },
  };
  const modal = {
    innerHTML: '',
    classList: makeClassList(['hidden']),
    querySelector(selector) { return selector === '[role="dialog"]' ? panel : null; },
    querySelectorAll(selector) { return selector.includes('.modal-close') ? [closeButton] : []; },
    addEventListener(type, handler) { listeners.set(`modal:${type}`, handler); },
    removeEventListener(type) { listeners.delete(`modal:${type}`); },
  };
  const trigger = {
    focused: false,
    focus() { this.focused = true; global.document.activeElement = trigger; },
  };

  withDomStub(() => {
    const controller = UI.createModalController(modal);
    controller.open({ content: '<div role="dialog"><button class="modal-close">Close</button></div>', trigger });
    assert.equal(modal.classList.contains('hidden'), false);
    assert.equal(global.document.body.classList.contains('modal-lock'), true);

    global.window.listeners.keydown({ key: 'Escape', preventDefault() {} });
    assert.equal(modal.classList.contains('hidden'), true);
    assert.equal(global.document.body.classList.contains('modal-lock'), false);
    assert.equal(trigger.focused, true);
  });
});

test('createCustomSelect opens one menu at a time and closes on Escape', () => {
  withDomStub(() => {
    const first = makeSelectRoot();
    const second = makeSelectRoot();
    UI.createCustomSelect(first, { options: ['Semua', 'News'] });
    UI.createCustomSelect(second, { options: ['Semua', 'Event'] });

    first.button.listeners.click({ stopPropagation() {} });
    assert.equal(first.menu.classList.contains('hidden'), false);
    assert.equal(first.button.attributes['aria-expanded'], 'true');

    second.button.listeners.click({ stopPropagation() {} });
    assert.equal(first.menu.classList.contains('hidden'), true);
    assert.equal(second.menu.classList.contains('hidden'), false);

    UI.closeActiveSelect();
    assert.equal(second.menu.classList.contains('hidden'), true);
    assert.equal(second.button.attributes['aria-expanded'], 'false');
  });
});

test('normalizeAdminComments maps backend comments and normalizes statuses', () => {
  const comments = Core.normalizeAdminComments({ data: [
    { news_comment_id: 10, news_title: 'Artikel A', commentator_name: 'Rina', commentator_email: 'rina@example.com', comment: 'Perlu tampil', comment_status: 'Disetujui', created_at: '2026-05-06' },
    { id: 11, article: 'Artikel B', name: 'Dimas', email: 'dimas@example.com', text: 'Perlu review', status: 'Menunggu' },
    { id: 12, article: 'Artikel C', name: 'Aulia', email: 'aulia@example.com', content: 'Ditolak', status: 'Rejected' },
  ] });

  assert.deepEqual(comments.map((comment) => comment.id), [10, 11, 12]);
  assert.deepEqual(comments.map((comment) => comment.status), ['Approved', 'Pending', 'Rejected']);
  assert.equal(comments[0].article, 'Artikel A');
  assert.equal(comments[1].text, 'Perlu review');
});

test('getCommentModerationStats counts moderation states', () => {
  const stats = Core.getCommentModerationStats([
    { status: 'Pending' },
    { status: 'Approved' },
    { status: 'Approved' },
    { status: 'Rejected' },
    { status: 'Flagged' },
  ]);

  assert.deepEqual(stats, { total: 5, pending: 1, approved: 2, rejected: 1, flagged: 1 });
});

test('filterAdminComments filters by status and free text query', () => {
  const comments = Core.normalizeAdminComments([
    { id: 1, article: 'Siginjai Fest', name: 'Rina', email: 'rina@example.com', comment: 'Bagus', status: 'Approved' },
    { id: 2, article: 'Ekonomi Syariah', name: 'Dimas', email: 'dimas@example.com', comment: 'Tambah foto', status: 'Pending' },
    { id: 3, article: 'Campus Visit', name: 'Aulia', email: 'aulia@example.com', comment: 'Spam', status: 'Flagged' },
  ]);

  assert.deepEqual(Core.filterAdminComments(comments, { status: 'Pending' }).map((comment) => comment.id), [2]);
  assert.deepEqual(Core.filterAdminComments(comments, { query: 'siginjai' }).map((comment) => comment.id), [1]);
  assert.deepEqual(Core.filterAdminComments(comments, { status: 'Flagged', query: 'aulia' }).map((comment) => comment.id), [3]);
});

test('buildCommentActionEndpoint whitelists moderation actions', () => {
  assert.equal(Core.buildCommentActionEndpoint(12, 'approve'), '/admin/news-comments/12/approve');
  assert.equal(Core.buildCommentActionEndpoint('abc', 'delete'), '/admin/news-comments/abc/delete');
  assert.throws(() => Core.buildCommentActionEndpoint(12, 'publish'), /Unsupported comment action/);
});

function makeClassList(initial = []) {
  const classes = new Set(initial);
  return {
    add(value) { classes.add(value); },
    remove(value) { classes.delete(value); },
    contains(value) { return classes.has(value); },
    toggle(value, force) {
      if (force === undefined) {
        classes.has(value) ? classes.delete(value) : classes.add(value);
      } else if (force) {
        classes.add(value);
      } else {
        classes.delete(value);
      }
    },
  };
}

function withDomStub(callback) {
  const previousDocument = global.document;
  const previousWindow = global.window;
  global.document = {
    activeElement: null,
    listeners: {},
    body: { classList: makeClassList() },
    addEventListener(type, handler) { this.listeners[type] = handler; },
  };
  global.window = {
    addEventListener(type, handler) { global.window.listeners[type] = handler; },
    removeEventListener(type) { delete global.window.listeners[type]; },
    setTimeout(handler) { handler(); },
    listeners: {},
  };
  try {
    callback();
  } finally {
    global.document = previousDocument;
    global.window = previousWindow;
  }
}

function makeSelectRoot() {
  const root = {
    classList: makeClassList(),
    contains(target) { return target === root || target === this.button || target === this.menu; },
    querySelector(selector) {
      if (selector === '.select-button') return this.button;
      if (selector === '.select-menu') return this.menu;
      return null;
    },
  };
  Object.defineProperty(root, 'innerHTML', {
    set() {
      root.button = {
        attributes: {},
        listeners: {},
        focus() { global.document.activeElement = root.button; },
        setAttribute(name, value) { this.attributes[name] = value; },
        addEventListener(type, handler) { this.listeners[type] = handler; },
      };
      root.menu = {
        classList: makeClassList(['hidden']),
        querySelectorAll() { return []; },
      };
    },
  });
  return root;
}
