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
      headers,
      credentials: 'same-origin',
      ...options,
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
    const fallbackComments = [
      { id: 1, name: 'Rina Aprilianti', role: 'Mahasiswa', status: 'Disetujui', text: 'Beritanya membantu memahami kegiatan GenBI Jambi dengan lebih ringkas.' },
      { id: 2, name: 'Dimas Pratama', role: 'Anggota GenBI', status: 'Pending', text: 'Dokumentasi kegiatan bisa ditambah pada pembaruan berikutnya.' },
      { id: 3, name: 'Aulia Rahman', role: 'Pembaca', status: 'Approved', text: 'Semoga agenda serupa makin sering hadir untuk mahasiswa Jambi.' },
    ];
    return withFallback(
      async () => Core.normalizeApprovedComments(await requestJson(Core.routeUrl('public.newsComments', { slug }))),
      () => Core.normalizeApprovedComments(fallbackComments)
    );
  }

  async function submitNewsComment(news, payload) {
    const slug = news?.slug || news?.id;
    const body = Core.createCommentPayload(payload);
    return withFallback(
      async () => requestJson(Core.routeUrl('public.newsCommentStore', { slug }), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      }),
      () => ({ ok: true, mode: 'fallback', message: 'Komentar masuk antrean moderasi.' })
    );
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

  async function getEventDetail(id) {
    return withFallback(
      async () => Core.normalizeEvent((await requestJson(Core.routeUrl('public.eventDetail', { id }))).data || {}),
      () => Core.normalizeEventList(Data.publicEvents || []).find((e) => String(e.id) === String(id)) || null
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

  window.GenBIAPI = {
    getCsrfToken,
    getAdminComments,
    getEventDetail,
    getEventList,
    getNewsComments,
    getNewsDetail,
    getNewsList,
    getPrestasiList,
    getRelatedNews,
    getTeamList,
    moderateComment,
    submitNewsComment,
  };
})();
