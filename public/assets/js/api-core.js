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
  const ROUTES = {
    public: {
      home: { clean: '/', static: 'index.html' },
      index: { clean: '/', static: 'index.html' },
      about: { clean: '/about', static: 'about.html' },
      team: { clean: '/team', static: 'team.html' },
      teams: { clean: '/teams', static: 'team.html' },
      event: { clean: '/event', static: 'event.html' },
      prestasi: { clean: '/prestasi', static: 'prestasi.html' },
      news: { clean: '/news', static: 'news.html' },
      contact: { clean: '/contact', static: 'contact.html' },
      prestasiSubmit: { clean: '/prestasi/submit/{token}', static: 'prestasi-submit.html' },
      presensiShow: { clean: '/presensi/{token}' },
      presensiMembers: { clean: '/presensi/{token}/members' },
      newsDetail: { clean: '/news/{slug}', static: 'news-detail.html?slug={slug}&id={id}' },
      newsComments: { clean: '/news/{slug}/comments' },
      newsCommentStore: { clean: '/news/{slug}/comment' },
      newsCommentVote: { clean: '/news/{slug}/comment/{id}/vote' },
      eventDetail: { clean: '/event/{slug}' },
    },
    admin: {
      dashboard: { clean: '/admin/dashboard', static: 'admin/dashboard.html' },
      news: { clean: '/admin/news', static: 'admin/news.html' },
      newsAdd: { clean: '/admin/news-add', static: 'admin/news-add.html' },
      newsEdit: { clean: '/admin/news-edit', static: 'admin/news-edit.html' },
      newsList: { clean: '/admin/news/list' },
      newsCategories: { clean: '/admin/news/categories' },
      categories: { clean: '/admin/categories' },
      categoryStore: { clean: '/admin/categories' },
      categoryUpdate: { clean: '/admin/categories/{id}/update' },
      categoryDelete: { clean: '/admin/categories/{id}/delete' },
      newsComments: { clean: '/admin/news-comments' },
      newsCommentAction: { clean: '/admin/news-comments/{id}/{action}' },
      commentSetting: { clean: '/admin/comment-setting' },
      newsShow: { clean: '/admin/news/{id}' },
      newsStore: { clean: '/admin/news' },
      newsUpdate: { clean: '/admin/news/{id}/update' },
      newsDelete: { clean: '/admin/news/{id}/delete' },
      newsUpload: { clean: '/admin/news/upload' },
      event: { clean: '/admin/event', static: 'admin/event.html' },
      eventAdd: { clean: '/admin/event-add', static: 'admin/event-add.html' },
      eventEdit: { clean: '/admin/event-edit', static: 'admin/event-edit.html' },
      events: { clean: '/admin/events' },
      eventShow: { clean: '/admin/events/{id}' },
      eventStore: { clean: '/admin/events' },
      eventUpdate: { clean: '/admin/events/{id}/update' },
      eventDelete: { clean: '/admin/events/{id}/delete' },
      photos: { clean: '/admin/photos' },
      photoShow: { clean: '/admin/photos/{id}' },
      photoStore: { clean: '/admin/photos' },
      photoUpdate: { clean: '/admin/photos/{id}/update' },
      photoDelete: { clean: '/admin/photos/{id}/delete' },
      photoUpload: { clean: '/admin/photos/upload' },
      prestasi: { clean: '/admin/prestasi', static: 'admin/prestasi.html' },
      prestasiAdd: { clean: '/admin/prestasi-add', static: 'admin/prestasi-add.html' },
      prestasiEdit: { clean: '/admin/prestasi-edit', static: 'admin/prestasi-edit.html' },
      prestasiList: { clean: '/admin/prestasi/list' },
      prestasiShow: { clean: '/admin/prestasi/{id}' },
      prestasiStore: { clean: '/admin/prestasi' },
      prestasiUpdate: { clean: '/admin/prestasi/{id}/update' },
      prestasiDelete: { clean: '/admin/prestasi/{id}/delete' },
      prestasiUpload: { clean: '/admin/prestasi/upload' },
      prestasiTokens: { clean: '/admin/prestasi-tokens' },
      prestasiTokenRevoke: { clean: '/admin/prestasi-tokens/{id}/revoke' },
      presensi: { clean: '/admin/presensi', static: 'admin/presensi.html' },
      presensiAdd: { clean: '/admin/presensi-add', static: 'admin/presensi-add.html' },
      presensiEdit: { clean: '/admin/presensi-edit', static: 'admin/presensi-edit.html' },
      presensiList: { clean: '/admin/presensi/list' },
      presensiShow: { clean: '/admin/presensi/{id}' },
      presensiDetail: { clean: '/admin/presensi-detail?id={id}' },
      presensiStore: { clean: '/admin/presensi' },
      presensiUpdate: { clean: '/admin/presensi/{id}/update' },
      presensiDelete: { clean: '/admin/presensi/{id}/delete' },
      presensiSubmissions: { clean: '/admin/presensi/{id}/submissions' },
      presensiApprove: { clean: '/admin/presensi/submissions/{id}/approve' },
      presensiMemberApprove: { clean: '/admin/presensi/{eventId}/members/{teamId}/approve' },
      teamMembers: { clean: '/admin/team-members' },
      teamMemberOptions: { clean: '/admin/team-members/options' },
      teamMembersBulk: { clean: '/admin/team-members/bulk' },
      teamMembersUpload: { clean: '/admin/team-members/upload' },
      teamMemberShow: { clean: '/admin/team-members/{id}' },
      teamMemberUpdate: { clean: '/admin/team-members/{id}/update' },
      teamMemberDelete: { clean: '/admin/team-members/{id}/delete' },
      teamMemberHome: { clean: '/admin/team-members/{id}/home' },
      feature: { clean: '/admin/feature', static: 'admin/feature.html' },
      featureAdd: { clean: '/admin/feature-add', static: 'admin/feature-add.html' },
      featureEdit: { clean: '/admin/feature-edit', static: 'admin/feature-edit.html' },
      features: { clean: '/admin/features' },
      featureShow: { clean: '/admin/features/{id}' },
      featureStore: { clean: '/admin/features' },
      featureUpload: { clean: '/admin/features/upload' },
      featureUpdate: { clean: '/admin/features/{id}/update' },
      featureDelete: { clean: '/admin/features/{id}/delete' },
      featureImageDelete: { clean: '/admin/features/{id}/images/{imageId}/delete' },
      featureImageReorder: { clean: '/admin/features/{id}/images/reorder' },
      contactSetting: { clean: '/admin/contact-setting' },
      contactSettingUpdate: { clean: '/admin/contact-setting' },
      pageHomeUpdate: { clean: '/admin/settings/page-home' },
    },
  };

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

  function formatDisplayDate(value = '') {
    if (!value) return '';

    const text = String(value).trim();
    const mysqlDate = text.match(/^(\d{4})-(\d{2})-(\d{2})/);
    const parsed = mysqlDate
      ? new Date(Number(mysqlDate[1]), Number(mysqlDate[2]) - 1, Number(mysqlDate[3]))
      : new Date(text);
    if (Number.isNaN(parsed.getTime())) return String(value).split(' ')[0] || '';

    const weekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    return `${weekdays[parsed.getDay()]}, ${parsed.getDate()} ${months[parsed.getMonth()]} ${parsed.getFullYear()}`;
  }

  function normalizeNews(item = {}) {
    const id = item.news_id || item.id || item.slug || makeNewsSlug(item);
    const title = item.news_title || item.title || 'Berita GenBI Jambi';
    const sourceDate = item.published_at || item.news_date || item.date || item.created_at || '';
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
      date: formatDisplayDate(sourceDate),
      readTime: item.read_time || item.readTime || '4 menit baca',
      image: item.photo || item.banner || item.image || DEFAULT_IMAGE,
      excerpt: item.news_content_short || item.excerpt || item.meta_description || '',
      body: body.length ? body : [item.news_content_short || item.excerpt || 'Konten berita belum tersedia.'],
      author: item.contributor_pewarta || item.author || '',
      editor: item.contributor_editor || item.editor || '',
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
      parentId: comment.parent_id ?? comment.parentId ?? null,
      name: comment.name || comment.commentator_name || comment.author_name || 'Pembaca',
      email: comment.email || comment.commentator_email || '',
      role: comment.role || 'Pembaca',
      status: comment.status || comment.comment_status || 'Pending',
      text: comment.text || comment.comment || comment.content || '',
      article: comment.article || comment.news_title || '',
      date: comment.date || comment.created_at || '',
      upVotes: Number(comment.up_votes ?? comment.upVotes ?? 0),
      downVotes: Number(comment.down_votes ?? comment.downVotes ?? 0),
      score: Number(comment.score ?? 0),
      depth: Number(comment.depth ?? 0),
      children: Array.isArray(comment.children) ? comment.children.map(normalizeComment) : [],
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
      parentId: normalized.parentId,
      parentExcerpt: comment.parent_excerpt || comment.parentExcerpt || '',
      parentName: comment.parent_name || comment.parentName || '',
    };
  }

  function normalizeAdminComments(payload) {
    return normalizeListPayload(payload).map(normalizeAdminComment);
  }

  function normalizeApprovedComments(payload) {
    return normalizeListPayload(payload).filter(isApprovedComment).map(normalizeComment);
  }

  function normalizeCommentTree(payload = {}) {
    const items = Array.isArray(payload?.data) ? payload.data : normalizeListPayload(payload);
    return {
      data: items.map(normalizeComment),
      policy: payload?.policy && typeof payload.policy === 'object' ? payload.policy : {},
      voter: payload?.voter && typeof payload.voter === 'object' ? payload.voter : { votes: {} },
    };
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
    return routeUrl('admin.newsCommentAction', { id, action: normalizedAction });
  }

  function createCommentPayload({ name, email, comment }) {
    return {
      name: String(name || '').trim(),
      email: String(email || '').trim(),
      comment: String(comment || '').trim(),
    };
  }

  function buildCommentVoteEndpoint(slug, id) {
    return routeUrl('public.newsCommentVote', { slug, id });
  }

  function buildCommentReplyPayload({ parentId, name, email, comment, website }) {
    return {
      ...createCommentPayload({ name, email, comment }),
      parent_id: parentId ? Number(parentId) : 0,
      website: String(website || '').trim(),
    };
  }

  function normalizeEvent(item = {}) {
    const title = item.event_title || item.title || 'Event GenBI Jambi';
    const id = item.event_id || item.id || 0;
    const content = item.event_content || item.content || '';
    const photo = item.photo || '';
    const banner = item.banner || '';
    const images = extractEventImages(item);
    const slug = item.slug || (id ? `${slugify(title)}-${id}` : slugify(title));
    return {
      id,
      slug,
      title,
      content,
      excerpt: item.event_content_short || item.excerpt || '',
      start_date: item.event_start_date || item.start_date || '',
      end_date: item.event_end_date || item.end_date || '',
      location: item.event_location || item.location || '',
      map: item.event_map || item.map || '',
      image: photo || item.image || banner || DEFAULT_IMAGE,
      photo,
      banner,
      images,
      status: item.status || 'Upcoming',
      meta_title: item.meta_title || '',
      meta_description: item.meta_description || '',
    };
  }

  function extractEventImages(item = {}) {
    const existing = Array.isArray(item.images) ? item.images.filter(Boolean) : [];
    const inline = [];
    const content = String(item.event_content || item.content || '');
    const matches = content.matchAll(/<img\b[^>]*\bsrc=["']([^"']+)["'][^>]*>/gi);
    for (const match of matches) {
      const src = String(match[1] || '').trim();
      if (src) inline.push(src);
    }

    const combined = [...existing, ...inline, item.banner || '', item.photo || '', item.image || '']
      .map((value) => String(value || '').trim())
      .filter(Boolean);

    return Array.from(new Set(combined));
  }

  function normalizeEventList(payload) {
    return normalizeListPayload(payload).map(normalizeEvent);
  }

  function normalizePrestasi(item = {}) {
    const title = item.judul_prestasi || item.title || 'Prestasi GenBI Jambi';
    const id = item.prestasi_id || item.id || item.slug || slugify(title);
    const images = resolvePrestasiImages(item);
    return {
      id,
      slug: item.slug || `${slugify(title)}-${id}`,
      name: item.nama_anggota || item.member_name || item.name || '',
      title,
      campus: item.komisariat || item.campus || '',
      category: item.category || item.kategori || 'Prestasi',
      year: item.tahun || item.year || '',
      image: images[0] || DEFAULT_IMAGE,
      images,
      description: item.deskripsi_singkat || item.description || '',
      detail: item.deskripsi_detail || item.detail || item.content || item.deskripsi_singkat || item.description || '',
      institution: item.institusi_penyelenggara || item.institution || '',
      raw: item,
    };
  }

  function extractDriveId(value = '') {
    const text = String(value || '');
    if (!/(drive\.google\.com|docs\.google\.com)/i.test(text)) return '';
    return text.match(/[?&]id=([-\w]{10,})/i)?.[1]
      || text.match(/\/file\/d\/([-\w]{10,})/i)?.[1]
      || text.match(/[-\w]{25,}/)?.[0]
      || '';
  }

  function resolvePrestasiImage(value = '') {
    const text = String(value || '').trim();
    if (!text) return DEFAULT_IMAGE;

    const driveId = extractDriveId(text);
    if (driveId) return `https://drive.google.com/thumbnail?id=${encodeURIComponent(driveId)}&sz=w1000`;

    if (/^https?:\/\//i.test(text)) return text.replace('/public/uploads/', '/uploads/');
    if (text.startsWith('/public/uploads/')) return text.replace('/public/uploads/', '/uploads/');
    if (text.startsWith('/')) return text;
    return `/uploads/prestasi/${text.replace(/^\/+/, '')}`;
  }

  function resolvePrestasiImages(item = {}) {
    const detail = String(item.deskripsi_lengkap || item.deskripsi_detail || item.detail || item.content || '');
    const submissionPhotos = extractSubmissionPhotoUrls(item.submission_payload_json || item.payload_json || '');
    const candidates = [
      item.foto,
      item.foto_prestasi,
      item.photo,
      item.image,
      item.certificate_photo,
      ...extractDocumentationImageLinks(detail),
      ...submissionPhotos,
    ].filter(Boolean);

    const resolved = [];
    candidates.forEach((candidate) => {
      const url = resolvePrestasiImage(candidate);
      if (url && !resolved.includes(url)) resolved.push(url);
    });

    return resolved.length ? resolved : [DEFAULT_IMAGE];
  }

  function extractDocumentationImageLinks(detail = '') {
    const text = String(detail || '');
    if (!text) return [];

    const links = [];
    const docMatch = text.match(/Dokumentasi\s*:\s*(.+)/i);
    if (docMatch?.[1]) {
      docMatch[1].split(/\s*,\s*/).forEach((part) => {
        const value = String(part || '').trim();
        if (value) links.push(value);
      });
    }

    const urlMatches = text.match(/https?:\/\/[^\s<>"]+/gi) || [];
    urlMatches.forEach((match) => {
      const value = String(match || '').replace(/[.,)]$/, '');
      if (looksLikePrestasiImageSource(value)) links.push(value);
    });

    return Array.from(new Set(links.filter(Boolean)));
  }

  function extractSubmissionPhotoUrls(payloadJson = '') {
    const text = String(payloadJson || '').trim();
    if (!text) return [];

    try {
      const payload = JSON.parse(text);
      if (!Array.isArray(payload?.photos)) return [];
      return Array.from(new Set(payload.photos.map((photo) => String(photo?.url || '').trim()).filter(Boolean)));
    } catch {
      return [];
    }
  }

  function looksLikePrestasiImageSource(value = '') {
    const text = String(value || '').trim();
    if (!text) return false;
    if (extractDriveId(text)) return true;
    if (/\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i.test(text)) return true;
    return text.startsWith('/uploads/');
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

  function fillRoutePattern(pattern, params = {}) {
    return String(pattern || '').replace(/\{([a-zA-Z0-9_]+)\}/g, (_, key) => encodeURIComponent(params[key] ?? ''));
  }

  function routeUrl(name, params = {}, locationLike) {
    const [scope, routeName] = String(name || '').includes('.') ? String(name).split('.', 2) : ['public', name];
    const route = ROUTES[scope]?.[routeName];
    if (!route) return '';
    const pattern = isStaticProtocol(locationLike) && route.static ? route.static : route.clean;
    return fillRoutePattern(pattern, params);
  }

  function pageUrl(page, locationLike) {
    const url = routeUrl(`public.${page}`, {}, locationLike);
    return url || (isStaticProtocol(locationLike) ? `${page}.html` : `/${page}`);
  }

  function adminUrl(page, locationLike) {
    const normalized = String(page || '').replace(/\.html$|\.php$/g, '').replace(/^\/+/, '');
    const routeName = normalized.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
    return routeUrl(`admin.${routeName}`, {}, locationLike) || (isStaticProtocol(locationLike) ? `admin/${normalized}.html` : `/admin/${normalized}`);
  }

  function newsDetailUrl(news, locationLike) {
    const item = normalizeNews(news || {});
    return routeUrl('public.newsDetail', { slug: item.slug, id: item.id }, locationLike);
  }

  function canonicalNewsUrl(news, locationLike) {
    const item = normalizeNews(news || {});
    const path = routeUrl('public.newsDetail', { slug: item.slug, id: item.id }, { protocol: 'https:' });
    const source = locationLike || (typeof window !== 'undefined' ? window.location : null);

    if (source && source.protocol && source.host && !isStaticProtocol(source)) {
      return `${source.protocol}//${source.host}${path}`;
    }

    return `https://genbijambi.com${path}`;
  }

  function resolveStaticRoute(pathname = '/') {
    const cleanPath = String(pathname || '/').replace(/\/+$|^\s+|\s+$/g, '') || '/';
    const publicRoutes = {
      '/': '/fallbacks/index.html',
      '/about': '/fallbacks/about.html',
      '/team': '/fallbacks/team.html',
      '/teams': '/fallbacks/team.html',
      '/event': '/fallbacks/event.html',
      '/prestasi': '/fallbacks/prestasi.html',
      '/news': '/fallbacks/news.html',
      '/contact': '/fallbacks/contact.html',
    };
    if (publicRoutes[cleanPath]) return publicRoutes[cleanPath];
    if (/^\/event\/\d+$/.test(cleanPath)) return '/fallbacks/event.html';
    if (/^\/news\/[^/]+$/.test(cleanPath)) return '/fallbacks/news-detail.html';
    if (/^\/prestasi\/submit\/[^/]+$/.test(cleanPath)) return '/fallbacks/prestasi-submit.html';
    if (cleanPath === '/admin') return '/fallbacks/admin/dashboard.html';
    if (/^\/admin\/[^/]+$/.test(cleanPath)) return `/fallbacks/admin/${cleanPath.split('/').pop()}.html`;
    // Legacy: support paths without /fallbacks prefix for assets
    if (/^\/assets\//.test(cleanPath)) return null;
    return null;
  }

  function canRequestBackend(locationLike) {
    const protocol = locationLike?.protocol || '';
    return protocol === 'http:' || protocol === 'https:';
  }

  return {
    DEFAULT_IMAGE,
    buildCommentActionEndpoint,
    buildCommentReplyPayload,
    buildCommentVoteEndpoint,
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
    normalizeCommentTree,
    normalizeCommentStatus,
    normalizeListPayload,
    normalizeNews,
    normalizeNewsList,
    normalizePrestasi,
    normalizeEvent,
    normalizeEventList,
    normalizePrestasiList,
    normalizeTeamMember,
    normalizeTeamList,
    normalizeTeamPayload,
    adminUrl,
    canonicalNewsUrl,
    newsDetailUrl,
    pageUrl,
    resolveStaticRoute,
    routeUrl,
    ROUTES,
    slugify,
  };
});
