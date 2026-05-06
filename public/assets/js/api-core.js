(function (root, factory) {
  'use strict';

  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
    return;
  }

  root.GenBIAPICore = factory();
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  const DEFAULT_IMAGE = 'https://genbijambi.com/public/uploads/slider-1.png';

  function slugify(value = '') {
    return String(value)
      .toLowerCase()
      .normalize('NFKD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '') || 'item';
  }

  function normalizeListPayload(payload) {
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload?.items)) return payload.items;
    if (Array.isArray(payload?.results)) return payload.results;
    return [];
  }

  function makeNewsSlug(item) {
    if (item.slug) return String(item.slug);
    const title = item.news_title || item.title || 'news';
    const id = item.news_id || item.id;
    return id ? `${slugify(title)}-${id}` : slugify(title);
  }

  function normalizeNews(item = {}) {
    const id = item.news_id || item.id || item.slug || makeNewsSlug(item);
    const title = item.news_title || item.title || 'Berita GenBI Jambi';
    const body = Array.isArray(item.body)
      ? item.body
      : String(item.news_content || item.content || '')
        .split(/\n{2,}/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean);

    return {
      id,
      slug: makeNewsSlug({ ...item, id, title }),
      title,
      category: item.category_name || item.category || 'Berita GenBI',
      date: item.published_at || item.news_date || item.date || item.created_at || '',
      readTime: item.read_time || item.readTime || '4 menit baca',
      image: item.photo || item.banner || item.image || DEFAULT_IMAGE,
      excerpt: item.news_content_short || item.excerpt || item.meta_description || '',
      body: body.length ? body : [item.news_content_short || item.excerpt || 'Konten berita belum tersedia.'],
      author: item.contributor_pewarta || item.author || 'Redaksi GenBI Jambi',
      editor: item.contributor_editor || item.editor || 'Redaksi GenBI Jambi',
      related: item.related || '',
      raw: item,
    };
  }

  function normalizeNewsList(payload) {
    return normalizeListPayload(payload).map(normalizeNews);
  }

  function findNewsByIdOrSlug(items, identifier) {
    const key = String(identifier || '');
    const normalized = items.map(normalizeNews);
    return normalized.find((item) => String(item.id) === key || item.slug === key) || null;
  }

  function isApprovedComment(comment = {}) {
    const status = String(comment.status || comment.comment_status || '').toLowerCase();
    return status === 'approved' || status === 'disetujui';
  }

  function normalizeComment(comment = {}) {
    return {
      id: comment.id || comment.comment_id || comment.news_comment_id || '',
      name: comment.name || comment.commentator_name || comment.author_name || 'Pembaca',
      email: comment.email || comment.commentator_email || '',
      role: comment.role || 'Pembaca',
      status: comment.status || comment.comment_status || 'Pending',
      text: comment.text || comment.comment || comment.content || '',
      article: comment.article || comment.news_title || '',
      date: comment.date || comment.created_at || '',
      raw: comment,
    };
  }

  function normalizeCommentStatus(value = '') {
    const status = String(value || '').toLowerCase();
    if (status === 'approved' || status === 'disetujui' || status === 'approve') return 'Approved';
    if (status === 'rejected' || status === 'ditolak' || status === 'reject') return 'Rejected';
    if (status === 'flagged' || status === 'spam' || status === 'reported') return 'Flagged';
    return 'Pending';
  }

  function normalizeAdminComment(comment = {}) {
    const normalized = normalizeComment(comment);
    return {
      ...normalized,
      status: normalizeCommentStatus(normalized.status),
      article: normalized.article || comment.article_title || comment.news?.title || 'Berita GenBI Jambi',
      text: normalized.text || comment.comment_text || '',
    };
  }

  function normalizeAdminComments(payload) {
    return normalizeListPayload(payload).map(normalizeAdminComment);
  }

  function normalizeApprovedComments(payload) {
    return normalizeListPayload(payload).filter(isApprovedComment).map(normalizeComment);
  }

  function getCommentModerationStats(comments = []) {
    return comments.reduce((stats, comment) => {
      const status = normalizeCommentStatus(comment.status);
      stats.total += 1;
      if (status === 'Approved') stats.approved += 1;
      if (status === 'Pending') stats.pending += 1;
      if (status === 'Rejected') stats.rejected += 1;
      if (status === 'Flagged') stats.flagged += 1;
      return stats;
    }, { total: 0, pending: 0, approved: 0, rejected: 0, flagged: 0 });
  }

  function filterAdminComments(comments = [], filters = {}) {
    const query = String(filters.query || '').toLowerCase().trim();
    const status = normalizeCommentStatus(filters.status || 'Semua');
    return comments.filter((comment) => {
      const normalizedStatus = normalizeCommentStatus(comment.status);
      const haystack = `${comment.article || ''} ${comment.name || ''} ${comment.email || ''} ${comment.text || ''}`.toLowerCase();
      const matchesStatus = !filters.status || filters.status === 'Semua' || normalizedStatus === status;
      return matchesStatus && (!query || haystack.includes(query));
    });
  }

  function buildCommentActionEndpoint(id, action) {
    const normalizedAction = String(action || '').toLowerCase();
    const allowed = ['approve', 'reject', 'delete'];
    if (!allowed.includes(normalizedAction)) throw new Error(`Unsupported comment action: ${action}`);
    return `/admin/news-comments/${encodeURIComponent(id)}/${normalizedAction}`;
  }

  function createCommentPayload({ name, email, comment }) {
    return {
      name: String(name || '').trim(),
      email: String(email || '').trim(),
      comment: String(comment || '').trim(),
    };
  }

  function normalizePrestasi(item = {}) {
    const title = item.judul_prestasi || item.title || 'Prestasi GenBI Jambi';
    const id = item.prestasi_id || item.id || item.slug || slugify(title);
    return {
      id,
      slug: item.slug || `${slugify(title)}-${id}`,
      name: item.nama_anggota || item.name || '',
      title,
      campus: item.komisariat || item.campus || '',
      category: item.category || item.kategori || 'Prestasi',
      year: item.tahun || item.year || '',
      image: item.foto_prestasi || item.image || DEFAULT_IMAGE,
      description: item.deskripsi_singkat || item.description || '',
      detail: item.deskripsi_detail || item.detail || item.deskripsi_singkat || item.description || '',
      raw: item,
    };
  }

  function normalizePrestasiList(payload) {
    return normalizeListPayload(payload).map(normalizePrestasi);
  }

  function normalizeTeamMember(item = {}) {
    return {
      id: item.id || 0,
      name: item.name || '',
      role: item.role || item.designation || '',
      divisionId: item.division_id || item.divisionId || item.divisi_id || 0,
      division: item.division || 'Umum',
      campus: item.campus || '',
      commission: item.commission || '',
      year: item.year || '2025',
      status: item.status || 'Pengurus',
      bio: item.bio || item.detail || '',
      photo: item.photo || '',
      email: item.email || '',
      instagram: item.instagram || '',
    };
  }

  function normalizeTeamPayload(payload) {
    return {
      members: normalizeTeamList(payload),
      bpi: normalizeTeamList(payload?.bpi || []),
      filters: {
        divisions: normalizeListPayload(payload?.filters?.divisions || payload?.divisions || []),
        campuses: normalizeListPayload(payload?.filters?.campuses || payload?.campuses || []),
        years: normalizeListPayload(payload?.filters?.years || payload?.years || []).map(String),
      },
      meta: {
        page: Number(payload?.meta?.page || 1),
        perPage: Number(payload?.meta?.per_page || payload?.meta?.perPage || 0),
        total: Number(payload?.meta?.total || normalizeListPayload(payload).length),
      },
    };
  }

  function normalizeTeamList(payload) {
    return normalizeListPayload(payload).map(normalizeTeamMember);
  }

  function buildEndpoint(basePath, params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') query.set(key, value);
    });
    const search = query.toString();
    return search ? `${basePath}?${search}` : basePath;
  }

  function isStaticProtocol(locationLike) {
    const protocol = locationLike?.protocol || '';
    return protocol === 'file:';
  }

  function isStaticServer(locationLike) {
    const port = String(locationLike?.port || '');
    const hostname = String(locationLike?.hostname || '');
    return port === '5173' || hostname === 'localhost' || hostname === '127.0.0.1';
  }

  function pageUrl(page, locationLike) {
    const cleanPages = {
      home: '/',
      index: '/',
      about: '/about',
      team: '/team',
      teams: '/teams',
      prestasi: '/prestasi',
      news: '/news',
      contact: '/contact',
    };
    const staticPages = {
      home: 'index.html',
      index: 'index.html',
      about: 'about.html',
      team: 'team.html',
      teams: 'team.html',
      prestasi: 'prestasi.html',
      news: 'news.html',
      contact: 'contact.html',
    };
    return isStaticProtocol(locationLike) ? staticPages[page] || `${page}.html` : cleanPages[page] || `/${page}`;
  }

  function adminUrl(page, locationLike) {
    const normalized = String(page || '').replace(/\.html$|\.php$/g, '').replace(/^\/+/, '');
    return isStaticProtocol(locationLike) ? `admin/${normalized}.html` : `/admin/${normalized}`;
  }

  function newsDetailUrl(news, locationLike) {
    const item = normalizeNews(news || {});
    return isStaticProtocol(locationLike) ? `news-detail.html?slug=${encodeURIComponent(item.slug)}&id=${encodeURIComponent(item.id)}` : `/news/${encodeURIComponent(item.slug)}`;
  }

  function canonicalNewsUrl(news) {
    const item = normalizeNews(news || {});
    return `https://genbijambi.com/news/${encodeURIComponent(item.slug)}`;
  }

  function resolveStaticRoute(pathname = '/') {
    const cleanPath = String(pathname || '/').replace(/\/+$|^\s+|\s+$/g, '') || '/';
    const publicRoutes = {
      '/': '/index.html',
      '/about': '/about.html',
      '/team': '/team.html',
      '/teams': '/team.html',
      '/prestasi': '/prestasi.html',
      '/news': '/news.html',
      '/contact': '/contact.html',
    };
    if (publicRoutes[cleanPath]) return publicRoutes[cleanPath];
    if (/^\/news\/[^/]+$/.test(cleanPath)) return '/news-detail.html';
    if (/^\/prestasi\/submit\/[^/]+$/.test(cleanPath)) return '/prestasi-submit.html';
    if (cleanPath === '/admin') return '/admin/dashboard.html';
    if (/^\/admin\/[^/]+$/.test(cleanPath)) return `/admin/${cleanPath.split('/').pop()}.html`;
    return null;
  }

  function canRequestBackend(locationLike) {
    const protocol = locationLike?.protocol || '';
    return protocol === 'http:' || protocol === 'https:';
  }

  return {
    DEFAULT_IMAGE,
    buildCommentActionEndpoint,
    buildEndpoint,
    canRequestBackend,
    createCommentPayload,
    findNewsByIdOrSlug,
    filterAdminComments,
    getCommentModerationStats,
    isApprovedComment,
    isStaticServer,
    normalizeAdminComment,
    normalizeAdminComments,
    normalizeApprovedComments,
    normalizeComment,
    normalizeCommentStatus,
    normalizeListPayload,
    normalizeNews,
    normalizeNewsList,
    normalizePrestasi,
    normalizePrestasiList,
    normalizeTeamMember,
    normalizeTeamList,
    normalizeTeamPayload,
    adminUrl,
    canonicalNewsUrl,
    newsDetailUrl,
    pageUrl,
    resolveStaticRoute,
    slugify,
  };
});
