(function () {
  'use strict';

  const Core = window.GenBIAPICore;
  const Data = window.GenBIData || {};
  const fallbackAdminComments = [
    { id: 1, article: 'Talkshow Siginjai Fest 2026 Dorong Generasi Muda Berkarya', name: 'Rina Aprilianti', email: 'rina@example.com', date: '2026-04-30', status: 'Approved', comment: 'Berita ini membantu pembaca memahami arah kegiatan Siginjai Fest secara ringkas.' },
    { id: 2, article: 'Talkshow Ekonomi Syariah Siginjai Fest', name: 'Dimas Pratama', email: 'dimas@example.com', date: '2026-04-30', status: 'Pending', comment: 'Mohon tambahkan dokumentasi foto kegiatan dan daftar narasumber.' },
    { id: 3, article: 'GenBI Goes To Campus Universitas Jambi', name: 'Aulia Rahman', email: 'aulia@example.com', date: '2026-04-24', status: 'Approved', comment: 'Informasi beasiswa dan kebanksentralan jadi lebih mudah dipahami.' },
    { id: 4, article: 'GenBI Jambi Laksanakan Kegiatan Buka Bersama dan Aksi Sosial', name: 'Anonim', email: 'anon@example.com', date: '2026-03-07', status: 'Flagged', comment: 'Komentar perlu diperiksa ulang oleh moderator sebelum tampil.' },
  ];

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
  }

  async function requestJson(path, options = {}) {
    if (!Core.canRequestBackend(window.location)) throw new Error('Backend requests require http or https.');
    if (!window.fetch) throw new Error('Fetch API is not available.');
    const headers = { Accept: 'application/json', ...(options.headers || {}) };
    // Automatically include CSRF token for mutating requests
    const method = (options.method || 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD') {
      const token = getCsrfToken();
      if (token) headers['X-CSRF-TOKEN'] = token;
    }
    const response = await window.fetch(path, {
      ...options,
      headers,
      credentials: options.credentials || 'same-origin',
    });
    if (!response.ok) throw new Error(`Request failed: ${response.status}`);
    return response.json();
  }

  async function withFallback(request, fallback) {
    try {
      return await request();
    } catch (error) {
      return fallback();
    }
  }

  async function getNewsList(filters = {}) {
    const endpoint = Core.buildEndpoint(Core.routeUrl('public.news'), filters);
    return withFallback(
      async () => Core.normalizeNewsList(await requestJson(endpoint)),
      () => Core.normalizeNewsList(Data.news || [])
    );
  }

  async function getNewsDetail(identifier) {
    const staticNews = Core.normalizeNewsList(Data.news || []);
    const fallbackItem = Core.findNewsByIdOrSlug(staticNews, identifier) || null;
    const slug = fallbackItem?.slug || identifier;
    return withFallback(
      async () => Core.normalizeNews(await requestJson(Core.routeUrl('public.newsDetail', { slug }))),
      () => fallbackItem || Promise.reject(new Error('News not found'))
    );
  }

  async function getRelatedNews(currentId, category) {
    const params = category ? { category } : {};
    return withFallback(
      async () => Core.normalizeNewsList(await requestJson(Core.buildEndpoint(Core.routeUrl('public.news'), params))).filter((item) => String(item.id) !== String(currentId)).slice(0, 3),
      () => Core.normalizeNewsList(Data.news || []).filter((item) => String(item.id) !== String(currentId)).slice(0, 3)
    );
  }

  async function getNewsComments(news) {
    const slug = news?.slug || news?.id;
    return withFallback(
      async () => Core.normalizeCommentTree(await requestJson(Core.routeUrl('public.newsComments', { slug }))),
      () => ({ data: [], policy: {}, voter: { votes: {} } })
    );
  }

  async function submitNewsComment(news, payload) {
    const slug = news?.slug || news?.id;
    const body = payload && Object.prototype.hasOwnProperty.call(payload, 'parentId')
      ? Core.buildCommentReplyPayload(payload)
      : Core.createCommentPayload(payload);
    const token = getCsrfToken();
    if (token) body._csrf_token = token;
    return requestJson(Core.routeUrl('public.newsCommentStore', { slug }), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
  }

  async function voteComment(news, commentId, value) {
    const slug = news?.slug || news?.id;
    const body = { value };
    const token = getCsrfToken();
    if (token) body._csrf_token = token;
    return requestJson(Core.buildCommentVoteEndpoint(slug, commentId), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
  }

  async function getPrestasiList(filters = {}) {
    return withFallback(
      async () => Core.normalizePrestasiList(await requestJson(Core.buildEndpoint(Core.routeUrl('public.prestasi'), filters))),
      () => Core.normalizePrestasiList(Data.prestasi || [])
    );
  }

  async function getTeamList(filters = {}) {
    return withFallback(
      async () => {
        const json = await requestJson(Core.buildEndpoint(Core.routeUrl('public.team'), filters));
        return Core.normalizeTeamPayload(json);
      },
      () => {
        const members = Core.normalizeTeamList(Data.teamMembers || []);
        return {
          members,
          bpi: Core.normalizeTeamList(Data.bpiMembers || []),
          filters: {
            divisions: Array.from(new Set(members.map((member) => member.division).filter(Boolean))),
            campuses: Array.from(new Set(members.map((member) => member.campus).filter(Boolean))),
            years: Array.from(new Set(members.map((member) => member.year).filter(Boolean))),
          },
          meta: { page: 1, perPage: members.length, total: members.length },
        };
      }
    );
  }

  async function getEventList(filters = {}) {
    return withFallback(
      async () => Core.normalizeEventList(await requestJson(Core.buildEndpoint(Core.routeUrl('public.event'), filters))),
      () => Core.normalizeEventList(Data.publicEvents || [])
    );
  }

  async function getEventDetail(identifier) {
    return withFallback(
      async () => {
        const fallbackItem = Core.normalizeEventList(Data.publicEvents || []).find((e) => String(e.id) === String(identifier) || e.slug === String(identifier));
        const slug = fallbackItem?.slug || String(identifier);
        return Core.normalizeEvent((await requestJson(Core.routeUrl('public.eventDetail', { slug }))).data || {});
      },
      () => Core.normalizeEventList(Data.publicEvents || []).find((e) => String(e.id) === String(identifier) || e.slug === String(identifier)) || null
    );
  }

  async function getAdminComments(filters = {}) {
    return withFallback(
      async () => Core.normalizeAdminComments(await requestJson(Core.buildEndpoint(Core.routeUrl('admin.newsComments'), filters))),
      () => Core.normalizeAdminComments(fallbackAdminComments)
    );
  }

  async function moderateComment(id, action) {
    const endpoint = Core.buildCommentActionEndpoint(id, action);
    return withFallback(
      async () => requestJson(endpoint, { method: 'POST' }),
      () => ({ ok: true, mode: 'fallback', id, action })
    );
  }

  async function getCommentSettings() {
    return withFallback(
      async () => (await requestJson(Core.routeUrl('admin.commentSetting'))).data || {},
      () => ({})
    );
  }

  async function updateCommentSettings(values) {
    return requestJson(Core.routeUrl('admin.commentSetting'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(values || {}),
    });
  }

  async function getTeamMemberOptions(query = '', limit = 12) {
    const params = typeof query === 'object' && query !== null
      ? { ...query }
      : { q: query, limit };
    params.type = params.type || 'member';
    params.limit = params.limit || limit || 12;
    return requestJson(Core.buildEndpoint(Core.routeUrl('admin.teamMemberOptions'), params));
  }

  async function getPresensiEvent(id) {
    return requestJson(Core.routeUrl('admin.presensiShow', { id }));
  }

  async function getPresensiSubmissions(id) {
    return requestJson(Core.routeUrl('admin.presensiSubmissions', { id }));
  }

  async function approvePresensiSubmission(id) {
    return requestJson(Core.routeUrl('admin.presensiApprove', { id }), { method: 'POST' });
  }

  async function submitPresensi(token, formData) {
    return requestJson(Core.routeUrl('public.presensiShow', { token }), {
      method: 'POST',
      body: formData,
    });
  }

  window.GenBIAPI = {
    approvePresensiSubmission,
    getCsrfToken,
    getAdminComments,
    getCommentSettings,
    getEventDetail,
    getEventList,
    getNewsComments,
    getNewsDetail,
    getNewsList,
    getPrestasiList,
    getPresensiEvent,
    getPresensiSubmissions,
    getRelatedNews,
    getTeamList,
    getTeamMemberOptions,
    moderateComment,
    requestJson,
    submitPresensi,
    submitNewsComment,
    updateCommentSettings,
    voteComment,
  };
})();
