(function () {
  'use strict';

  const { news, teamMembers, site, programs, publicEvents } = window.GenBIData || {};
  const Admin = window.GenBIAdmin || {};
  const API = window.GenBIAPI || {};
  const Core = window.GenBIAPICore || {};
  const UI = window.GenBIUI || {};
  const { adminUrl } = window.GenBIApp || {};
  const programIconChoices = Admin.programIconChoices || ['sparkles', 'users', 'bank', 'chart', 'academic', 'calendar', 'heart', 'news', 'grid'];
  const programIconGroups = Admin.programIconGroups || [{ key: 'all', label: 'All Icons', icons: programIconChoices }];
  const page = document.body.dataset.cmsPage || 'news';
  const mode = document.body.dataset.cmsMode || 'list';
  const csrfMeta = document.querySelector('meta[name="csrf-token"]')?.content || '';
  if (csrfMeta && API && !API.getCsrfToken) {
    API.getCsrfToken = () => csrfMeta;
  }

  function route(name, params = {}) {
    return Core.routeUrl(name, params, window.location);
  }

  function normalizeAdminImageUrl(value = '') {
    const source = String(value || '').trim();
    if (!source) return '';
    if (/^(https?:)?\/\//i.test(source) || source.startsWith('data:') || source.startsWith('/')) return source;
    return `/uploads/${source.replace(/^\/+/, '')}`;
  }

  const categories = [
    { id: 1, name: 'BANK INDONESIA', banner: 'https://genbijambi.com/public/uploads/banner-1.png' },
    { id: 2, name: 'GenBI Wilayah', banner: 'https://genbijambi.com/public/uploads/banner-1.png' },
    { id: 3, name: 'GenBI UIN STS JAMBI', banner: 'https://genbijambi.com/public/uploads/banner-1.png' },
    { id: 4, name: 'GenBI Universitas Jambi', banner: '' },
    { id: 5, name: 'GenBI Kolaborasi', banner: 'https://genbijambi.com/public/uploads/banner-1.png' },
    { id: 6, name: 'GenBI Prestasi', banner: 'https://genbijambi.com/public/uploads/banner-1.png' }
  ];

  const languages = [
    ['ABOUT', 'About'], ['ADDRESS', 'Address'], ['ALL', 'All'], ['CATEGORY', 'Category'], ['CLIENT_COMMENT', 'Client Comment'],
    ['CLIENT_COMPANY_NAME', 'Client Company Name'], ['CLIENT_NAME', 'Client Name'], ['COMMENT', 'Comment'], ['CONTACT', 'Contact'],
    ['CONTACT_FORM', 'Contact Form'], ['EMAIL_ADDRESS', 'Email Address'], ['END_DATE', 'End Date'], ['EVENT', 'Event'],
    ['EVENT_END_DATE', 'Event End Date'], ['EVENT_LOCATION_MAP', 'Event Location Map'], ['EVENT_START_DATE', 'Event Start Date'],
    ['FAQ', 'FAQ'], ['FIRST_NAME', 'First Name'], ['HOME', 'Home'], ['LAST_NAME', 'Last Name'], ['MESSAGE', 'Message'],
    ['NAME', 'Name'], ['NEWS', 'News'], ['NEWS_DATE', 'News Date'], ['NO_RESULT_FOUND', 'No Result is Found'],
    ['PAGE', 'Page'], ['PHONE_NUMBER', 'Phone Number'], ['PHOTO_GALLERY', 'Photo Gallery'], ['PORTFOLIO', 'Portfolio'],
    ['PRICING_TABLE', 'Pricing Table'], ['PROJECT_END_DATE', 'Project End Date'], ['PROJECT_OVERVIEW', 'Project Overview'],
    ['PROJECT_START_DATE', 'Project Start Date'], ['READ_MORE', 'Read More'], ['SEARCH_FOR', 'Search for...'], ['SEND_MESSAGE', 'Send Message'],
    ['SERVICE', 'Service'], ['SHARE_THIS_EVENT', 'Share This Event'], ['SHARE_THIS_NEWS', 'Share This News'], ['START_DATE', 'Start Date'],
    ['SUBJECT', 'Subject'], ['SUBMIT', 'Submit'], ['TEAM', 'Team'], ['TESTIMONIAL', 'Testimonial']
  ];

  const events = [];

  const sliders = [
    { id: 1, photo: 'https://genbijambi.com/public/uploads/slider-1.png', heading: 'WE ARE GENBI PROVINSI JAMBI', button1: 'Read More', url1: 'https://wa.me/6289627896750', button2: 'Contact Us', url2: 'https://wa.me/6289627896750', position: 'Left' },
    { id: 2, photo: 'https://genbijambi.com/public/uploads/slider-4.png', heading: 'WE ARE GENBI PROVINSI JAMBI', button1: 'Read More', url1: 'https://wa.me/6289627896750', button2: 'Contact Us', url2: 'https://wa.me/6289627896750', position: 'Right' }
  ];

  const memberPhotos = [
    'https://official.genbijambi.com/storage/team-members/Ilham-Jaya-Kusuma.png',
    'https://official.genbijambi.com/storage/team-members/Ananda-Marisa-Pertiwi.png',
    'https://official.genbijambi.com/storage/team-members/Depi-Susanti.png'
  ];

  const prestasiCategories = ['QRIS', 'KTI', 'Essay', 'Inovasi Desa', 'Kreativitas', 'Ekonomi Syariah'];

  const hasSsrPrototype = () => Boolean(document.querySelector('#admin-prototype-root[data-ssr="true"]'));

  const routes = {
    language: () => { Admin.renderAdminShell('language'); hasSsrPrototype() ? bindSsrPrototype() : renderLanguage(); },
    category: () => { Admin.renderAdminShell('category'); renderCategoryList(); },
    comment: () => { Admin.renderAdminShell('comment'); renderCommentSetup(); },
    'comment-setting': () => { Admin.renderAdminShell('comment-setting'); document.querySelector('#comment-setting-form[data-ssr="true"]') ? bindSsrCommentSettings() : renderCommentSettings(); },
    news: () => { Admin.renderAdminShell('news-list'); mode === 'editor' ? renderNewsEditor(false) : renderNewsList(); },
    'news-edit': () => { Admin.renderAdminShell('news-list'); renderNewsEditor(true); },
    prestasi: () => { Admin.renderAdminShell('prestasi'); mode === 'editor' ? renderPrestasiEditor(false) : renderPrestasiList(); },
    'prestasi-add': () => { Admin.renderAdminShell('prestasi'); renderPrestasiEditor(false); },
    'prestasi-edit': () => { Admin.renderAdminShell('prestasi'); renderPrestasiEditor(true); },
    'prestasi-token': () => { Admin.renderAdminShell('prestasi'); renderPrestasiTokenList(); },
    event: () => { Admin.renderAdminShell('event'); mode === 'editor' ? renderEventEditor() : renderEventList(); },
    'event-edit': () => { Admin.renderAdminShell('event'); renderEventEditor(); },
    slider: () => { Admin.renderAdminShell('slider'); hasSsrPrototype() ? bindSsrPrototype() : (mode === 'editor' ? renderSliderEditor() : renderSliderList()); },
    'slider-add': () => { Admin.renderAdminShell('slider'); hasSsrPrototype() ? bindSsrPrototype() : renderSliderEditor(); },
    team: () => { Admin.renderAdminShell('team'); mode === 'editor' ? renderTeamEditor() : renderTeamList(); },
    feature: () => { Admin.renderAdminShell('feature'); mode === 'editor' ? renderFeatureEditor() : renderFeatureList(); },
    'feature-edit': () => { Admin.renderAdminShell('feature'); renderFeatureEditor(); },
    why: () => { Admin.renderAdminShell('why'); hasSsrPrototype() ? bindSsrPrototype() : (mode === 'editor' ? renderWhyChooseEditor() : renderWhyChooseList()); },
    faq: () => { Admin.renderAdminShell('faq'); hasSsrPrototype() ? bindSsrPrototype() : (mode === 'editor' ? renderFaqEditor() : renderFaqList()); },
    social: () => { Admin.renderAdminShell('social'); hasSsrPrototype() ? bindSsrPrototype() : renderSocialMedia(); },
    photo: () => { Admin.renderAdminShell('gallery'); mode === 'editor' ? renderPhotoEditor() : renderPhotoList(); },
    page: () => { Admin.renderAdminShell('page'); },
    buku: () => { Admin.renderAdminShell('buku-list'); },
    'buku-add': () => { Admin.renderAdminShell('buku-add'); },
    'buku-edit': () => { Admin.renderAdminShell('buku-list'); },
  };

  const teamSelection = new Set();

  // Defer route execution until this module has finished initializing.
  // Some editor routes reference tools declared later in the file (for example
  // MapEmbedTool); running immediately can hit class temporal-dead-zone errors.
  queueMicrotask(() => (routes[page] || routes.news)());

  function renderShell(title, subtitle, actions = '') {
    const root = document.querySelector('#admin-content');
    if (!root) return null;
    root.innerHTML = `
      <section class="mx-auto max-w-7xl">
        <header class="cms-header slide-in">
          <div>
            <p class="eyebrow">Admin CMS</p>
            <h1 class="section-title mt-3">${title}</h1>
            ${subtitle ? `<p class="mt-4 max-w-2xl text-base leading-7 text-[rgb(var(--text-secondary))]">${subtitle}</p>` : ''}
          </div>
          <div class="cms-actions">${actions}</div>
        </header>
        <div id="cms-body" class="mt-6"></div>
      </section>
    `;
    return document.querySelector('#cms-body');
  }

  function selectControl({ id, options, value = '', className = 'config-input', attrs = '' }) {
    return `<select class="${className} js-admin-custom-select" id="${id}" ${attrs}>${options.map((option) => {
      const optionValue = typeof option === 'object' ? option.value : option;
      const optionLabel = typeof option === 'object' ? option.label : option;
      return `<option value="${escape(optionValue)}" ${String(optionValue) === String(value) ? 'selected' : ''}>${escape(optionLabel)}</option>`;
    }).join('')}</select>`;
  }

  function enhanceAdminSelects(root = document) {
    UI?.enhanceNativeSelects(root, 'select.js-admin-custom-select', {
      buttonClass: 'admin-select-button',
      iconHtml: Admin.icon('chevronDown', 'h-4 w-4 shrink-0 text-[rgb(var(--text-secondary))]'),
      menuClass: 'admin-select-menu',
      portal: true,
      wrapperClass: 'admin-custom-select',
    });
  }

  function renderLanguage() {
    const body = renderShell('Edit Language Data', 'Ubah label bahasa dengan layout block. Label teknis tetap terbaca, teks terjemahan bisa diedit langsung.', '<button id="save-language" class="btn btn-primary">Update Language</button>');
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950 dark-theme-note">NB: bagian ini untuk mengubah teks kecil yang tidak diatur dari section lain.</div>
        <div class="language-grid mt-6">
          ${languages.map(([key, value]) => `
            <article class="language-row">
              <code>${key}</code>
              <div class="language-edit" contenteditable="true" spellcheck="false">${escape(value)}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    document.querySelector('#save-language').addEventListener('click', () => Admin.showToast('Language data disimpan pada mode simulasi.'));
  }

  async function renderCategoryList() {
    const ssrList = document.querySelector('#admin-category-list[data-ssr="true"]');
    if (ssrList) {
      bindCategoryActions(true);
      return;
    }
    const body = renderShell('View Categories', 'Kategori berita dipakai untuk filter publik, editor berita, dan Pengumuman di beranda.', '<button class="btn btn-primary" type="button" data-category-add>Add New</button>');
    let items = categories;
    try {
      const response = await fetch(route('admin.categories'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      const payload = await response.json();
      if (Array.isArray(payload?.data)) {
        items = payload.data.map((item) => ({
          id: item.id || item.category_id,
          name: item.name || item.category_name || '',
          banner: normalizeAdminImageUrl(item.banner || item.category_banner || ''),
        }));
      }
    } catch (error) { /* static fallback */ }

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Category')}
        <div class="admin-responsive-table mt-5">
          <table class="cms-table">
            <thead><tr><th>SL</th><th>Category Name</th><th>Category Banner</th><th>Action</th></tr></thead>
            <tbody>${items.map((item, index) => `
              <tr>
                <td>${index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td>${item.banner ? `<img src="${escape(item.banner)}" class="table-banner" alt="${escape(item.name)}" />` : '<span class="text-neutral-500">Belum ada banner</span>'}</td>
                <td><div class="flex gap-2"><button class="cms-action edit" type="button" data-category-edit="${item.id}" data-category-name="${escape(item.name)}">Edit</button><button class="cms-action delete" type="button" data-category-delete="${item.id}">Delete</button></div></td>
              </tr>
            `).join('')}</tbody>
          </table>
        </div>
      </section>
    `;
    enhanceAdminSelects(body);
    bindCategoryActions();
  }

  function openCategoryForm({ id = '', name = '', trigger = document.activeElement } = {}) {
    const root = document.querySelector('#admin-modal-root') || document.body;
    const previous = document.querySelector('#category-editor-modal');
    if (previous) previous.remove();

    const isEdit = Boolean(id);
    const modal = document.createElement('div');
    modal.id = 'category-editor-modal';
    modal.className = 'category-editor-modal hidden';
    root.appendChild(modal);

    const content = `
      <div class="category-editor-backdrop" data-modal-close></div>
      <section class="category-editor-panel" role="dialog" aria-modal="true" aria-labelledby="category-editor-title">
        <header class="category-editor-head">
          <div>
            <p class="eyebrow">Admin CMS</p>
            <h2 id="category-editor-title">${isEdit ? 'Edit Kategori' : 'Tambah Kategori'}</h2>
            <p>${isEdit ? 'Ubah nama kategori berita yang sudah ada.' : 'Tambahkan kategori berita baru dari tombol Add New.'}</p>
          </div>
          <button type="button" class="category-editor-close" data-modal-close aria-label="Tutup form kategori">×</button>
        </header>
        <form class="category-editor-form" id="category-editor-form">
          <label class="config-field m-0">
            <span>Nama Kategori</span>
            <input class="config-input" id="category-editor-name" placeholder="Contoh: Pengumuman" maxlength="120" required value="${escape(name)}" />
          </label>
          <div class="category-editor-actions">
            <button class="btn btn-secondary" type="button" data-modal-close>Batal</button>
            <button class="btn btn-primary" type="submit">${isEdit ? 'Update Kategori' : 'Tambah Kategori'}</button>
          </div>
        </form>
      </section>
    `;

    const controller = UI?.createModalController?.(modal, {
      panelSelector: '.category-editor-panel',
      closeSelector: '[data-modal-close]',
      initialFocusSelector: '#category-editor-name',
      onClose: () => modal.remove(),
    });

    if (controller) {
      controller.open({ content, trigger });
    } else {
      modal.innerHTML = content;
      modal.classList.remove('hidden');
      document.body.classList.add('modal-lock');
    }

    const close = () => {
      if (controller) {
        controller.close();
        return;
      }
      modal.remove();
      document.body.classList.remove('modal-lock');
      trigger?.focus?.();
    };

    modal.querySelector('#category-editor-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const categoryName = modal.querySelector('#category-editor-name')?.value?.trim() || '';
      if (!categoryName) return Admin.showToast('Nama kategori wajib diisi.');

      const token = getAdminCsrfToken();
      const endpoint = isEdit ? route('admin.categoryUpdate', { id }) : route('admin.categoryStore');
      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: new URLSearchParams({ name: categoryName, category_name: categoryName, _csrf_token: token }),
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
          Admin.showToast(payload.error || 'Gagal menyimpan kategori.');
          return;
        }
        close();
        Admin.showToast(isEdit ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
        if (document.querySelector('#admin-category-list[data-ssr="true"]')) window.location.reload();
        else renderCategoryList();
      } catch (error) {
        Admin.showToast('Gagal menyimpan kategori. Periksa koneksi.');
      }
    });
  }

  function bindCategoryActions(isSsr = false) {
    document.querySelector('[data-category-add]')?.addEventListener('click', (event) => openCategoryForm({ trigger: event.currentTarget }));

    document.querySelectorAll('[data-category-edit]').forEach((button) => {
      button.addEventListener('click', () => {
        openCategoryForm({ id: button.dataset.categoryEdit || '', name: button.dataset.categoryName || '', trigger: button });
      });
    });

    document.querySelectorAll('[data-category-delete]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.categoryDelete || '';
        const ok = await Admin.showConfirm({ title: 'Hapus kategori?', message: 'Kategori hanya bisa dihapus jika belum dipakai oleh berita.', confirmText: 'Hapus', danger: true });
        if (!ok) return;

        const token = getAdminCsrfToken();
        try {
          const response = await fetch(route('admin.categoryDelete', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify({ _csrf_token: token }),
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) {
            Admin.showToast(payload.error || 'Gagal menghapus kategori.');
            return;
          }
          Admin.showToast('Kategori berhasil dihapus.');
          if (isSsr) window.location.reload();
          else renderCategoryList();
        } catch (error) {
          Admin.showToast('Gagal menghapus kategori. Periksa koneksi.');
        }
      });
    });
  }

  async function renderNewsList() {
    // Check if SSR markup exists - if so, only bind delete behavior and multi-select
    if (document.querySelector('#admin-news-list[data-ssr="true"]')) {
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      bindNewsDeleteButtons();
      bindAdminMultiSelect();
      return;
    }

    const body = renderShell('View News', 'Daftar berita dari database. Aksi hapus memakai custom confirmation modal.', `<a href="${adminUrl('news-add')}" class="btn btn-primary">Add News</a>`);
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data berita...</div>';

    let items = news; // fallback to static data
    try {
      const res = await fetch(route('admin.newsList'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (res.ok) {
        const json = await res.json();
        items = json.data || [];
      }
    } catch (e) { /* use fallback */ }

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('News')}
        <div class="admin-responsive-table mt-5">
          <table class="cms-table">
            <thead><tr><th>SL</th><th>News Title</th><th>News Short Content</th><th>Photo</th><th>Category</th><th>Status</th><th>Action</th></tr></thead>
            <tbody id="news-tbody"></tbody>
          </table>
        </div>
        <div class="admin-pagination mt-5" id="news-pagination" aria-label="Pagination berita"></div>
      </section>
    `;
    let currentPage = 1;
    const allItems = items;
    const renderPage = () => {
      const search = (body.querySelector('.cms-search input')?.value || '').toLowerCase();
      const perPage = Number(document.querySelector('#news-per-page')?.value) || 10;
      const filtered = allItems.filter((item) => {
        const title = (item.title || item.news_title || '').toLowerCase();
        const excerpt = (item.excerpt || item.news_content_short || '').toLowerCase();
        const category = (item.category || item.category_name || '').toLowerCase();
        return !search || title.includes(search) || excerpt.includes(search) || category.includes(search);
      });
      const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
      currentPage = Math.min(currentPage, totalPages);
      const start = (currentPage - 1) * perPage;
      const pageItems = filtered.slice(start, start + perPage);
      const tbody = document.querySelector('#news-tbody');
      if (tbody) tbody.innerHTML = renderNewsRows(pageItems, start);
      renderAdminPagination('#news-pagination', totalPages, currentPage, (page) => {
        currentPage = page;
        renderPage();
      });
      bindNewsDeleteButtons();
    };
    enhanceAdminSelects(body);
    document.querySelector('#news-per-page')?.addEventListener('change', () => { currentPage = 1; renderPage(); });
    body.querySelector('.cms-search input')?.addEventListener('input', () => { currentPage = 1; renderPage(); });
    renderPage();
  }

  function renderNewsRows(items, offset = 0) {
    if (items.length === 0) {
      return '<tr><td colspan="7" class="text-center text-neutral-500 py-8">Belum ada berita.</td></tr>';
    }
    return items.map((item, index) => `
      <tr>
        <td>${offset + index + 1}</td>
        <td><strong>${escape(item.title || item.news_title || '')}</strong><p class="mt-1 text-xs text-neutral-500">${item.date || item.published_at || ''}</p></td>
        <td><p class="news-caption-cell">${escape(item.excerpt || item.news_content_short || '')}</p></td>
        <td>${item.photo ? `<img src="${item.photo.startsWith('http') || item.photo.startsWith('/') ? item.photo : 'https://genbijambi.com/public/uploads/' + item.photo}" class="table-thumb" alt="${escape(item.title || '')}" />` : '<span class="text-neutral-400">-</span>'}</td>
        <td><span class="cms-pill">${escape(item.category || item.category_name || '')}</span></td>
        <td><span class="cms-pill ${item.status === 'published' ? 'cms-pill-green' : item.status === 'draft' ? 'cms-pill-yellow' : ''}">${item.status || 'published'}</span></td>
        <td>
          <div class="flex gap-2"><a href="${adminUrl('news-edit')}?id=${item.id || item.news_id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-delete data-news-id="${item.id || item.news_id}">Delete</button></div>
        </td>
      </tr>
    `).join('');
  }

  function bindNewsDeleteButtons() {
    document.querySelectorAll('[data-delete][data-news-id], [data-delete-news]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.newsId || button.dataset.deleteNews;
        const ok = await Admin.showConfirm({ title: 'Hapus berita?', message: 'Berita akan dihapus (soft delete). Data masih bisa dipulihkan.', confirmText: 'Hapus', danger: true });
        if (!ok) return;
        try {
          const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(route('admin.newsDelete', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin'
          });
          if (res.ok) {
            Admin.showToast('Berita berhasil dihapus.');
            button.closest('tr')?.remove();
          } else {
            Admin.showToast('Gagal menghapus berita.');
          }
        } catch (e) {
          Admin.showToast('Gagal menghapus berita.');
        }
      });
    });
  }

  async function renderNewsEditor(isEdit) {
    const id = Number(new URLSearchParams(location.search).get('id')) || 0;

    // Check if SSR form markup exists - if so, hydrate instead of rebuilding
    const ssrForm = document.querySelector('#news-editor-form[data-ssr="true"]');
    if (ssrForm) {
      const ssrIsEdit = ssrForm.dataset.edit === '1';
      const ssrItemId = Number(ssrForm.dataset.itemId) || 0;
      const ssrContent = document.querySelector('#editor-fallback article')?.innerHTML || '';
      const ssrItem = {
        id: ssrItemId,
        title: document.querySelector('#news-title-field')?.textContent?.trim() || '',
        excerpt: document.querySelector('#news-short-content-field')?.textContent?.trim() || '',
        content: ssrContent,
      };
      const editor = initMediumEditor(ssrItem, ssrIsEdit);
      bindMediumEditorActions(editor);
      bindNewsImageUploads();
      enhanceAdminSelects(document.querySelector('#cms-body') || document);
      bindNewsFormSubmit(ssrIsEdit, ssrItemId, editor);
      return;
    }

    let item = {
      title: '',
      excerpt: '',
      body: [''],
      date: new Date().toISOString().slice(0, 10),
      category: 'BANK INDONESIA',
      category_id: 4,
      image: '',
      author: 'Redaksi GenBI Jambi',
      editor: 'Editor GenBI Jambi',
      content: '',
      meta_title: '',
      meta_keyword: '',
      meta_description: '',
      contributor_pewarta: '',
      contributor_editor: '',
    };

    // Load from backend if editing
    if (isEdit && id > 0) {
      try {
        const res = await fetch(route('admin.newsShow', { id }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) {
          const json = await res.json();
          const d = json.data || {};
          item = {
            ...item,
            id: d.id || d.news_id,
            title: d.title || d.news_title || '',
            excerpt: d.excerpt || d.news_content_short || '',
            content: d.content || d.news_content || '',
            date: (d.date || d.published_at || '').slice(0, 10),
            category: d.category || d.category_name || '',
            category_id: d.category_id || 0,
            image: d.photo || d.image || '',
            author: d.contributor_pewarta || d.author || '',
            editor: d.contributor_editor || d.editor || '',
            meta_title: d.meta_title || '',
            meta_keyword: d.meta_keyword || '',
            meta_description: d.meta_description || '',
            contributor_pewarta: d.contributor_pewarta || '',
            contributor_editor: d.contributor_editor || '',
          };
        }
      } catch (e) {
        // Fallback to static data
        const staticItem = news.find((entry) => entry.id === id) || news[0];
        if (staticItem) item = { ...item, ...staticItem };
      }
    }

    // Load categories from backend
    let backendCategories = categories;
    try {
      const catRes = await fetch(route('admin.newsCategories'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (catRes.ok) {
        const catJson = await catRes.json();
        if (catJson.data && catJson.data.length > 0) {
          backendCategories = catJson.data.map(c => ({ id: c.category_id, name: c.category_name }));
        }
      }
    } catch (e) { /* use fallback */ }
    const body = renderShell(
      isEdit ? 'Edit News' : 'Add News',
      'Ruang tulis sekarang memakai Editor.js. Judul, paragraf, quote, list, dan gambar disusun sebagai blok seperti editor Medium.',
      `<a href="${adminUrl('news')}" class="btn btn-secondary">View All</a>`
    );

    body.innerHTML = `
      <form class="medium-editor-layout" id="news-editor-form">
        <main class="medium-editor-canvas">
          <div class="medium-editor-kicker">Story editor</div>
          <section class="story-main-block">
            <label for="news-title-field">News Title</label>
            <div id="news-title-field" class="story-title-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis judul berita...">${escape(item.title)}</div>
          </section>
          <section class="story-main-block">
            <label for="news-short-content-field">News Short Content</label>
            <div id="news-short-content-field" class="story-excerpt-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis caption atau ringkasan singkat untuk news list...">${escape(item.excerpt)}</div>
          </section>
          <div class="medium-editor-divider">
            <div class="medium-editor-kicker">News content</div>
          </div>
          <div id="news-editor" class="medium-editor-host"></div>
          <div id="editor-fallback" class="editor-fallback hidden">
            <article contenteditable="true">${item.content || (item.body || ['']).map((p) => `<p>${escape(p)}</p>`).join('')}</article>
          </div>
          <p class="medium-editor-help">Tekan <strong>Enter</strong> untuk membuat blok baru. Sisipkan gambar di antara paragraf lewat tombol plus Editor.js atau panel kanan.</p>
        </main>
        <aside class="editor-config-sidebar medium-config-sidebar">
          <section class="config-card medium-config-card">
            <h2>Quick Insert</h2>
            <p class="config-hint">Sisipkan gambar sebagai blok konten, bukan hanya featured image.</p>
            <button type="button" id="insert-paragraph" class="btn btn-secondary w-full">Tambah Paragraf</button>
            <button type="button" id="insert-heading" class="btn btn-secondary w-full mt-2">Tambah Heading</button>
            <button type="button" id="insert-quote" class="btn btn-secondary w-full mt-2">Tambah Quote</button>
            <label class="config-field mt-4"><span>Image URL</span><input id="inline-image-url" class="config-input" placeholder="https://..." /></label>
            <button type="button" id="insert-image-url" class="btn btn-primary w-full mt-2">Insert Image URL</button>
            <input id="inline-image-file" class="hidden" type="file" accept="image/*" />
            <button type="button" id="insert-image-file" class="btn btn-secondary w-full mt-2">Upload Image Block</button>
            <p id="news-inline-upload-status" class="config-hint mt-3 hidden">Mengupload gambar...</p>
          </section>
          <section class="config-card medium-config-card">
            <h2>Publishing</h2>
            ${control('News Publish Date', `<input class="config-input" type="date" value="${dateInput(item.date)}" />`)}
            ${control('Category', selectControl({ id: 'news-category-select', value: item.category_id || backendCategories.find((cat) => cat.name === item.category)?.id || '', options: backendCategories.map((cat) => ({ value: cat.id, label: cat.name })) }))}
            ${control('Comment', selectControl({ id: 'news-comment-select', value: 'On', options: ['On', 'Off'] }))}
          </section>
          <section class="config-card medium-config-card">
            <h2>Featured Photo</h2>
            ${item.image ? `<img src="${item.image}" class="config-preview" alt="Featured photo" />` : '<div class="config-empty">Belum ada foto utama</div>'}
            <input id="news-photo-file" class="hidden" type="file" accept="image/*" />
            <button type="button" id="news-photo-upload-btn" class="btn btn-secondary w-full mt-2">Upload Featured Photo</button>
            <input class="config-input mt-2" id="news-photo-url" value="${escape(item.image)}" placeholder="URL foto utama" />
          </section>
          <section class="config-card medium-config-card">
            <h2>Contributors</h2>
            ${control('Pewarta', `<input class="config-input" id="news-pewarta" value="${escape(item.contributor_pewarta || item.author || '')}" />`)}
            ${control('Editor', `<input class="config-input" id="news-editor-name" value="${escape(item.contributor_editor || item.editor || '')}" />`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>SEO Information</h2>
            ${control('Meta Title', `<input class="config-input" id="news-meta-title" value="${escape(item.meta_title || item.title)}" />`)}
            ${control('Meta Keywords', `<textarea class="config-input" id="news-meta-keyword" rows="4">${escape(item.meta_keyword || `${item.category}, GenBI Jambi, berita Jambi`)}</textarea>`)}
            ${control('Meta Description', `<textarea class="config-input" id="news-meta-desc" rows="5">${escape(item.meta_description || item.excerpt)}</textarea>`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Status</h2>
            ${control('Publish Status', selectControl({ id: 'news-status', value: item.status || 'draft', options: [{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }, { value: 'archived', label: 'Archived' }] }))}
          </section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update News' : 'Submit News'}</button>
        </aside>
      </form>
    `;

    const editor = initMediumEditor(item, isEdit);
    bindMediumEditorActions(editor);
    bindNewsImageUploads();
    enhanceAdminSelects(body);

    document.querySelector('#news-editor-form').addEventListener('submit', async (event) => {
      event.preventDefault();
      let editorContent = '';
      if (editor?.save) {
        try {
          const outputData = await editor.save();
          editorContent = blocksToNewsHtml(outputData.blocks);
        } catch (error) {
          // Fallback: get content from fallback editor
          const fallbackEl = document.querySelector('#editor-fallback article');
          if (fallbackEl) editorContent = fallbackEl.innerHTML;
        }
      } else {
        const fallbackEl = document.querySelector('#editor-fallback article');
        if (fallbackEl) editorContent = fallbackEl.innerHTML;
      }

      const ok = await Admin.showConfirm({
        title: isEdit ? 'Update berita?' : 'Submit berita?',
        message: isEdit ? 'Berita akan diperbarui di database.' : 'Berita baru akan disimpan ke database.',
        confirmText: isEdit ? 'Update' : 'Submit'
      });
      if (!ok) return;

      // Gather form data
      const payload = {
        title: document.querySelector('#news-title-field')?.textContent?.trim() || '',
        excerpt: document.querySelector('#news-short-content-field')?.textContent?.trim() || '',
        content: editorContent || item.content || '',
        date: document.querySelector('[type="date"]')?.value || '',
        category_id: Number(document.querySelector('#news-category-select')?.value) || 0,
        status: document.querySelector('#news-status')?.value || 'draft',
        contributor_pewarta: document.querySelector('#news-pewarta')?.value?.trim() || '',
        contributor_editor: document.querySelector('#news-editor-name')?.value?.trim() || '',
        meta_title: document.querySelector('#news-meta-title')?.value?.trim() || '',
        meta_keyword: document.querySelector('#news-meta-keyword')?.value?.trim() || '',
        meta_description: document.querySelector('#news-meta-desc')?.value?.trim() || '',
        photo: document.querySelector('#news-photo-url')?.value?.trim() || '',
      };

      const token = getAdminCsrfToken();
      const url = isEdit ? route('admin.newsUpdate', { id }) : route('admin.newsStore');

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: JSON.stringify({ ...payload, _csrf_token: token })
        });
        const result = await res.json();
        if (res.ok) {
          Admin.showToast(isEdit ? 'Berita berhasil diperbarui.' : 'Berita berhasil ditambahkan.');
          if (!isEdit && result.data?.id) {
            // Redirect to edit page for the new article
            setTimeout(() => { window.location.href = `${adminUrl('news-edit')}?id=${result.data.id}`; }, 1200);
          }
        } else {
          Admin.showToast(result.error || 'Gagal menyimpan berita.');
        }
      } catch (e) {
        console.error('Fetch error details:', e);
        Admin.showToast('Gagal menyimpan berita. Error: ' + (e.message || 'Koneksi'));
      }
    });
  }

  function bindNewsFormSubmit(isEdit, id, editor) {
    document.querySelector('#news-editor-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      let editorContent = '';
      if (editor?.save) {
        try {
          const outputData = await editor.save();
          editorContent = blocksToNewsHtml(outputData.blocks);
        } catch (error) {
          const fallbackEl = document.querySelector('#editor-fallback article');
          if (fallbackEl) editorContent = fallbackEl.innerHTML;
        }
      } else {
        const fallbackEl = document.querySelector('#editor-fallback article');
        if (fallbackEl) editorContent = fallbackEl.innerHTML;
      }

      const ok = await Admin.showConfirm({
        title: isEdit ? 'Update berita?' : 'Submit berita?',
        message: isEdit ? 'Berita akan diperbarui di database.' : 'Berita baru akan disimpan ke database.',
        confirmText: isEdit ? 'Update' : 'Submit'
      });
      if (!ok) return;

      const payload = {
        title: document.querySelector('#news-title-field')?.textContent?.trim() || '',
        excerpt: document.querySelector('#news-short-content-field')?.textContent?.trim() || '',
        content: editorContent || '',
        date: document.querySelector('[type="date"]')?.value || '',
        category_id: Number(document.querySelector('#news-category-select')?.value) || 0,
        status: document.querySelector('#news-status')?.value || 'draft',
        contributor_pewarta: document.querySelector('#news-pewarta')?.value?.trim() || '',
        contributor_editor: document.querySelector('#news-editor-name')?.value?.trim() || '',
        meta_title: document.querySelector('#news-meta-title')?.value?.trim() || '',
        meta_keyword: document.querySelector('#news-meta-keyword')?.value?.trim() || '',
        meta_description: document.querySelector('#news-meta-desc')?.value?.trim() || '',
        photo: document.querySelector('#news-photo-url')?.value?.trim() || '',
      };

      const token = getAdminCsrfToken();
      const url = isEdit ? route('admin.newsUpdate', { id }) : route('admin.newsStore');

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: JSON.stringify({ ...payload, _csrf_token: token })
        });
        const result = await res.json();
        if (res.ok) {
          Admin.showToast(isEdit ? 'Berita berhasil diperbarui.' : 'Berita berhasil ditambahkan.');
          if (!isEdit && result.data?.id) {
            setTimeout(() => { window.location.href = `${adminUrl('news-edit')}?id=${result.data.id}`; }, 1200);
          }
        } else {
          Admin.showToast(result.error || 'Gagal menyimpan berita.');
        }
      } catch (e) {
        console.error('Fetch error details:', e);
        Admin.showToast('Gagal menyimpan berita. Error: ' + (e.message || 'Koneksi'));
      }
    });
  }

  function initMediumEditor(item, isEdit, options = {}) {
    const holderId = options.holderId || 'news-editor';
    const holder = document.querySelector(`#${holderId}`);
    const fallback = document.querySelector('#editor-fallback');
    if (!holder) return null;

    if (!window.EditorJS) {
      holder.classList.add('hidden');
      fallback?.classList.remove('hidden');
      Admin.showToast('Editor.js CDN belum termuat. Fallback editor aktif.');
      return null;
    }

    const initialBlocks = options.buildBlocks ? options.buildBlocks(item, isEdit) : buildNewsBlocks(item, isEdit);
    const tools = {};
    if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [1, 2, 3], defaultLevel: 2 } };
    const ListTool = window.EditorjsList || window.List;
    if (ListTool) tools.list = { class: ListTool, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
    if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Tulis kutipan...', captionPlaceholder: 'Sumber kutipan' } };
    const ImageToolClass = window.ImageTool || SimpleImageTool;
    if (ImageToolClass) {
      tools.image = {
        class: ImageToolClass,
        config: {
          uploader: {
            async uploadByFile(file) {
              try {
                Admin.showToast('Mengupload gambar...');
                const url = await uploadNewsImageFile(file);
                Admin.showToast('Gambar berhasil diupload.');
                return { success: 1, file: { url } };
              } catch (e) {
                Admin.showToast('Gagal upload gambar.');
                return { success: 0 };
              }
            },
            uploadByUrl(url) {
              return Promise.resolve({ success: 1, file: { url } });
            }
          }
        }
      };
    }

    if (options.extraTools) Object.assign(tools, options.extraTools);

    return new window.EditorJS({
      holder: holderId,
      autofocus: true,
      minHeight: 520,
      placeholder: options.placeholder || 'Mulai tulis berita. Tekan Tab atau klik plus untuk memilih blok.',
      tools,
      data: {
        time: Date.now(),
        blocks: initialBlocks
      }
    });
  }

  function buildNewsBlocks(item, isEdit) {
    const htmlBlocks = htmlToNewsBlocks(item.content || item.news_content || '');
    if (htmlBlocks.length) return htmlBlocks;

    return (item.body || []).filter(Boolean).map((paragraph) => ({
      type: 'paragraph',
      data: { text: paragraph },
    }));
  }

  function htmlToNewsBlocks(html = '') {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = String(html || '').trim();
    const blocks = [];
    const nodes = wrapper.children.length ? Array.from(wrapper.children) : [];

    if (!nodes.length && wrapper.textContent.trim()) {
      return [{ type: 'paragraph', data: { text: wrapper.textContent.trim() } }];
    }

    nodes.forEach((node) => {
      const tag = node.tagName.toLowerCase();
      if (tag === 'p') blocks.push({ type: 'paragraph', data: { text: node.innerHTML.trim() } });
      else if (/^h[1-6]$/.test(tag)) blocks.push({ type: 'header', data: { text: node.innerHTML.trim(), level: Number(tag.slice(1)) } });
      else if (tag === 'ul' || tag === 'ol') blocks.push({ type: 'list', data: { style: tag === 'ol' ? 'ordered' : 'unordered', items: Array.from(node.querySelectorAll(':scope > li')).map((li) => li.innerHTML.trim()) } });
      else if (tag === 'blockquote') blocks.push({ type: 'quote', data: { text: node.querySelector('p')?.innerHTML.trim() || node.textContent.trim(), caption: node.querySelector('cite')?.textContent.trim() || '', alignment: 'left' } });
      else if (tag === 'figure') {
        const image = node.querySelector('img');
        if (image?.getAttribute('src')) blocks.push({ type: 'image', data: { file: { url: image.getAttribute('src') }, caption: node.querySelector('figcaption')?.textContent.trim() || image.getAttribute('alt') || '', withBorder: false, withBackground: false, stretched: false } });
      } else if (tag === 'img' && node.getAttribute('src')) {
        blocks.push({ type: 'image', data: { file: { url: node.getAttribute('src') }, caption: node.getAttribute('alt') || '', withBorder: false, withBackground: false, stretched: false } });
      } else if (node.textContent.trim()) {
        blocks.push({ type: 'paragraph', data: { text: node.innerHTML.trim() } });
      }
    });

    return blocks;
  }

  function blocksToNewsHtml(blocks = []) {
    return blocks.map((block) => {
      if (block.type === 'paragraph') return block.data.text ? `<p>${block.data.text}</p>` : '';
      if (block.type === 'header') {
        const level = Math.min(Math.max(Number(block.data.level) || 2, 1), 6);
        return block.data.text ? `<h${level}>${block.data.text}</h${level}>` : '';
      }
      if (block.type === 'list') {
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
        const renderListItems = (items) => items.map((item) => {
          if (typeof item === 'string') return `<li>${item}</li>`;
          if (typeof item === 'object' && item !== null) {
            const content = item.content || '';
            const sublist = item.items && item.items.length > 0 ? `<${tag}>${renderListItems(item.items)}</${tag}>` : '';
            return `<li>${content}${sublist}</li>`;
          }
          return `<li>${item}</li>`;
        }).join('');
        return `<${tag}>${renderListItems(block.data.items || [])}</${tag}>`;
      }
      if (block.type === 'quote') return block.data.text ? `<blockquote><p>${block.data.text}</p>${block.data.caption ? `<cite>${block.data.caption}</cite>` : ''}</blockquote>` : '';
      if (block.type === 'image') {
        const src = block.data.file?.url || block.data.url || '';
        if (!src) return '';
        return `<figure><img src="${escape(src)}" alt="${escape(block.data.caption || '')}" />${block.data.caption ? `<figcaption>${escape(block.data.caption)}</figcaption>` : ''}</figure>`;
      }
      return block.data.text ? `<p>${block.data.text}</p>` : '';
    }).filter(Boolean).join('\n');
  }

  class SimpleImageTool {
    static get toolbox() {
      return { title: 'Image', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 16.5 4.72-4.72a1.5 1.5 0 0 1 2.12 0l2.16 2.16 1.22-1.22a1.5 1.5 0 0 1 2.12 0l4.16 4.16M8.25 8.25h.01"/></svg>' };
    }
    constructor({ data, config }) { this.data = data || { file: { url: '' }, caption: '' }; this.config = config || {}; this.urlInput = null; this.captionInput = null; this.fileInput = null; }
    render() { const wrapper = document.createElement('div'); wrapper.className = 'rounded-2xl border border-neutral-900/10 bg-white p-4'; wrapper.innerHTML = `<label class="mb-3 block text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">Image URL</label><input type="text" class="config-input w-full" placeholder="https://..." value="${escape(this.data.file?.url || this.data.url || '')}" /><label class="mb-3 mt-4 block text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">Caption</label><input type="text" class="config-input w-full" placeholder="Caption gambar (opsional)" value="${escape(this.data.caption || '')}" /><input type="file" class="hidden" accept="image/*" /><button type="button" class="btn btn-secondary mt-3">Upload Image</button>`; this.urlInput = wrapper.querySelectorAll('input')[0]; this.captionInput = wrapper.querySelectorAll('input')[1]; this.fileInput = wrapper.querySelector('input[type="file"]'); wrapper.querySelector('button')?.addEventListener('click', () => this.fileInput?.click()); this.fileInput?.addEventListener('change', async () => { const file = this.fileInput?.files?.[0]; if (!file || !this.config.uploader?.uploadByFile) return; const result = await this.config.uploader.uploadByFile(file); const url = result?.file?.url || ''; if (url && this.urlInput) this.urlInput.value = url; this.fileInput.value = ''; }); return wrapper; }
    save() { return { file: { url: this.urlInput?.value?.trim() || '' }, caption: this.captionInput?.value?.trim() || '', withBorder: false, withBackground: false, stretched: false }; }
    validate(savedData) { return Boolean(savedData?.file?.url || savedData?.url); }
  }

  function bindNewsImageUploads() {
    bindNewsImageUpload('#news-photo-upload-btn', '#news-photo-file', '#news-photo-url');
  }

  function bindNewsImageUpload(buttonSelector, fileSelector, urlSelector) {
    const button = document.querySelector(buttonSelector);
    const fileInput = document.querySelector(fileSelector);
    const urlInput = document.querySelector(urlSelector);

    button?.addEventListener('click', () => fileInput?.click());
    fileInput?.addEventListener('change', async () => {
      const file = fileInput.files?.[0];
      if (!file) return;

      try {
        Admin.showToast('Mengupload gambar...');
        const url = await uploadNewsImageFile(file);

        if (urlInput) urlInput.value = url;
        if (buttonSelector === '#news-photo-upload-btn') {
          let preview = document.querySelector('.config-preview');
          const empty = document.querySelector('.config-empty');
          if (!preview && empty) {
            preview = document.createElement('img');
            preview.className = 'config-preview';
            preview.alt = 'Featured photo';
            empty.replaceWith(preview);
          }
          if (preview) preview.src = url;
        }
        Admin.showToast('Gambar berhasil diupload.');
      } catch (err) {
        Admin.showToast(err.message || 'Gagal upload gambar.');
      } finally {
        fileInput.value = '';
      }
    });
  }

  function bindMediumEditorActions(editor) {
    const insertParagraph = document.querySelector('#insert-paragraph');
    const insertHeading = document.querySelector('#insert-heading');
    const insertQuote = document.querySelector('#insert-quote');
    const imageUrl = document.querySelector('#inline-image-url');
    const insertImageUrl = document.querySelector('#insert-image-url');
    const insertImageFile = document.querySelector('#insert-image-file');
    const imageFile = document.querySelector('#inline-image-file');

    insertParagraph?.addEventListener('click', () => insertEditorBlock(editor, 'paragraph', { text: '' }));
    insertHeading?.addEventListener('click', () => insertEditorBlock(editor, 'header', { text: 'Subjudul baru', level: 2 }));
    insertQuote?.addEventListener('click', () => insertEditorBlock(editor, 'quote', { text: 'Kutipan penting...', caption: '', alignment: 'left' }));
    insertImageUrl?.addEventListener('click', () => {
      const url = imageUrl?.value.trim();
      if (!url) return Admin.showToast('Masukkan URL gambar terlebih dahulu.');
      insertEditorBlock(editor, 'image', { file: { url }, caption: '', withBorder: false, withBackground: false, stretched: false });
      imageUrl.value = '';
    });
    insertImageFile?.addEventListener('click', () => imageFile?.click());
    imageFile?.addEventListener('change', async () => {
      const file = imageFile.files?.[0];
      if (!file) return;
      const status = document.querySelector('#news-inline-upload-status');
      const buttonText = insertImageFile?.textContent || 'Upload Image Block';
      try {
        if (status) status.classList.remove('hidden');
        if (insertImageFile) {
          insertImageFile.disabled = true;
          insertImageFile.textContent = 'Uploading...';
        }
        Admin.showToast('Mengupload gambar...');
        const url = await uploadNewsImageFile(file);
        insertEditorBlock(editor, 'image', { file: { url }, caption: file.name, withBorder: false, withBackground: false, stretched: false });
        Admin.showToast('Gambar berhasil diupload dan ditambahkan.');
      } catch (error) {
        Admin.showToast(error.message || 'Gagal upload gambar.');
      } finally {
        if (status) status.classList.add('hidden');
        if (insertImageFile) {
          insertImageFile.disabled = false;
          insertImageFile.textContent = buttonText;
        }
        imageFile.value = '';
      }
    });
  }

  async function uploadNewsImageFile(file) {
    const token = getAdminCsrfToken();
    const formData = new FormData();
    formData.append('image', file);
    formData.append('_csrf_token', token);

    const res = await fetch(route('admin.newsUpload'), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
      credentials: 'same-origin',
      body: formData,
    });
    const json = await res.json();
    const url = json.data?.url || '';
    if (!res.ok || !url) throw new Error(json.error || 'Upload gagal. Pastikan file berupa gambar (max 5MB).');
    return url;
  }

  function getAdminCsrfToken() {
    return (API && API.getCsrfToken && API.getCsrfToken()) || document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  async function insertEditorBlock(editor, type, data) {
    if (!editor?.blocks?.insert) return Admin.showToast('Editor belum siap.');
    try { if (editor.isReady) await editor.isReady; editor.blocks.insert(type, data); } catch (error) { Admin.showToast('Gagal menambahkan blok. Coba klik area editor lalu ulangi.'); }
  }

  async function renderCommentSetup() {
    if (document.querySelector('#admin-comment-list[data-ssr="true"]')) {
      bindSsrCommentActions();
      return;
    }
    const body = renderShell('News Comment Moderation', 'Kelola komentar pembaca. Moderator bisa meninjau, menyetujui, menolak, atau menghapus komentar sebelum tampil di halaman publik.');
    const state = { comments: [], query: '', status: 'Semua' };
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div id="comment-dashboard" class="comment-dashboard"></div>
        <div class="cms-toolbar mt-6">
          <label class="cms-search">${Admin.icon('search')}<input id="comment-search" placeholder="Cari nama, artikel, email, atau isi komentar..." /></label>
          <div id="comment-status-filter" class="view-toggle" role="group" aria-label="Filter komentar"></div>
        </div>
        <div id="comment-list" class="comment-moderation-list mt-6">
          <div class="rounded-2xl border border-neutral-900/10 bg-white p-6 text-sm text-neutral-600">Memuat komentar...</div>
        </div>
      </section>
    `;

    state.comments = await API.getAdminComments();
    renderCommentDashboard(state);
    renderCommentStatusFilter(state);
    renderCommentList(state);

    document.querySelector('#comment-search').addEventListener('input', (event) => {
      state.query = event.target.value;
      renderCommentList(state);
    });
  }

  function renderCommentDashboard(state) {
    const stats = Core.getCommentModerationStats(state.comments);
    document.querySelector('#comment-dashboard').innerHTML = `
      <article><span>Pending</span><strong>${stats.pending}</strong><p>Menunggu review admin.</p></article>
      <article><span>Approved</span><strong>${stats.approved}</strong><p>Komentar tampil di publik.</p></article>
      <article><span>Rejected/Flagged</span><strong>${stats.rejected + stats.flagged}</strong><p>Ditolak atau perlu pemeriksaan khusus.</p></article>
    `;
  }

  function renderCommentStatusFilter(state) {
    const root = document.querySelector('#comment-status-filter');
    const statuses = ['Semua', 'Pending', 'Approved', 'Rejected', 'Flagged'];
    root.innerHTML = statuses.map((status) => `<button type="button" class="${state.status === status ? 'is-active' : ''}" data-status="${status}">${status}</button>`).join('');
    root.querySelectorAll('button').forEach((button) => {
      button.addEventListener('click', () => {
        state.status = button.dataset.status;
        renderCommentStatusFilter(state);
        renderCommentList(state);
      });
    });
  }

  function renderCommentList(state) {
    const root = document.querySelector('#comment-list');
    const filtered = Core.filterAdminComments(state.comments, { query: state.query, status: state.status });
    root.innerHTML = filtered.length ? filtered.map((item) => `
      <article class="comment-moderation-card">
        <div class="comment-moderation-main">
          <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800"><span>${escape(item.article)}</span><span>•</span><span>${escape(item.date)}</span></div>
          <h2>${escape(item.name)}</h2>
          <p class="text-sm text-[rgb(var(--text-secondary))]">${escape(item.email)}</p>
          ${item.parentId ? `<p class="mt-2 rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-900 dark-theme-note">Balasan untuk ${item.parentName ? escape(item.parentName) : 'komentar'}: “${escape(item.parentExcerpt || 'Komentar induk')}”</p>` : ''}
          <p class="comment-moderation-text">${escape(item.text)}</p>
        </div>
        <aside class="comment-moderation-side">
          <span class="comment-status-admin ${item.status.toLowerCase()}">${item.status}</span>
          <button class="cms-action edit" data-action="approve" data-id="${item.id}">Approve</button>
          <button class="cms-action hold" data-action="reject" data-id="${item.id}">Reject</button>
          <button class="cms-action delete" data-action="delete" data-id="${item.id}">Delete</button>
        </aside>
      </article>
    `).join('') : '<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-[rgb(var(--text-secondary))]">Tidak ada komentar yang cocok dengan filter.</div>';

    root.querySelectorAll('[data-action]').forEach((button) => {
      button.addEventListener('click', () => handleCommentAction(state, button.dataset.id, button.dataset.action));
    });
  }

  async function handleCommentAction(state, id, action) {
    const labels = { approve: 'menyetujui', reject: 'menolak', delete: 'menghapus' };
    if (action !== 'approve') {
      const ok = await Admin.showConfirm({
        title: action === 'delete' ? 'Hapus komentar?' : 'Tolak komentar?',
        message: `Admin akan ${labels[action]} komentar ini.`,
        confirmText: action === 'delete' ? 'Hapus' : 'Tolak',
        danger: true,
      });
      if (!ok) return;
    }

    await API.moderateComment(id, action);
    if (action === 'delete') {
      state.comments = state.comments.filter((comment) => String(comment.id) !== String(id));
    } else {
      const nextStatus = action === 'approve' ? 'Approved' : 'Rejected';
      state.comments = state.comments.map((comment) => String(comment.id) === String(id) ? { ...comment, status: nextStatus } : comment);
    }
    renderCommentDashboard(state);
    renderCommentList(state);
    Admin.showToast(`Komentar berhasil ${labels[action]} pada mode integrasi.`);
  }

  function bindSsrPrototype() {
    const root = document.querySelector('#admin-prototype-root[data-ssr="true"]');
    if (!root) return;
    enhanceAdminSelects(root);
    const prototype = root.dataset.prototypePage || '';
    if (prototype === 'language') {
      root.querySelector('#save-language')?.addEventListener('click', () => Admin.showToast('Language data masih berada pada mode prototipe.'));
      return;
    }
    if (prototype === 'slider-add') {
      bindLiveSliderForm(Number(root.querySelector('#slider-form')?.dataset.slot || 1));
      return;
    }
    if (prototype === 'social-media' || prototype === 'social_media' || prototype === 'social') {
      root.querySelector('#save-social')?.addEventListener('click', () => Admin.showToast('Social media masih berada pada mode prototipe.'));
      return;
    }
    if (prototype === 'faq-add') {
      bindSimpleSubmit('#faq-form', 'Submit FAQ?', 'FAQ ditambahkan pada mode simulasi.');
      return;
    }
    if (prototype === 'why-choose-add' || prototype === 'why_choose-add') {
      bindSimpleSubmit('#why-form', 'Submit item edukasi?', 'Item edukasi ditambahkan pada mode simulasi.');
    }
  }

  function bindSsrCommentActions() {
    document.querySelectorAll('[data-comment-action]').forEach((button) => {
      button.addEventListener('click', async () => {
        const action = button.dataset.commentAction || '';
        const id = button.dataset.id || '';
        const labels = { approve: 'menyetujui', reject: 'menolak', delete: 'menghapus' };
        if (!id || !labels[action]) return;
        if (action !== 'approve') {
          const ok = await Admin.showConfirm({
            title: action === 'delete' ? 'Hapus komentar?' : 'Tolak komentar?',
            message: `Admin akan ${labels[action]} komentar ini.`,
            confirmText: action === 'delete' ? 'Hapus' : 'Tolak',
            danger: true,
          });
          if (!ok) return;
        }
        const result = await API.moderateComment(id, action);
        if (result?.ok === false) {
          Admin.showToast('Gagal memperbarui komentar.');
          return;
        }
        window.location.reload();
      });
    });
  }

  async function renderCommentSettings() {
    const body = renderShell('Comment Settings', 'Atur perilaku komentar publik secara global, termasuk voting, balasan, moderasi, dan rate limit.');
    body.innerHTML = '<div class="admin-card p-6 text-sm text-neutral-600">Memuat pengaturan komentar...</div>';
    const settings = await API.getCommentSettings();
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <form id="comment-setting-form" class="grid gap-4 md:grid-cols-2">
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Comments Enabled</span><select name="comments.enabled" class="config-input js-admin-custom-select"><option value="1" ${settings['comments.enabled'] !== false ? 'selected' : ''}>On</option><option value="0" ${settings['comments.enabled'] === false ? 'selected' : ''}>Off</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Voting Enabled</span><select name="comments.voting_enabled" class="config-input js-admin-custom-select"><option value="1" ${settings['comments.voting_enabled'] !== false ? 'selected' : ''}>On</option><option value="0" ${settings['comments.voting_enabled'] === false ? 'selected' : ''}>Off</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Replies Enabled</span><select name="comments.replies_enabled" class="config-input js-admin-custom-select"><option value="1" ${settings['comments.replies_enabled'] !== false ? 'selected' : ''}>On</option><option value="0" ${settings['comments.replies_enabled'] === false ? 'selected' : ''}>Off</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Replies Require Moderation</span><select name="comments.replies_require_moderation" class="config-input js-admin-custom-select"><option value="1" ${settings['comments.replies_require_moderation'] !== false ? 'selected' : ''}>On</option><option value="0" ${settings['comments.replies_require_moderation'] === false ? 'selected' : ''}>Off</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Max Reply Depth</span><input name="comments.max_reply_depth" type="number" min="1" max="10" class="config-input" value="${escape(settings['comments.max_reply_depth'] ?? 3)}"></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Root Sort</span><select name="comments.root_sort" class="config-input js-admin-custom-select"><option value="newest_first" ${(settings['comments.root_sort'] || 'newest_first') === 'newest_first' ? 'selected' : ''}>Newest First</option><option value="oldest_first" ${settings['comments.root_sort'] === 'oldest_first' ? 'selected' : ''}>Oldest First</option><option value="top_voted" ${settings['comments.root_sort'] === 'top_voted' ? 'selected' : ''}>Top Voted</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Reply Sort</span><select name="comments.reply_sort" class="config-input js-admin-custom-select"><option value="oldest_first" ${(settings['comments.reply_sort'] || 'oldest_first') === 'oldest_first' ? 'selected' : ''}>Oldest First</option><option value="newest_first" ${settings['comments.reply_sort'] === 'newest_first' ? 'selected' : ''}>Newest First</option><option value="top_voted" ${settings['comments.reply_sort'] === 'top_voted' ? 'selected' : ''}>Top Voted</option></select></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Comment Rate Limit / 15 min</span><input name="comments.rate_limit_per_ip_per_15min" type="number" min="1" max="500" class="config-input" value="${escape(settings['comments.rate_limit_per_ip_per_15min'] ?? 20)}"></label>
          <label class="grid gap-2 text-sm font-semibold text-neutral-700"><span>Vote Rate Limit / 15 min</span><input name="comments.vote_rate_limit_per_ip_per_15min" type="number" min="1" max="500" class="config-input" value="${escape(settings['comments.vote_rate_limit_per_ip_per_15min'] ?? 60)}"></label>
          <div class="md:col-span-2"><button type="submit" class="btn btn-primary">Simpan Pengaturan</button></div>
        </form>
      </section>
    `;
    enhanceAdminSelects(body);
    body.querySelector('#comment-setting-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const formData = new FormData(event.currentTarget);
      const payload = Object.fromEntries(formData.entries());
      await API.updateCommentSettings(payload);
      Admin.showToast('Pengaturan komentar berhasil disimpan.');
    });
  }

  async function renderEventList() {
    const ssrList = document.querySelector('#admin-event-list[data-ssr="true"]');
    if (ssrList) {
      bindAgendaActions();
      return;
    }
    const body = renderShell('Agenda', 'Agenda komunitas tampil dari data `tbl_event` yang sama dengan halaman publik dan section Agenda Utama di landing page.', `<a href="${adminUrl('event-add')}" class="btn btn-primary">Add Agenda</a>`);
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data agenda...</div>';
    let items = [];
    try {
      const res = await fetch(route('admin.events'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (res.ok) {
        const json = await res.json();
        items = (json.data || []).map((item) => Core.normalizeEvent(item));
      }
    } catch (e) {
      items = (publicEvents || []).map((item) => ({ id: item.id, title: item.title, excerpt: item.description || '', start_date: item.date || '-', end_date: item.date || '-', location: '', status: 'Fallback' }));
    }
    body.innerHTML = `<section class="admin-card p-4 md:p-6">${renderSearchToolbar('Agenda')}<div class="admin-responsive-table mt-5"><table class="cms-table"><thead><tr><th>SL</th><th>Agenda</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Action</th></tr></thead><tbody id="agenda-tbody">${renderEventRows(items)}</tbody></table></div></section>`;
    body.querySelector('.cms-search input')?.addEventListener('input', (event) => {
      const query = String(event.target.value || '').toLowerCase();
      const filtered = items.filter((item) => [item.title, item.excerpt, item.location].join(' ').toLowerCase().includes(query));
      const tbody = document.querySelector('#agenda-tbody');
      if (tbody) tbody.innerHTML = renderEventRows(filtered);
      bindAgendaActions();
    });
    bindAgendaActions();
  }

  function renderEventRows(items) {
    if (!items.length) return '<tr><td colspan="6" class="py-8 text-center text-neutral-500">Belum ada agenda.</td></tr>';
    return items.map((item, index) => `<tr><td>${index + 1}</td><td><strong>${escape(item.title || '')}</strong><p class="mt-1 text-xs text-neutral-500">${escape(item.excerpt || '')}</p></td><td>${escape(item.start_date || '-')}</td><td>${escape(item.end_date || '-')}</td><td><span class="cms-pill muted">${escape(item.status || '-')}</span></td><td><div class="flex gap-2"><a href="${adminUrl('event-edit')}?id=${item.id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-agenda-delete="${item.id}">Delete</button></div></td></tr>`).join('');
  }

  function bindAgendaActions() {
    document.querySelectorAll('[data-agenda-delete]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.agendaDelete);
        const ok = await Admin.showConfirm({ title: 'Hapus agenda?', message: 'Agenda akan dihapus dari database dan landing page.', confirmText: 'Delete', danger: true });
        if (!ok) return;
        const res = await fetch(route('admin.eventDelete', { id }), { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() }, credentials: 'same-origin', body: JSON.stringify({ _csrf_token: getAdminCsrfToken() }) });
        if (res.ok) {
          Admin.showToast('Agenda berhasil dihapus.');
          const ssrList = document.querySelector('#admin-event-list[data-ssr="true"]');
          if (ssrList) button.closest('tr')?.remove();
          else renderEventList();
        } else { Admin.showToast('Gagal menghapus agenda.'); }
      });
    });
  }

  class MapEmbedTool {
    static get toolbox() {
      return {
        title: 'Embed Map',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19.5 3.75 17.25V4.5L9 6.75m0 12.75 6-2.25m-6 2.25V6.75m6 10.5 5.25 2.25V6.75L15 4.5m0 12.75V4.5"/></svg>'
      };
    }
    constructor({ data }) { this.data = data || { url: '', caption: '' }; this.urlInput = null; this.captionInput = null; }
    render() {
      const wrapper = document.createElement('div');
      wrapper.className = 'rounded-2xl border border-neutral-900/10 bg-white p-4';
      wrapper.innerHTML = `<label class="mb-3 block text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">Google Maps Embed</label><input type="text" class="config-input w-full" placeholder="Paste iframe atau https://www.google.com/maps/embed?..." value="${escape(this.data.url || '')}" /><label class="mb-3 mt-4 block text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">Caption</label><input type="text" class="config-input w-full" placeholder="Caption lokasi (opsional)" value="${escape(this.data.caption || '')}" />`;
      this.urlInput = wrapper.querySelectorAll('input')[0];
      this.captionInput = wrapper.querySelectorAll('input')[1];
      return wrapper;
    }
    save() { return { url: extractMapEmbedUrl(this.urlInput?.value || ''), caption: this.captionInput?.value?.trim() || '' }; }
    validate(savedData) { return Boolean(extractMapEmbedUrl(savedData?.url || '')); }
  }

  function extractMapEmbedUrl(value = '') { const input = String(value || '').trim(); if (!input) return ''; const iframeMatch = input.match(/<iframe[^>]+src=["']([^"']+)["']/i); const candidate = (iframeMatch?.[1] || input).trim(); return /^https:\/\/www\.google\.com\/maps\/embed\?/i.test(candidate) ? candidate : ''; }

  function initEventBlockEditor(item) {
    const holder = document.querySelector('#event-editor');
    const fallback = document.querySelector('#editor-fallback');
    if (!holder) return null;
    if (!window.EditorJS) {
      holder.classList.add('hidden');
      fallback?.classList.remove('hidden');
      Admin.showToast('Editor.js CDN belum termuat. Fallback editor aktif.');
      return null;
    }
    const tools = {};
    if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [1, 2, 3], defaultLevel: 2 } };
    const ListTool = window.EditorjsList || window.List;
    if (ListTool) tools.list = { class: ListTool, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
    if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Tulis kutipan...', captionPlaceholder: 'Sumber kutipan' } };
    if (window.ImageTool) tools.image = { class: window.ImageTool, config: { uploader: { async uploadByFile(file) { try { Admin.showToast('Mengupload gambar agenda...'); const url = await uploadNewsImageFile(file); Admin.showToast('Gambar agenda berhasil diupload.'); return { success: 1, file: { url } }; } catch (e) { Admin.showToast('Gagal upload gambar agenda.'); return { success: 0 }; } }, uploadByUrl(url) { return Promise.resolve({ success: 1, file: { url } }); } } } };
    tools.embedMap = { class: MapEmbedTool, inlineToolbar: false };
    return new window.EditorJS({ holder: 'event-editor', autofocus: true, minHeight: 520, placeholder: 'Mulai tulis agenda. Tekan Tab atau klik plus untuk memilih blok.', tools, data: { time: Date.now(), blocks: buildEventBlocks(item) } });
  }

  function buildEventBlocks(item = {}) {
    const htmlBlocks = htmlToEventBlocks(item.content || item.event_content || '');
    if (htmlBlocks.length) return htmlBlocks;
    return [];
  }

  function htmlToEventBlocks(html = '') {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = String(html || '').trim();
    const blocks = [];
    const nodes = wrapper.children.length ? Array.from(wrapper.children) : [];
    if (!nodes.length && wrapper.textContent.trim()) return [{ type: 'paragraph', data: { text: wrapper.textContent.trim() } }];
    nodes.forEach((node) => {
      const tag = node.tagName.toLowerCase();
      if (tag === 'p') blocks.push({ type: 'paragraph', data: { text: node.innerHTML.trim() } });
      else if (/^h[1-6]$/.test(tag)) blocks.push({ type: 'header', data: { text: node.innerHTML.trim(), level: Number(tag.slice(1)) } });
      else if (tag === 'ul' || tag === 'ol') blocks.push({ type: 'list', data: { style: tag === 'ol' ? 'ordered' : 'unordered', items: Array.from(node.querySelectorAll(':scope > li')).map((li) => li.innerHTML.trim()) } });
      else if (tag === 'blockquote') blocks.push({ type: 'quote', data: { text: node.querySelector('p')?.innerHTML.trim() || node.textContent.trim(), caption: node.querySelector('cite')?.textContent.trim() || '', alignment: 'left' } });
      else if (tag === 'figure') { const image = node.querySelector('img'); if (image?.getAttribute('src')) blocks.push({ type: 'image', data: { file: { url: image.getAttribute('src') }, caption: node.querySelector('figcaption')?.textContent.trim() || image.getAttribute('alt') || '', withBorder: false, withBackground: false, stretched: false } }); }
      else if (tag === 'img' && node.getAttribute('src')) blocks.push({ type: 'image', data: { file: { url: node.getAttribute('src') }, caption: node.getAttribute('alt') || '', withBorder: false, withBackground: false, stretched: false } });
      else if (tag === 'div' && node.dataset.blockType === 'map') { const iframe = node.querySelector('iframe'); blocks.push({ type: 'embedMap', data: { url: extractMapEmbedUrl(iframe?.getAttribute('src') || node.dataset.mapUrl || ''), caption: node.dataset.caption || node.querySelector('p')?.textContent.trim() || '' } }); }
      else if (tag === 'iframe' && /(google\.com\/maps|maps\.google\.com|googleusercontent\.com\/maps)/i.test(node.getAttribute('src') || '')) blocks.push({ type: 'embedMap', data: { url: extractMapEmbedUrl(node.getAttribute('src') || ''), caption: '' } });
      else if (node.textContent.trim()) blocks.push({ type: 'paragraph', data: { text: node.innerHTML.trim() } });
    });
    return blocks;
  }

  function blocksToEventHtml(blocks = []) {
    return blocks.map((block) => {
      if (block.type === 'paragraph') return block.data.text ? `<p>${block.data.text}</p>` : '';
      if (block.type === 'header') { const level = Math.min(Math.max(Number(block.data.level) || 2, 1), 6); return block.data.text ? `<h${level}>${block.data.text}</h${level}>` : ''; }
      if (block.type === 'list') { const tag = block.data.style === 'ordered' ? 'ol' : 'ul'; const renderListItems = (items) => items.map((item) => { if (typeof item === 'string') return `<li>${item}</li>`; if (typeof item === 'object' && item !== null) { const content = item.content || ''; const sublist = item.items && item.items.length > 0 ? `<${tag}>${renderListItems(item.items)}</${tag}>` : ''; return `<li>${content}${sublist}</li>`; } return `<li>${item}</li>`; }).join(''); return `<${tag}>${renderListItems(block.data.items || [])}</${tag}>`; }
      if (block.type === 'quote') return block.data.text ? `<blockquote><p>${block.data.text}</p>${block.data.caption ? `<cite>${block.data.caption}</cite>` : ''}</blockquote>` : '';
      if (block.type === 'image') { const src = block.data.file?.url || block.data.url || ''; if (!src) return ''; return `<figure><img src="${escape(src)}" alt="${escape(block.data.caption || '')}" />${block.data.caption ? `<figcaption>${escape(block.data.caption)}</figcaption>` : ''}</figure>`; }
      if (block.type === 'map' || block.type === 'embedMap') { const url = extractMapEmbedUrl(block.data.url || ''); if (!url) return ''; const caption = String(block.data.caption || '').trim(); return `<div class="event-map-block" data-block-type="map" data-map-url="${escape(url)}" data-caption="${escape(caption)}"><iframe src="${escape(url)}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>${caption ? `<p>${escape(caption)}</p>` : ''}</div>`; }
      return block.data.text ? `<p>${block.data.text}</p>` : '';
    }).filter(Boolean).join('\n');
  }



  async function renderEventEditor() {
    const ssrForm = document.querySelector('#event-form[data-ssr="true"]');
    if (ssrForm) {
      const eventId = Number(ssrForm.dataset.itemId) || 0;
      const isEdit = ssrForm.dataset.edit === '1';
      let item = {};
      try { item = JSON.parse(ssrForm.dataset.item || '{}'); } catch { item = {}; }
      const eventEditor = initMediumEditor(item, isEdit, { buildBlocks: buildEventBlocks, placeholder: 'Mulai tulis agenda. Tekan Tab atau klik plus untuk memilih blok.', extraTools: { embedMap: { class: MapEmbedTool, inlineToolbar: false } } });
      bindSsrEventForm(ssrForm, eventEditor, item, isEdit, eventId);
      return;
    }
    const eventId = Number(new URLSearchParams(location.search).get('id')) || 0;
    const isEdit = page === 'event-edit' || eventId > 0;
    let item = null;
    if (isEdit && eventId > 0) {
      try {
        const res = await fetch(route('admin.eventShow', { id: eventId }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) { const json = await res.json(); item = Core.normalizeEvent(json.data || {}); }
      } catch (e) { item = null; }
    }
    const body = renderShell(isEdit ? 'Edit Agenda' : 'Add Agenda', 'Ruang tulis agenda sekarang mengikuti struktur story editor seperti News, dengan tambahan blok map untuk lokasi.', `<a href="${adminUrl('event')}" class="btn btn-secondary">View All Agenda</a>`);
    body.innerHTML = `<form class="medium-editor-layout" id="event-form"><main class="medium-editor-canvas"><div class="medium-editor-kicker">Agenda editor</div><section class="story-main-block"><label for="event-title-field">Agenda Title</label><div id="event-title-field" class="story-title-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis judul agenda...">${escape(item?.title || '')}</div></section><section class="story-main-block"><label for="event-short-content-field">Agenda Short Content</label><div id="event-short-content-field" class="story-excerpt-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis ringkasan singkat untuk agenda list...">${escape(item?.excerpt || '')}</div></section><div class="medium-editor-divider"><div class="medium-editor-kicker">Agenda content</div></div><div id="news-editor" class="medium-editor-host"></div><div id="editor-fallback" class="editor-fallback hidden"><article contenteditable="true">${item?.content || ''}</article></div><p class="medium-editor-help">Tekan <strong>Enter</strong> untuk membuat blok baru. Tambahkan gambar dan map di antara paragraf lewat Editor.js atau panel kanan.</p></main><aside class="editor-config-sidebar medium-config-sidebar"><section class="config-card medium-config-card"><h2>Publishing</h2>${control('Agenda Start Date', `<input id="event-start-date" class="config-input" type="date" value="${escape(item?.start_date || '2026-05-05')}" />`)}${control('Agenda End Date', `<input id="event-end-date" class="config-input" type="date" value="${escape(item?.end_date || '2026-05-05')}" />`)}${control('Location', `<input id="event-location" class="config-input" value="${escape(item?.location || '')}" placeholder="Lokasi agenda..." />`)}${control('Primary Map URL', `<textarea id="event-map" class="config-input" rows="6" placeholder="Paste iframe Google Maps atau URL embed https://www.google.com/maps/embed?...">${escape(item?.map || '')}</textarea><p class="config-hint mt-2">Tempel kode iframe Google Maps lengkap. Sistem akan otomatis mengambil nilai <code>src</code>-nya.</p>`)}</section><section class="config-card medium-config-card"><h2>SEO Information</h2>${control('Meta Title', `<input id="event-meta-title" class="config-input" value="${escape(item?.meta_title || item?.title || '')}" />`)}${control('Meta Description', `<textarea id="event-meta-description" class="config-input" rows="5">${escape(item?.meta_description || item?.excerpt || '')}</textarea>`)}</section><button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update Agenda' : 'Submit Agenda'}</button></aside></form>`;
    const eventEditor = initMediumEditor(item || {}, isEdit, { buildBlocks: buildEventBlocks, placeholder: 'Mulai tulis agenda. Tekan Tab atau klik plus untuk memilih blok.', extraTools: { embedMap: { class: MapEmbedTool, inlineToolbar: false } } });
    document.querySelector('#event-form').addEventListener('submit', async (event) => {
      event.preventDefault();
      let contentHtml = '';
      if (eventEditor?.save) {
        try { const saved = await eventEditor.save(); contentHtml = blocksToEventHtml(saved.blocks || []); } catch (error) { const fallbackEl = document.querySelector('#editor-fallback article'); if (fallbackEl) contentHtml = fallbackEl.innerHTML; }
      } else { const fallbackEl = document.querySelector('#editor-fallback article'); if (fallbackEl) contentHtml = fallbackEl.innerHTML; }
      const payload = { title: document.querySelector('#event-title-field')?.textContent?.trim() || '', excerpt: document.querySelector('#event-short-content-field')?.textContent?.trim() || '', content: contentHtml, location: document.querySelector('#event-location')?.value?.trim() || '', map: extractMapEmbedUrl(document.querySelector('#event-map')?.value || ''), start_date: document.querySelector('#event-start-date')?.value || '', end_date: document.querySelector('#event-end-date')?.value || '', photo: item?.photo || '', banner: item?.banner || '', meta_title: document.querySelector('#event-meta-title')?.value?.trim() || '', meta_description: document.querySelector('#event-meta-description')?.value?.trim() || '' };
      const url = isEdit ? route('admin.eventUpdate', { id: eventId }) : route('admin.eventStore');
      const res = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const json = await res.json().catch(() => ({}));
      if (res.ok) {
        Admin.showToast(isEdit ? 'Agenda diperbarui.' : 'Agenda ditambahkan.');
        if (!isEdit && json.data?.id) window.setTimeout(() => { window.location.href = `${adminUrl('event-edit')}?id=${json.data.id}`; }, 700);
      } else {
        Admin.showToast(json.error || 'Gagal menyimpan agenda.');
      }
    });
  }

  function bindSsrEventForm(form, eventEditor, item, isEdit, eventId) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      let contentHtml = '';
      if (eventEditor?.save) {
        try { const saved = await eventEditor.save(); contentHtml = blocksToEventHtml(saved.blocks || []); }
        catch { contentHtml = form.querySelector('#editor-fallback article')?.innerHTML || ''; }
      } else {
        contentHtml = form.querySelector('#editor-fallback article')?.innerHTML || '';
      }
      const payload = {
        title: form.querySelector('#event-title-field')?.textContent?.trim() || '',
        excerpt: form.querySelector('#event-short-content-field')?.textContent?.trim() || '',
        content: contentHtml,
        location: form.querySelector('#event-location')?.value?.trim() || '',
        map: extractMapEmbedUrl(form.querySelector('#event-map')?.value || ''),
        start_date: form.querySelector('#event-start-date')?.value || '',
        end_date: form.querySelector('#event-end-date')?.value || '',
        photo: item?.photo || '',
        banner: item?.banner || '',
        meta_title: form.querySelector('#event-meta-title')?.value?.trim() || '',
        meta_description: form.querySelector('#event-meta-description')?.value?.trim() || '',
      };
      const url = isEdit ? route('admin.eventUpdate', { id: eventId }) : route('admin.eventStore');
      const res = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const json = await res.json().catch(() => ({}));
      if (res.ok) {
        Admin.showToast(isEdit ? 'Agenda diperbarui.' : 'Agenda ditambahkan.');
        if (!isEdit && json.data?.id) window.setTimeout(() => { window.location.href = `${adminUrl('event-edit')}?id=${json.data.id}`; }, 700);
      } else {
        Admin.showToast(json.error || 'Gagal menyimpan agenda.');
      }
    });
  }

  function bindSsrCommentSettings() {
    const form = document.querySelector('#comment-setting-form[data-ssr="true"]');
    if (!form) return;
    enhanceAdminSelects(form);
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = Object.fromEntries(new FormData(form).entries());
      await API.updateCommentSettings(payload);
      Admin.showToast('Pengaturan komentar berhasil disimpan.');
    });
  }

  function liveSiteSettings() {
    return { ...(site || {}), ...(window.GenBISiteSettings || {}) };
  }

  function getLiveSliders() {
    const settings = liveSiteSettings();
    const slides = Array.isArray(settings.heroSlides) && settings.heroSlides.length ? settings.heroSlides : sliders.map((item) => ({ image: item.photo, title: item.heading, caption: '', eyebrow: 'GenBI Provinsi Jambi' }));
    return [0, 1].map((index) => ({
      id: index + 1,
      index,
      photo: slides[index]?.image || sliders[index]?.photo || '',
      heading: slides[index]?.title || sliders[index]?.heading || '',
      caption: slides[index]?.caption || '',
      eyebrow: slides[index]?.eyebrow || slides[0]?.eyebrow || 'GenBI Provinsi Jambi',
      position: index === 0 ? 'Primary hero' : 'Secondary hero',
    }));
  }

  function renderSliderList() {
    const body = renderShell('View Sliders', 'Slider ini live: gambar dan teksnya menjadi background hero landing page paling atas.', `<a href="${adminUrl('slider-add')}?slot=1" class="btn btn-primary">Add Slider</a>`);
    const liveSliders = getLiveSliders();
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950 dark-theme-note">
          Slider 1 dan Slider 2 tersimpan di <code>site.banner_image_1</code> dan <code>site.banner_image_2</code>. Perubahan langsung mengalir ke hero landing page <strong>/</strong>.
        </div>
        <div class="slider-card-grid mt-5">
          ${liveSliders.map((item) => `
            <article class="slider-card">
              <img src="${escape(item.photo)}" alt="${escape(item.heading || item.position)}" />
              <div>
                <span class="cms-pill cms-pill-blue">${escape(item.position)}</span>
                <h2 class="mt-3">${escape(item.heading || 'Belum ada headline')}</h2>
                <p>${escape(item.caption || 'Belum ada deskripsi.')}</p>
              </div>
              <div class="flex gap-2"><a class="cms-action edit" href="${adminUrl('slider-add')}?slot=${item.id}">Edit</a><a class="cms-action" href="/" target="_blank" rel="noopener">Preview</a></div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
  }

  function renderSliderEditor() {
    const slot = Math.min(2, Math.max(1, Number(new URLSearchParams(window.location.search).get('slot')) || 1));
    const item = getLiveSliders()[slot - 1];
    const body = renderShell(`Edit Slider ${slot}`, 'Form slider sekarang live dan menyimpan data ke Settings hero landing page.', `<a href="${adminUrl('slider')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="slider-form" data-slot="${slot}">
        <main class="block-writing-surface">
          <label class="config-field"><span>Eyebrow / Badge</span><input class="config-input" id="slider-eyebrow" value="${escape(item.eyebrow)}" placeholder="Contoh: GenBI Provinsi Jambi" /></label>
          <label class="config-field"><span>Heading</span><textarea class="config-input min-h-28" id="slider-heading" placeholder="Heading slider...">${escape(item.heading)}</textarea></label>
          <label class="config-field"><span>Caption</span><textarea class="config-input min-h-32" id="slider-caption" placeholder="Konten pendek slider...">${escape(item.caption)}</textarea></label>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card">
            <h2>Slider Config</h2>
            ${control('Hero Slot', `<input class="config-input" value="Slider ${slot}" disabled />`)}
            ${control('Image URL', `<input class="config-input" id="slider-image-url" value="${escape(item.photo)}" placeholder="/uploads/branding/..." />`)}
            ${control('Upload Image', '<input class="admin-file-input" id="slider-image-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/x-icon,image/vnd.microsoft.icon" />')}
            <div class="settings-banner-preview mt-4"><div class="settings-banner-preview-media">${item.photo ? `<img src="${escape(item.photo)}" alt="${escape(item.heading || 'Slider preview')}" loading="lazy">` : '<div class="settings-banner-preview-empty">Belum ada gambar</div>'}</div></div>
          </section>
          <button type="submit" class="btn btn-primary w-full">Save Slider ${slot}</button>
        </aside>
      </form>
    `;
    bindLiveSliderForm(slot);
  }

  function sliderSettingsPayload(slot) {
    const live = getLiveSliders();
    const current = live[slot - 1];
    current.eyebrow = document.querySelector('#slider-eyebrow')?.value?.trim() || 'GenBI Provinsi Jambi';
    current.heading = document.querySelector('#slider-heading')?.value?.trim() || '';
    current.caption = document.querySelector('#slider-caption')?.value?.trim() || '';
    current.photo = document.querySelector('#slider-image-url')?.value?.trim() || '';
    const fallback = sliders.map((item) => ({
      eyebrow: 'GenBI Provinsi Jambi',
      heading: item.heading || 'Bersama GenBI, tumbuh dan berdampak untuk Jambi.',
      caption: 'Ruang belajar, berkarya, dan mengabdi bersama GenBI Jambi.',
      photo: item.photo || 'https://genbijambi.com/public/uploads/slider-1.png',
    }));
    const first = { ...fallback[0], ...(live[0] || {}) };
    const second = { ...fallback[1], ...(live[1] || {}) };
    first.eyebrow = first.eyebrow || current.eyebrow || fallback[0].eyebrow;
    first.heading = first.heading || fallback[0].heading;
    first.caption = first.caption || fallback[0].caption;
    first.photo = first.photo || fallback[0].photo;
    second.eyebrow = second.eyebrow || first.eyebrow || fallback[1].eyebrow;
    second.heading = second.heading || first.heading || fallback[1].heading;
    second.caption = second.caption || first.caption || fallback[1].caption;
    second.photo = second.photo || first.photo || fallback[1].photo;
    return {
      'site.banner_badge': current.eyebrow || first.eyebrow,
      'site.banner_headline': first.heading,
      'site.banner_headline_alt': second.heading,
      'site.banner_subtitle': first.caption,
      'site.banner_subtitle_alt': second.caption,
      'site.banner_image_1': first.photo,
      'site.banner_image_2': second.photo,
    };
  }

  function syncSliderSettings(payload) {
    const settings = window.GenBISiteSettings = { ...(window.GenBISiteSettings || {}) };
    settings.heroSlides = Array.isArray(settings.heroSlides) && settings.heroSlides.length ? settings.heroSlides : [{}, {}];
    settings.heroSlides[0] = { ...(settings.heroSlides[0] || {}), eyebrow: payload['site.banner_badge'], title: payload['site.banner_headline'], caption: payload['site.banner_subtitle'], image: payload['site.banner_image_1'] };
    settings.heroSlides[1] = { ...(settings.heroSlides[1] || {}), eyebrow: payload['site.banner_badge'], title: payload['site.banner_headline_alt'], caption: payload['site.banner_subtitle_alt'], image: payload['site.banner_image_2'] };
  }

  function bindLiveSliderForm(slot) {
    document.querySelector('#slider-image-file')?.addEventListener('change', async (event) => {
      const file = event.target.files?.[0];
      if (!file) return;
      const form = new FormData();
      form.append('image', file);
      const response = await fetch('/admin/settings/upload', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() }, credentials: 'same-origin', body: form });
      const json = await response.json().catch(() => ({}));
      if (!response.ok || !json.data?.url) return Admin.showToast(json.error || 'Upload slider gagal.');
      const input = document.querySelector('#slider-image-url');
      if (input) input.value = json.data.url;
      const preview = document.querySelector('.settings-banner-preview-media');
      if (preview) {
        const heading = document.querySelector('#slider-heading')?.value?.trim() || 'Slider preview';
        preview.innerHTML = `<img src="${escape(json.data.url)}" alt="${escape(heading)}" loading="lazy">`;
      }
      Admin.showToast('Gambar slider berhasil diupload. Klik Save untuk menerapkan ke landing page.');
    });
    document.querySelector('#slider-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = sliderSettingsPayload(slot);
      const response = await fetch('/admin/settings/banner', { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const json = await response.json().catch(() => ({}));
      if (!response.ok) return Admin.showToast(json.error || 'Gagal menyimpan slider.');
      syncSliderSettings(payload);
      Admin.showToast('Slider berhasil disimpan dan sudah live di landing page.');
      window.setTimeout(() => { window.location.href = adminUrl('slider'); }, 700);
    });
  }

  async function renderTeamList() {
    // Check if SSR markup exists - if so, bind delete, batch, and home toggle
    if (document.querySelector('#admin-team-list[data-ssr="true"]')) {
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      bindTeamDeleteButtons();
      bindTeamBatchMode();
      bindTeamHomeToggle();
      bindTeamAlumniButtons();
      return;
    }

    const body = renderShell('View Team Members', 'Direktori anggota memakai mode card. Bisa berpindah antara grid dan list tanpa tabel sempit. Beranda memakai periode terbaru lalu bisa dioverride lewat aksi BPI Beranda.', '');
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data anggota...</div>';

    const state = { view: 'grid', query: '', division: '', campus: '', year: '', page: 1, perPage: 12, total: 0, items: [], batchMode: false, filters: { divisions: [], campuses: [], years: [] } };
    // Hydrate state from URL
    const urlParams = new URLSearchParams(location.search);
    if (urlParams.get('page')) state.page = Math.max(1, Number(urlParams.get('page')) || 1);
    if (urlParams.get('per_page')) state.perPage = Number(urlParams.get('per_page')) || 12;
    if (urlParams.get('q')) state.query = urlParams.get('q');
    if (urlParams.get('division')) state.division = urlParams.get('division');
    if (urlParams.get('campus')) state.campus = urlParams.get('campus');
    if (urlParams.get('year')) state.year = urlParams.get('year');
    const syncUrl = () => {
      const params = new URLSearchParams();
      if (state.query) params.set('q', state.query);
      if (state.division) params.set('division', state.division);
      if (state.campus) params.set('campus', state.campus);
      if (state.year) params.set('year', state.year);
      if (state.page > 1) params.set('page', String(state.page));
      if (state.perPage !== 12) params.set('per_page', String(state.perPage));
      const qs = params.toString();
      const url = qs ? `${location.pathname}?${qs}` : location.pathname;
      history.replaceState({}, '', url);
    };
    const load = async () => {
      const endpoint = Core.buildEndpoint(route('admin.teamMembers'), { q: state.query, division: state.division, campus: state.campus, year: state.year, page: state.page, per_page: state.perPage });
      try {
        const res = await fetch(endpoint, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) {
          const json = await res.json();
          state.items = json.data || [];
          state.total = Number(json.meta?.total || state.items.length);
          state.page = Number(json.meta?.page || state.page);
          state.filters = json.filters || state.filters;
          return;
        }
      } catch (e) { /* fallback below */ }
      state.filters = {
        divisions: Array.from(new Set(teamMembers.map((item) => item.division).filter(Boolean))),
        campuses: Array.from(new Set(teamMembers.map((item) => item.commission || item.campus).filter(Boolean))),
        years: Array.from(new Set(teamMembers.map((item) => item.year).filter(Boolean))),
      };
      const filtered = teamMembers.filter((item) => {
        const haystack = `${item.name} ${item.role} ${item.commission} ${item.division}`.toLowerCase();
        if (state.query && !haystack.includes(state.query.toLowerCase())) return false;
        if (state.division && item.division !== state.division) return false;
        if (state.campus && (item.commission || item.campus) !== state.campus) return false;
        if (state.year && String(item.year) !== String(state.year)) return false;
        return true;
      });
      state.total = filtered.length;
      state.items = filtered.slice((state.page - 1) * state.perPage, state.page * state.perPage);
    };

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="cms-toolbar">
          <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-neutral-600">Show ${selectControl({ id: 'team-per-page', className: 'admin-inline-select', value: '12', options: ['12', '24', '48'] })} entries</label>
            <label class="cms-search">${Admin.icon('search')}<input id="team-search" placeholder="Cari nama, jabatan, komisariat, divisi..." /></label>
          </div>
          <div class="view-toggle" role="group" aria-label="Mode tampilan">
            <button type="button" class="is-active" data-view="grid">${Admin.icon('grid')} Grid</button>
            <button type="button" data-view="list">${Admin.icon('list')} List</button>
          </div>
        </div>
        <div class="team-action-row mt-5">
          <a href="${adminUrl('team-member-add')}" class="cms-action edit">Add Team Member</a>
          <button type="button" class="cms-action edit" id="team-batch-toggle">Batch Operation</button>
        </div>
        <div class="team-batch-bar mt-3 hidden" id="team-batch-bar"></div>
        <div class="team-filter-row mt-5" aria-label="Filter anggota">
          <label class="team-filter-field">Divisi<select class="config-input js-admin-custom-select" id="team-division-filter"></select></label>
          <label class="team-filter-field">Komisariat<select class="config-input js-admin-custom-select" id="team-campus-filter"></select></label>
          <label class="team-filter-field">Tahun<select class="config-input js-admin-custom-select" id="team-year-filter"></select></label>
        </div>
        <div id="team-cards" class="team-card-grid mt-6"></div>
        <div class="admin-pagination mt-5" id="team-pagination" aria-label="Pagination anggota"></div>
      </section>
    `;
    const updateSelectionCount = () => {
      const count = document.querySelector('#team-selection-count');
      if (count) count.textContent = String(teamSelection.size);
    };
    const render = async () => {
      syncUrl();
      await load();
      const root = document.querySelector('#team-cards');
      root.className = state.view === 'grid' ? 'team-card-grid mt-6' : 'team-card-list mt-6';
      root.innerHTML = state.items.length ? state.items.map((item, index) => teamCard(item, index, state.batchMode)).join('') : '<div class="rounded-2xl border border-neutral-900/10 bg-white p-6 text-sm text-neutral-500">Belum ada anggota.</div>';
      bindTeamCardActions(render);
      renderTeamFilterButtons(state, render);
      renderTeamBatchBar(state, render);
      renderAdminPagination('#team-pagination', Math.max(1, Math.ceil(state.total / state.perPage)), state.page, (page) => { state.page = page; render(); });
      updateSelectionCount();
    };
    enhanceAdminSelects(body);
    document.querySelectorAll('[data-view]').forEach((button) => button.addEventListener('click', () => {
      state.view = button.dataset.view;
      document.querySelectorAll('[data-view]').forEach((item) => item.classList.toggle('is-active', item === button));
      render();
    }));
    document.querySelector('#team-search').addEventListener('input', (event) => { state.query = event.target.value; state.page = 1; render(); });
    document.querySelector('#team-per-page')?.addEventListener('change', (event) => { state.perPage = Number(event.target.value) || 12; state.page = 1; render(); });
    document.querySelector('#team-batch-toggle')?.addEventListener('click', () => { state.batchMode = !state.batchMode; render(); });
    render();
  }

  async function renderTeamEditor() {
    const ssrForm = document.querySelector('#team-form[data-ssr="true"]');
    if (ssrForm) {
      const id = Number(ssrForm.dataset.itemId) || 0;
      const isEdit = ssrForm.dataset.edit === '1';
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      document.querySelector('#team-photo-upload')?.addEventListener('change', uploadTeamPhoto);
      ssrForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = teamEditorPayload();
        const res = await fetch(isEdit ? route('admin.teamMemberUpdate', { id }) : route('admin.teamMembers'), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
          credentials: 'same-origin',
          body: JSON.stringify(payload),
        });
        const json = await res.json().catch(() => ({}));
        if (res.ok) {
          Admin.showToast(isEdit ? 'Anggota diperbarui.' : 'Anggota ditambahkan.');
          if (!isEdit && json.data?.id) setTimeout(() => { window.location.href = `${adminUrl('team-member-edit')}?id=${json.data.id}`; }, 900);
        } else {
          Admin.showToast(json.error || 'Gagal menyimpan anggota.');
        }
      });
      return;
    }
    const id = Number(new URLSearchParams(location.search).get('id')) || 4;
    const isEdit = location.pathname.includes('edit');
    let item = isEdit ? (teamMembers.find((entry) => entry.id === id) || teamMembers[0]) : { name: '', role: '', division: '', commission: '', campus: '', status: '', bio: '', year: new Date().getFullYear() };
    if (isEdit) {
      try {
        const res = await fetch(route('admin.teamMemberShow', { id }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) item = (await res.json()).data || item;
      } catch (e) { /* fallback */ }
    }
    const options = await loadTeamFormOptions();
    const body = renderShell(isEdit ? 'Edit Team Member' : 'Add Team Member', 'Form tambah dan edit dibuat sama. Field tambahan: Komisariat, Divisi, Jabatan, dan Divisi Lain.', `<a href="${adminUrl('team-member')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="team-form">
        <main class="block-writing-surface">
          <input id="team-name" class="news-title-block" placeholder="Nama anggota..." value="${escape(item.name)}" />
          <div class="team-form-grid">
            ${control('Jabatan', `<input id="team-designation" class="config-input" value="${escape(item.role || item.designation || '')}" />`)}
            ${control('Komisariat', selectControl({ id: 'team-komsat-id', value: item.komsat_id || '', options: [{ value: '', label: 'Pilih Komisariat' }, ...options.commissions.map((option) => ({ value: option.id, label: option.nama }))] }))}
            ${control('Divisi', selectControl({ id: 'team-divisi-id', value: item.divisi_id || item.division_id || '', options: [{ value: '', label: 'Pilih Divisi' }, ...options.divisions.map((option) => ({ value: option.id, label: option.nama }))] }))}
            ${control('Tahun', `<input id="team-year" class="config-input" type="number" value="${escape(item.year || item.tahun || new Date().getFullYear())}" />`)}
            ${control('Email', `<input id="team-email" class="config-input" type="email" value="${escape(item.email || '')}" />`)}
            ${control('Phone', `<input id="team-phone" class="config-input" value="${escape(item.phone || '')}" />`)}
          </div>
          <textarea id="team-detail" class="news-body-block smaller" placeholder="Description / bio singkat anggota...">${escape(item.detail || item.bio || '')}</textarea>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card">
            <h2>Photo</h2>
            <div class="member-preview-avatar" id="team-photo-preview">${item.photo ? `<img src="${escape(item.photo)}" alt="${escape(item.name)}" />` : Admin.initials(item.name || 'Member')}</div>
            ${control('Photo URL', `<input id="team-photo" class="config-input" value="${escape(item.photo_raw || item.photo || '')}" />`)}
            ${control('Upload Photo', '<input class="config-input" id="team-photo-upload" type="file" accept="image/*" />')}
          </section>
          <section class="config-card"><h2>Visibility</h2>${control('Tampilkan di Beranda', selectControl({ id: 'team-show-home', value: item.show_on_home ? '1' : '0', options: [{ value: '1', label: 'Show' }, { value: '0', label: 'Hide' }] }))}</section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update Member' : 'Submit Member'}</button>
        </aside>
      </form>
    `;
    enhanceAdminSelects(body);
    document.querySelector('#team-photo-upload')?.addEventListener('change', uploadTeamPhoto);
    document.querySelector('#team-form').addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = teamEditorPayload();
      const res = await fetch(isEdit ? route('admin.teamMemberUpdate', { id }) : route('admin.teamMembers'), {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        const json = await res.json();
        Admin.showToast(isEdit ? 'Anggota diperbarui.' : 'Anggota ditambahkan.');
        if (!isEdit && json.data?.id) setTimeout(() => { window.location.href = `${adminUrl('team-member-edit')}?id=${json.data.id}`; }, 900);
      } else {
        const json = await res.json().catch(() => ({}));
        Admin.showToast(json.error || 'Gagal menyimpan anggota.');
      }
    });
  }


  function renderFeatureList() {
    const ssrList = document.querySelector('#admin-feature-list[data-ssr="true"]');
    if (ssrList) {
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      hydrateFeatureIcons(ssrList);
      bindFeatureDeleteButtons();
      return;
    }
    const body = renderShell('Program Utama', 'Program utama tampil sebagai daftar editorial. Isi dapat dipindai tanpa visual yang ramai.', `<a href="${adminUrl('feature-add')}" class="btn btn-primary">Add Program Utama</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Program Utama')}
        <div class="simple-card-grid mt-5">
          ${programs.map((item) => `
            <article class="simple-admin-card">
              <div class="meta">${escape(item.focus)}</div>
              <h2>${escape(item.title)} · ${escape(item.name)}</h2>
              <p>${escape(item.description)}</p>
              <div class="mt-4 flex gap-2">${rowActions('Program Utama')}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    bindDeleteButtons('Program akan dihapus dari daftar simulasi.');
  }

  function renderFeatureEditor() {
    const form = document.querySelector('#feature-editor-form[data-ssr="true"]');
    if (form) {
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      setupFeatureIconPicker(form);
      bindFeatureImageBoard(form);
      bindFeatureForm(form);
      hydrateFeatureIcons(document.querySelector('#admin-content') || document);
      return;
    }
    const body = renderShell('Add Program Utama', 'Tambah program dengan field besar dan tombol input custom.', `<a href="${adminUrl('feature')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="feature-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Nama program..."></div>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Fokus program..."></div>
          <article class="news-body-block smaller" contenteditable="true" data-placeholder="Manfaat program untuk anggota dan publik..."></article>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Program Config</h2>${control('Icon', '<input class="config-input" placeholder="users, bank, phone..." />')}${control('Show on Home', selectControl({ id: 'feature-show-home', value: 'Show', options: ['Show', 'Hide'] }))}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Program Utama</button>
        </aside>
      </form>
    `;
    bindSimpleSubmit('#feature-form', 'Submit program?', 'Program ditambahkan pada mode simulasi.');
  }

  function hydrateFeatureIcons(root = document) {
    root.querySelectorAll('[data-program-icon]').forEach((node) => {
      const iconKey = node.dataset.programIcon || 'sparkles';
      node.innerHTML = Admin.icon(iconKey, 'h-4 w-4');
    });
    root.querySelectorAll('[data-feature-icon-preview]').forEach((node) => {
      const iconKey = node.dataset.featureIconPreview || 'sparkles';
      node.innerHTML = Admin.icon(iconKey, 'h-5 w-5');
    });
  }

  function setupFeatureIconPicker(form) {
    const picker = form.querySelector('#feature-icon-picker');
    const button = form.querySelector('#feature-icon-button');
    const label = form.querySelector('#feature-icon-label');
    if (!picker || !button || !label) return;

    const closeAdminSelectMenus = () => {
      document.querySelectorAll('.admin-select-button[aria-expanded="true"]').forEach((openButton) => openButton.click());
    };

    const updateSelection = (iconKey) => {
      picker.dataset.selectedIcon = iconKey;
      const preview = button.querySelector('[data-feature-icon-preview]');
      if (preview) preview.dataset.featureIconPreview = iconKey;
      label.textContent = iconKey;
      hydrateFeatureIcons(button);
    };

    button.setAttribute('aria-haspopup', 'dialog');
    button.setAttribute('aria-expanded', 'false');
    button.addEventListener('click', () => {
      closeAdminSelectMenus();
      openFeatureIconModal(picker.dataset.selectedIcon || 'sparkles', updateSelection, button);
    });
    updateSelection(picker.dataset.selectedIcon || 'sparkles');
  }

  function openFeatureIconModal(selectedIcon, onSelect, returnFocus) {
    const previousModal = document.querySelector('#feature-icon-modal');
    if (previousModal) previousModal.remove();

    const modal = document.createElement('div');
    modal.id = 'feature-icon-modal';
    modal.className = 'feature-icon-modal';
    modal.innerHTML = `
      <div class="feature-icon-modal-backdrop" data-close-icon-modal></div>
      <section class="feature-icon-modal-panel" role="dialog" aria-modal="true" aria-labelledby="feature-icon-modal-title">
        <header class="feature-icon-modal-head">
          <div>
            <p class="eyebrow">Heroicons Library</p>
            <h2 id="feature-icon-modal-title">Pilih Ikon Program</h2>
            <p>Ikon dikelompokkan agar Program Utama mudah dibedakan secara visual.</p>
          </div>
          <button type="button" class="feature-icon-modal-close" data-close-icon-modal aria-label="Tutup pemilih ikon">×</button>
        </header>
        <div class="feature-icon-modal-tools">
          <label class="feature-icon-search">
            <span>Search icon</span>
            <input id="feature-icon-search" type="search" placeholder="Cari: bank, academic, calendar..." autocomplete="off" />
          </label>
          <div class="feature-icon-segments" role="tablist" aria-label="Kategori ikon">
            <button type="button" class="feature-icon-segment is-active" data-icon-group="all">All</button>
            ${programIconGroups.map((group) => `<button type="button" class="feature-icon-segment" data-icon-group="${escape(group.key)}">${escape(group.label)}</button>`).join('')}
          </div>
        </div>
        <div class="feature-icon-modal-grid" id="feature-icon-modal-grid"></div>
        <p class="feature-icon-empty hidden" id="feature-icon-empty">Tidak ada ikon yang cocok.</p>
      </section>
    `;

    document.body.appendChild(modal);
    document.body.classList.add('modal-lock');

    const panel = modal.querySelector('.feature-icon-modal-panel');
    const grid = modal.querySelector('#feature-icon-modal-grid');
    const search = modal.querySelector('#feature-icon-search');
    const empty = modal.querySelector('#feature-icon-empty');
    const segments = Array.from(modal.querySelectorAll('[data-icon-group]'));
    let activeGroup = 'all';

    const groupedIcons = programIconGroups.reduce((map, group) => {
      map[group.key] = group.icons || [];
      return map;
    }, {});
    const allIcons = Array.from(new Set(programIconChoices));

    const render = () => {
      const query = (search?.value || '').trim().toLowerCase();
      const source = activeGroup === 'all' ? allIcons : (groupedIcons[activeGroup] || []);
      const icons = source.filter((iconKey) => !query || iconKey.toLowerCase().includes(query));
      grid.innerHTML = icons.map((iconKey) => `
        <button type="button" class="feature-icon-option ${selectedIcon === iconKey ? 'is-active' : ''}" data-icon-key="${iconKey}" aria-pressed="${selectedIcon === iconKey ? 'true' : 'false'}">
          <span class="feature-icon-option-preview">${Admin.icon(iconKey, 'h-5 w-5 feature-icon-option-svg')}</span>
          <span>${escape(iconKey)}</span>
        </button>
      `).join('');
      empty?.classList.toggle('hidden', icons.length > 0);
    };

    const close = () => {
      modal.remove();
      document.body.classList.remove('modal-lock');
      returnFocus?.setAttribute('aria-expanded', 'false');
      returnFocus?.focus();
    };

    returnFocus?.setAttribute('aria-expanded', 'true');
    render();
    window.setTimeout(() => search?.focus(), 0);

    modal.addEventListener('click', (event) => {
      if (event.target.closest('[data-close-icon-modal]')) {
        close();
        return;
      }
      const segment = event.target.closest('[data-icon-group]');
      if (segment) {
        activeGroup = segment.dataset.iconGroup || 'all';
        segments.forEach((item) => item.classList.toggle('is-active', item === segment));
        render();
        return;
      }
      const option = event.target.closest('[data-icon-key]');
      if (option) {
        selectedIcon = option.dataset.iconKey || 'sparkles';
        onSelect(selectedIcon);
        close();
      }
    });
    search?.addEventListener('input', render);
    modal.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') close();
      if (event.key !== 'Tab' || !panel) return;
      const focusable = Array.from(panel.querySelectorAll('button, input')).filter((node) => !node.disabled && node.offsetParent !== null);
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
  }

  function collectFeatureImages(form) {
    return Array.from(form.querySelectorAll('.feature-image-card')).map((card, index) => ({
      id: Number(card.dataset.imageId || 0) || 0,
      path: card.dataset.imagePath || '',
      sort_order: index,
    })).filter((image) => image.path);
  }

  function bindFeatureImageBoard(form) {
    const board = form.querySelector('#feature-image-board');
    const empty = form.querySelector('#feature-image-empty');
    const fileInput = form.querySelector('#feature-image-files');
    const uploadButton = form.querySelector('#feature-upload-btn');
    if (!board || !fileInput || !uploadButton) return;

    const syncState = () => {
      const cards = board.querySelectorAll('.feature-image-card');
      cards.forEach((card, index) => {
        const badge = card.querySelector('.feature-image-card-meta span');
        if (badge) badge.textContent = `#${index + 1}`;
      });
      if (empty) empty.classList.toggle('hidden', cards.length > 0);
    };

    const createCard = (image) => {
      const card = document.createElement('article');
      card.className = 'feature-image-card';
      card.dataset.imageId = String(image.id || 0);
      card.dataset.imagePath = image.path || image.url || '';
      card.innerHTML = `
        <img src="${escape(image.url || image.path || '')}" alt="Preview Program Utama" />
        <div class="feature-image-card-meta">
          <span>#1</span>
          <div class="flex gap-2">
            <button type="button" class="feature-image-move" data-direction="up" aria-label="Geser ke atas">↑</button>
            <button type="button" class="feature-image-move" data-direction="down" aria-label="Geser ke bawah">↓</button>
            <button type="button" class="feature-image-remove" aria-label="Hapus gambar">Hapus</button>
          </div>
        </div>
      `;
      board.appendChild(card);
      syncState();
    };

    uploadButton.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', async () => {
      const files = Array.from(fileInput.files || []);
      for (const file of files) {
        const formData = new FormData();
        formData.append('image', file);
        const res = await fetch(route('admin.featureUpload'), {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
          credentials: 'same-origin',
          body: formData,
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.data?.url) {
          Admin.showToast(json.error || 'Gagal mengunggah gambar Program Utama.');
          continue;
        }
        createCard(json.data);
      }
      fileInput.value = '';
    });

    board.addEventListener('click', async (event) => {
      const removeButton = event.target.closest('.feature-image-remove');
      const moveButton = event.target.closest('.feature-image-move');
      const card = event.target.closest('.feature-image-card');
      if (!card) return;

      if (moveButton) {
        const sibling = moveButton.dataset.direction === 'up' ? card.previousElementSibling : card.nextElementSibling;
        if (sibling && sibling.classList.contains('feature-image-card')) {
          if (moveButton.dataset.direction === 'up') {
            board.insertBefore(card, sibling);
          } else {
            board.insertBefore(sibling, card);
          }
          syncState();
        }
        return;
      }

      if (removeButton) {
        const featureId = Number(form.dataset.itemId || 0);
        const imageId = Number(card.dataset.imageId || 0);
        if (featureId > 0 && imageId > 0) {
          const ok = await Admin.showConfirm({ title: 'Hapus gambar?', message: 'Gambar akan dihapus dari slideshow Program Utama.', confirmText: 'Hapus', danger: true });
          if (!ok) return;
          const res = await fetch(route('admin.featureImageDelete', { id: featureId, imageId }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
            credentials: 'same-origin',
          });
          if (!res.ok) {
            Admin.showToast('Gagal menghapus gambar Program Utama.');
            return;
          }
        }
        card.remove();
        syncState();
      }
    });

    syncState();
  }

  function featurePayload(form) {
    return {
      title: form.querySelector('#feature-title')?.value?.trim() || '',
      name: form.querySelector('#feature-name')?.value?.trim() || '',
      focus: form.querySelector('#feature-focus')?.value?.trim() || '',
      description: form.querySelector('#feature-description')?.value?.trim() || '',
      icon_key: form.querySelector('#feature-icon-picker')?.dataset.selectedIcon || 'sparkles',
      show_on_home: form.querySelector('#feature-show-home')?.value === '1',
      status: form.querySelector('#feature-status')?.value || 'draft',
      sort_order: Number(form.querySelector('#feature-sort-order')?.value || 0),
      images: collectFeatureImages(form),
    };
  }

  function bindFeatureForm(form) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const isEdit = form.dataset.edit === '1';
      const featureId = Number(form.dataset.itemId || 0);
      const endpoint = isEdit ? route('admin.featureUpdate', { id: featureId }) : route('admin.featureStore');
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
        credentials: 'same-origin',
        body: JSON.stringify(featurePayload(form)),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        Admin.showToast(json.error || 'Gagal menyimpan Program Utama.');
        return;
      }
      Admin.showToast(isEdit ? 'Program Utama diperbarui.' : 'Program Utama ditambahkan.');
      const nextId = json.data?.id || featureId;
      if (!isEdit && nextId) {
        window.setTimeout(() => { window.location.href = `${adminUrl('feature-edit')}?id=${nextId}`; }, 800);
      }
    });
  }

  function bindFeatureDeleteButtons() {
    document.querySelectorAll('[data-delete-feature]').forEach((button) => {
      button.addEventListener('click', async () => {
        const featureId = Number(button.dataset.deleteFeature || 0);
        if (!featureId) return;
        const ok = await Admin.showConfirm({ title: 'Hapus Program Utama?', message: 'Program akan disembunyikan dari landing page dan daftar admin.', confirmText: 'Hapus', danger: true });
        if (!ok) return;
        const res = await fetch(route('admin.featureDelete', { id: featureId }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getAdminCsrfToken() },
          credentials: 'same-origin',
        });
        if (!res.ok) {
          Admin.showToast('Gagal menghapus Program Utama.');
          return;
        }
        button.closest('tr')?.remove();
        Admin.showToast('Program Utama berhasil dihapus.');
      });
    });
  }

  function renderWhyChooseList() {
    const rows = [
      { name: 'What is GenBI?', text: 'Penjelasan singkat tentang komunitas Generasi Baru Indonesia.', photo: site.logo },
      { name: 'What is Inflation?', text: 'Materi edukasi inflasi dan stabilitas harga.', photo: 'https://genbijambi.com/public/uploads/why-choose-2.jpg' },
      { name: 'What is QRIS?', text: 'Materi edukasi QRIS dan pembayaran digital.', photo: 'https://genbijambi.com/public/uploads/why-choose-3.jpg' }
    ];
    const body = renderShell('View Why Choose Us', 'Konten edukasi pendek yang membantu pengunjung memahami GenBI dan literasi kebanksentralan.', `<a href="${adminUrl('why-choose-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Why Choose Us')}
        <div class="simple-card-grid mt-5">
          ${rows.map((item) => `
            <article class="simple-admin-card">
              <img src="${item.photo}" alt="${escape(item.name)}" onerror="this.style.display='none'" />
              <div class="meta">Educational card</div>
              <h2>${escape(item.name)}</h2>
              <p>${escape(item.text)}</p>
              <div class="mt-4 flex gap-2">${rowActions('Why Choose Us')}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    bindDeleteButtons('Item edukasi akan dihapus dari daftar simulasi.');
  }

  function renderWhyChooseEditor() {
    const body = renderShell('Add Why Choose Us', 'Tambahkan item edukasi dengan field editorial dan foto pendukung.', `<a href="${adminUrl('why-choose')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="why-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Nama item edukasi..."></div>
          <article class="news-body-block smaller" contenteditable="true" data-placeholder="Isi penjelasan singkat..."></article>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Media</h2>${control('Icon', '<input class="config-input" placeholder="thumb, globe, clock..." />')}${control('Photo', '<input class="config-input" type="file" />')}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Item</button>
        </aside>
      </form>
    `;
    bindSimpleSubmit('#why-form', 'Submit item edukasi?', 'Item edukasi ditambahkan pada mode simulasi.');
  }

  function renderFaqList() {
    const body = renderShell('View FAQs', 'FAQ ringkas untuk menjawab pertanyaan umum pengunjung.', `<a href="${adminUrl('faq-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('FAQ')}
        <div class="simple-card-grid mt-5">
          <article class="simple-admin-card">
            <div class="meta">Show on home: Yes</div>
            <h2>GenBI Provinsi Jambi</h2>
            <p>Informasi umum tentang komunitas, kegiatan, dan anggota GenBI Jambi.</p>
            <div class="mt-4 flex gap-2">${rowActions('FAQ')}</div>
          </article>
        </div>
      </section>
    `;
    bindDeleteButtons('FAQ akan dihapus dari daftar simulasi.');
  }

  function renderFaqEditor() {
    const body = renderShell('Add FAQ', 'FAQ menggunakan area tulis lebar agar jawaban tidak terasa seperti input sempit.', `<a href="${adminUrl('faq')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="faq-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Pertanyaan FAQ..."></div>
          <article class="news-body-block smaller" contenteditable="true" data-placeholder="Tulis jawaban FAQ..."></article>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Visibility</h2>${control('Show on Home', selectControl({ id: 'faq-show-home', value: 'Yes', options: ['Yes', 'No'] }))}</section>
          <button type="submit" class="btn btn-primary w-full">Submit FAQ</button>
        </aside>
      </form>
    `;
    bindSimpleSubmit('#faq-form', 'Submit FAQ?', 'FAQ ditambahkan pada mode simulasi.');
  }

  function renderSocialMedia() {
    const body = renderShell('Social Media', 'Kosongkan field jika kanal tidak ingin tampil di halaman publik.', '<button id="save-social" class="btn btn-primary">Submit</button>');
    body.innerHTML = `
      <section class="admin-card p-5 md:p-7">
        <div class="grid max-w-3xl gap-4">
          ${control('YouTube', '<input class="config-input" value="https://youtu.be/9fqrRMLTw6F" />')}
          ${control('Instagram', '<input class="config-input" value="https://www.instagram.com/genbi_jambi" />')}
          ${control('WhatsApp', '<input class="config-input" value="https://wa.me/6289627896750" />')}
        </div>
      </section>
    `;
    document.querySelector('#save-social').addEventListener('click', () => Admin.showToast('Social media disimpan pada mode simulasi.'));
  }

  async function renderPhotoList() {
    const ssrList = document.querySelector('#admin-photo-list[data-ssr="true"]');
    if (ssrList) {
      bindSsrPhotoList(ssrList);
      return;
    }
    const body = renderShell('View Photos', 'Galeri foto tampil sebagai kartu agar preview lebih jelas.', `<a href="${adminUrl('photo-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = '<section class="admin-card p-8 text-center text-sm text-neutral-500">Memuat galeri foto...</section>';
    const res = await fetch(route('admin.photos'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
    const json = await res.json().catch(() => ({ data: [] }));
    const photos = Array.isArray(json.data) ? json.data : [];
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Photo')}
        <div class="simple-card-grid mt-5">
          ${photos.length ? photos.map((item) => `
            <article class="simple-admin-card">
              <img src="${escape(item.image)}" alt="${escape(item.title)}" onerror="this.replaceWith(Object.assign(document.createElement('div'), { className: 'config-empty', textContent: 'No image' }))" />
              <h2 class="mt-4">${escape(item.title)}</h2>
              ${item.caption ? `<p class="mt-2 text-sm text-[rgb(var(--text-secondary))]">${escape(item.caption)}</p>` : ''}
              <div class="mt-4 flex gap-2">
                <a class="cms-action edit" href="${adminUrl('photo-add')}?id=${item.id}">Edit</a>
                <button class="cms-action delete" data-photo-delete="${item.id}">Delete</button>
              </div>
            </article>
          `).join('') : '<div class="p-8 text-center text-sm text-neutral-500">Belum ada foto.</div>'}
        </div>
      </section>
    `;
    body.querySelectorAll('[data-photo-delete]').forEach((button) => button.addEventListener('click', async () => {
      const ok = await Admin.showConfirm({ title: 'Hapus foto?', message: 'Foto akan dihapus dari galeri.', confirmText: 'Hapus', danger: true });
      if (!ok) return;
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const response = await fetch(route('admin.photoDelete', { id: button.dataset.photoDelete }), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' });
      if (response.ok) { Admin.showToast('Foto berhasil dihapus.'); button.closest('article')?.remove(); }
      else Admin.showToast('Gagal menghapus foto.');
    }));
  }

  async function renderPhotoEditor() {
    const ssrForm = document.querySelector('#photo-form[data-ssr="true"]');
    if (ssrForm) {
      bindSsrPhotoForm(ssrForm, Number(ssrForm.dataset.photoId) || 0);
      return;
    }
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    let item = { title: '', caption: '', image: '', status: 'show' };
    if (id) {
      const res = await fetch(route('admin.photoShow', { id }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      const json = await res.json().catch(() => ({}));
      item = json.data || item;
    }
    const body = renderShell(id ? 'Edit Photo' : 'Add Photo', 'Galeri foto sekarang live dan tersimpan di backend.', `<a href="${adminUrl('photo')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="photo-form">
        <main class="block-writing-surface">
          <div class="admin-upload-zone">
            <p class="font-bold text-[rgb(var(--text-primary))]">Upload Photo</p>
            <div id="photo-preview" class="config-preview mb-3">${item.image ? `<img src="${escape(item.image)}" alt="${escape(item.title || 'Photo preview')}" />` : '<div class="config-empty">Belum ada foto</div>'}</div>
            <input id="photo-file" class="admin-file-input" type="file" accept="image/*" />
            <p class="text-sm text-[rgb(var(--text-secondary))]">Only jpg, jpeg, gif, and png are allowed.</p>
          </div>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Photo Info</h2>${control('Title', `<input id="photo-title" class="config-input" value="${escape(item.title)}" placeholder="Judul foto..." />`)}${control('Image URL', `<input id="photo-image" class="config-input" value="${escape(item.image)}" placeholder="/uploads/gallery/..." />`)}${control('Caption', `<input id="photo-caption" class="config-input" value="${escape(item.caption)}" placeholder="Caption foto..." />`)}${control('Visibility', selectControl({ id: 'photo-visibility', value: item.status === 'hide' ? 'hide' : 'show', options: [{ value: 'show', label: 'Show' }, { value: 'hide', label: 'Hide' }] }))}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Photo</button>
        </aside>
      </form>
    `;
    document.querySelector('#photo-file')?.addEventListener('change', async (event) => {
      const file = event.target.files?.[0];
      if (!file) return;
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const form = new FormData();
      form.append('image', file);
      const res = await fetch(route('admin.photoUpload'), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: form });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || !json.data?.url) { Admin.showToast(json.error || 'Upload gagal.'); return; }
      document.querySelector('#photo-image').value = json.data.url;
      renderSafeImagePreview('#photo-preview', json.data.url, 'Preview');
    });
    document.querySelector('#photo-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const payload = { title: document.querySelector('#photo-title')?.value?.trim() || '', image: document.querySelector('#photo-image')?.value?.trim() || '', caption: document.querySelector('#photo-caption')?.value?.trim() || '', status: document.querySelector('#photo-visibility')?.value || 'show', _csrf_token: token };
      const endpoint = id ? route('admin.photoUpdate', { id }) : route('admin.photoStore');
      const res = await fetch(endpoint, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) { Admin.showToast(json.error || 'Gagal menyimpan foto.'); return; }
      Admin.showToast('Foto berhasil disimpan.');
      window.location.href = adminUrl('photo');
    });
  }

  function bindSsrPhotoList(root) {
    root.querySelectorAll('[data-photo-delete]').forEach((button) => button.addEventListener('click', async () => {
      const ok = await Admin.showConfirm({ title: 'Hapus foto?', message: 'Foto akan dihapus dari galeri.', confirmText: 'Hapus', danger: true });
      if (!ok) return;
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const response = await fetch(route('admin.photoDelete', { id: button.dataset.photoDelete }), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' });
      if (response.ok) window.location.reload();
      else Admin.showToast('Gagal menghapus foto.');
    }));
  }

  function bindSsrPhotoForm(form, id) {
    form.querySelector('#photo-file')?.addEventListener('change', async (event) => {
      const file = event.target.files?.[0];
      if (!file) return;
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const upload = new FormData();
      upload.append('image', file);
      const res = await fetch(route('admin.photoUpload'), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: upload });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || !json.data?.url) { Admin.showToast(json.error || 'Upload gagal.'); return; }
      const image = form.querySelector('#photo-image');
      if (image) image.value = json.data.url;
      renderSafeImagePreview('#photo-preview', json.data.url, 'Preview');
    });
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const token = API.getCsrfToken ? API.getCsrfToken() : '';
      const payload = { title: form.querySelector('#photo-title')?.value?.trim() || '', image: form.querySelector('#photo-image')?.value?.trim() || '', caption: form.querySelector('#photo-caption')?.value?.trim() || '', status: form.querySelector('#photo-visibility')?.value || 'show', _csrf_token: token };
      const endpoint = id ? route('admin.photoUpdate', { id }) : route('admin.photoStore');
      const res = await fetch(endpoint, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) { Admin.showToast(json.error || 'Gagal menyimpan foto.'); return; }
      window.location.href = adminUrl('photo');
    });
  }

  function bindSimpleSubmit(selector, title, success) {
    document.querySelector(selector)?.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (await Admin.showConfirm({ title, message: 'Data akan disimpan pada mode simulasi frontend.' })) Admin.showToast(success);
    });
  }

  function teamCard(item, index, batchMode = false) {
    const photo = item.photo || memberPhotos[index % memberPhotos.length];
    const checked = teamSelection.has(Number(item.id)) ? 'checked' : '';
    const homeClass = item.show_on_home ? 'is-home' : '';
    const batchClass = batchMode ? 'is-batch' : '';
    return `
      <article class="team-admin-card ${homeClass} ${batchClass}" data-team-id="${item.id}">
        <label class="team-select-check ${batchMode ? '' : 'hidden'}"><input type="checkbox" data-team-select="${item.id}" ${checked} /> Select</label>
        <div class="team-admin-photo"><img src="${photo}" alt="${escape(item.name)}" onerror="this.remove(); this.parentElement.textContent='${Admin.initials(item.name)}';" /></div>
        <div class="team-admin-content">
          <h2>${escape(item.name)}</h2>
          <p>${escape(item.role)}</p>
          <div class="team-tags"><span>${escape(item.commission)}</span><span>${escape(item.division)}</span><span>${escape(item.status)}</span></div>
        </div>
        <div class="team-card-actions"><button type="button" class="cms-action" data-team-home="${item.id}" title="${item.show_on_home ? 'Hapus BPI dari Beranda' : 'Tambah Anggota ke Beranda'}">${item.show_on_home ? 'Hapus BPI' : 'BPI Beranda'}</button><button type="button" class="cms-action" data-team-alumni="${item.id}">Jadikan Alumni</button><a href="${adminUrl('team-member-edit')}?id=${item.id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-team-delete="${item.id}">Delete</button></div>
      </article>
    `;
  }

  function bindTeamDeleteButtons() {
    document.querySelectorAll('[data-delete-team]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.deleteTeam);
        const ok = await Admin.showConfirm({ title: 'Hapus anggota?', message: 'Anggota akan dihapus dari database.', confirmText: 'Delete', danger: true });
        if (!ok) return;
        const token = getAdminCsrfToken();
        const res = await fetch(route('admin.teamMemberDelete', { id }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: JSON.stringify({ _csrf_token: token }),
        });
        if (res.ok) {
          Admin.showToast('Anggota dihapus.');
          button.closest('tr')?.remove();
          button.closest('.team-admin-card')?.remove();
        } else {
          Admin.showToast('Gagal menghapus anggota.');
        }
      });
    });
  }

  function bindTeamCardActions(render) {
    document.querySelectorAll('[data-team-select]').forEach((input) => {
      input.addEventListener('change', () => {
        const id = Number(input.dataset.teamSelect);
        if (input.checked) teamSelection.add(id); else teamSelection.delete(id);
        const count = document.querySelector('#team-selection-count');
        if (count) count.textContent = String(teamSelection.size);
      });
    });
    document.querySelectorAll('[data-team-home]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.teamHome);
        const card = button.closest('.team-admin-card');
        const adding = !(card?.classList.contains('is-home'));
        const res = await fetch(route('admin.teamMemberHome', { id }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': API.getCsrfToken?.() || '' },
          credentials: 'same-origin',
          body: JSON.stringify({ show_on_home: adding, _csrf_token: API.getCsrfToken?.() || '' }),
        });
        Admin.showToast(res.ok ? (adding ? 'Ditambahkan ke BPI Beranda.' : 'Dihapus dari BPI Beranda.') : 'Gagal memperbarui BPI Beranda.');
        if (res.ok) render();
      });
    });
    document.querySelectorAll('[data-team-delete]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.teamDelete);
        const ok = await Admin.showConfirm({ title: 'Hapus anggota?', message: 'Anggota akan dihapus dari database.', confirmText: 'Delete', danger: true });
        if (!ok) return;
        const res = await fetch(route('admin.teamMemberDelete', { id }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': API.getCsrfToken?.() || '' },
          credentials: 'same-origin',
          body: JSON.stringify({ _csrf_token: API.getCsrfToken?.() || '' }),
        });
        if (res.ok) {
          teamSelection.delete(id);
          Admin.showToast('Anggota dihapus.');
          render();
        } else {
          Admin.showToast('Gagal menghapus anggota.');
        }
      });
    });
    document.querySelectorAll('[data-team-alumni]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.teamAlumni);
        const ok = await Admin.showConfirm({
          title: 'Jadikan alumni?',
          message: 'Anggota akan dipindahkan ke komisariat Alumni dan tidak masuk daftar anggota aktif.',
          confirmText: 'Jadikan Alumni',
        });
        if (!ok) return;
        const res = await fetch(route('admin.teamMemberAlumni', { id }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': API.getCsrfToken?.() || '' },
          credentials: 'same-origin',
          body: JSON.stringify({ _csrf_token: API.getCsrfToken?.() || '' }),
        });
        if (res.ok) {
          teamSelection.delete(id);
          Admin.showToast('Anggota dijadikan alumni.');
          render();
        } else {
          Admin.showToast('Gagal menjadikan anggota alumni.');
        }
      });
    });
  }

  function renderTeamFilterButtons(state, render) {
    const groups = [
      ['division', '#team-division-filter', 'Semua Divisi', state.filters.divisions || []],
      ['campus', '#team-campus-filter', 'Semua Komisariat', state.filters.campuses || []],
      ['year', '#team-year-filter', 'Semua Tahun', state.filters.years || []],
    ];

    groups.forEach(([key, selector, emptyLabel, values]) => {
      const select = document.querySelector(selector);
      if (!select) return;
      select.innerHTML = `<option value="">${escape(emptyLabel)}</option>${values.map((value) => `<option value="${escape(value)}">${escape(value)}</option>`).join('')}`;
      select.value = state[key] || '';
      if (select.dataset.customSelectReady !== '1') enhanceAdminSelects(select.parentElement || document);
      const wrapper = select.closest('.admin-custom-select');
      const label = select.options[select.selectedIndex]?.text || emptyLabel;
      wrapper?.querySelector('.admin-select-button span')?.replaceChildren(document.createTextNode(label));
      wrapper?.querySelectorAll('.admin-select-menu button').forEach((button) => {
        button.classList.toggle('is-active', String(button.dataset.value) === String(select.value));
      });
      select.addEventListener('change', () => {
        state[key] = select.value || '';
        state.page = 1;
        render();
      });
    });
  }

  function renderTeamBatchBar(state, render) {
    const bar = document.querySelector('#team-batch-bar');
    if (!bar) return;

    bar.classList.toggle('hidden', !state.batchMode);
    if (!state.batchMode) {
      bar.innerHTML = '';
      return;
    }

    bar.innerHTML = `
      <strong><span id="team-selection-count">${teamSelection.size}</span> dipilih</strong>
      <button type="button" class="cms-action delete" data-team-bulk="delete">Delete</button>
      <button type="button" class="cms-action" data-team-bulk="alumni">Jadikan Alumni</button>
      <button type="button" class="cms-action" id="team-selection-clear">Clear</button>
    `;

    bar.querySelector('#team-selection-clear')?.addEventListener('click', () => {
      teamSelection.clear();
      render();
    });

    bar.querySelectorAll('[data-team-bulk]').forEach((button) => button.addEventListener('click', async () => {
      if (teamSelection.size < 1) {
        Admin.showToast('Pilih minimal satu anggota.');
        return;
      }

      const action = button.dataset.teamBulk;
      const isDelete = action === 'delete';
      const isAlumni = action === 'alumni';
      const ok = await Admin.showConfirm({
        title: isDelete ? 'Delete anggota terpilih?' : 'Jadikan alumni?',
        message: `${teamSelection.size} anggota akan diproses.`,
        confirmText: isDelete ? 'Delete' : isAlumni ? 'Jadikan Alumni' : 'Proses',
        danger: isDelete,
      });
      if (!ok) return;

      const token = API.getCsrfToken?.() || '';
      const res = await fetch(route('admin.teamMembersBulk'), {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        credentials: 'same-origin',
        body: JSON.stringify({ action, ids: Array.from(teamSelection), _csrf_token: token }),
      });

      if (res.ok) {
        Admin.showToast('Batch operation berhasil.');
        render();
      } else {
        Admin.showToast('Batch operation gagal.');
      }
    }));
  }

  function teamEditorPayload() {
    const komsatSelect = document.querySelector('#team-komsat-id');
    const komsatLabel = komsatSelect?.selectedOptions?.[0]?.textContent || '';
    return {
      name: document.querySelector('#team-name')?.value || '',
      designation: document.querySelector('#team-designation')?.value || '',
      komsat_id: document.querySelector('#team-komsat-id')?.value || '',
      komsat: komsatLabel === 'Pilih Komisariat' ? '' : komsatLabel,
      divisi_id: document.querySelector('#team-divisi-id')?.value || '',
      email: document.querySelector('#team-email')?.value || '',
      phone: document.querySelector('#team-phone')?.value || '',
      detail: document.querySelector('#team-detail')?.value || '',
      photo: document.querySelector('#team-photo')?.value || '',
      tahun: document.querySelector('#team-year')?.value || '',
      show_on_home: document.querySelector('#team-show-home')?.value === '1',
    };
  }

  async function loadTeamFormOptions() {
    try {
      const res = await fetch(route('admin.teamMemberOptions'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (res.ok) {
        const json = await res.json();
        return json.data || { divisions: [], commissions: [] };
      }
    } catch (e) { /* fallback */ }
    return { divisions: [], commissions: [] };
  }

  async function uploadTeamPhoto(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    const token = API.getCsrfToken?.() || '';
    const form = new FormData();
    form.append('image', file);
    form.append('_csrf_token', token);
    const res = await fetch(route('admin.teamMembersUpload'), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: form });
    const json = await res.json().catch(() => ({}));
    if (res.ok && json.data?.url) {
      document.querySelector('#team-photo').value = json.data.url;
      renderSafeImagePreview('#team-photo-preview', json.data.url, 'Preview');
      Admin.showToast('Foto berhasil diupload.');
    } else {
      Admin.showToast(json.error || 'Upload foto gagal.');
    }
  }

  function renderSearchToolbar(label) {
    return `
      <div class="cms-toolbar">
        <label class="text-sm text-neutral-600">Show ${selectControl({ id: `${label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-per-page`, className: 'admin-inline-select', value: '10', options: ['10', '25'] })} entries</label>
        <label class="cms-search">${Admin.icon('search')}<input placeholder="Search ${label.toLowerCase()}..." /></label>
      </div>
    `;
  }

  function rowActions(label) {
    return `<div class="flex gap-2"><button class="cms-action edit">Edit</button><button class="cms-action delete" data-delete>Delete</button></div>`;
  }

  function bindDeleteButtons(message) {
    document.querySelectorAll('[data-delete]').forEach((button) => {
      button.addEventListener('click', async () => {
        const ok = await Admin.showConfirm({ title: 'Hapus data?', message, confirmText: 'Hapus', danger: true });
        if (ok) Admin.showToast('Data dihapus pada mode simulasi.');
      });
    });
  }

  function control(label, content) {
    return `<label class="config-field"><span>${label}</span>${content}</label>`;
  }


  function normalizeCommission(item = {}) {
    const value = `${item.commission || ''} ${item.campus || ''} ${item.status || ''}`.toLowerCase();
    if (value.includes('alumni')) return 'Alumni';
    if (value.includes('uin')) return 'UIN Sultan Thaha';
    return 'Universitas Jambi';
  }

  function miniSelectBlock(label, options, selected = '') {
    return `<section class="mini-block"><span>${label}</span>${selectControl({ id: `${label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-select`, className: 'team-commission-select', value: selected, options })}</section>`;
  }

  function miniBlock(label, value = '') {
    return `<section class="mini-block"><span>${label}</span><div contenteditable="true" data-placeholder="${label}...">${escape(value)}</div></section>`;
  }

  function attachEditorToolbar() {
    document.querySelectorAll('.editor-toolbar button').forEach((button) => {
      button.addEventListener('mousedown', (event) => event.preventDefault());
      button.addEventListener('click', () => {
        const editor = document.querySelector('.news-body-block');
        editor?.focus();
        document.execCommand(button.dataset.command, false, button.dataset.value || null);
      });
    });
  }

  function dateInput(value = '') {
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;
    const monthMap = { January: '01', February: '02', March: '03', April: '04', May: '05', June: '06', July: '07', August: '08', September: '09', October: '10', November: '11', December: '12' };
    const parts = String(value).replace(',', '').split(' ');
    if (parts.length >= 3) return `${parts[2]}-${monthMap[parts[0]] || '01'}-${String(parts[1]).padStart(2, '0')}`;
    return '2026-05-05';
  }

  function escape(value = '') { return Admin.escapeHtml(value); }

  function renderSafeImagePreview(selector, src, alt = 'Preview') {
    const preview = document.querySelector(selector);
    if (!preview) return;
    const img = document.createElement('img');
    img.src = String(src || '');
    img.alt = alt;
    preview.replaceChildren(img);
  }

  // ─── Prestasi CMS ───────────────────────────────────────────────────────────

  async function renderPrestasiList() {
    // Check if SSR markup exists - if so, only bind delete/detail behavior
    if (document.querySelector('#admin-prestasi-list[data-ssr="true"]')) {
      enhanceAdminSelects(document.querySelector('#admin-content') || document);
      bindPrestasiDeleteButtons();
      bindPrestasiApproveButtons();
      bindPrestasiDetailButtons();
      // Bind search form Enter key
      const searchInput = document.querySelector('#prestasi-search');
      if (searchInput) {
        searchInput.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
            document.querySelector('#prestasi-filter-form')?.submit();
          }
        });
      }
      return;
    }

    const body = renderShell(
      'View Prestasi',
      'Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.',
      `<a href="${adminUrl('prestasi-token')}" class="btn btn-secondary">Buat Link Form Prestasi</a><a href="${adminUrl('prestasi-add')}" class="btn btn-primary">Add Prestasi</a>`
    );
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data prestasi...</div>';

    // Hydrate state from URL
    const prestasiUrlParams = new URLSearchParams(location.search);
    const prestasiState = {
      page: Math.max(1, Number(prestasiUrlParams.get('page')) || 1),
      perPage: Number(prestasiUrlParams.get('per_page')) || 25,
      search: prestasiUrlParams.get('q') || '',
      category: prestasiUrlParams.get('category') || '',
      status: prestasiUrlParams.get('status') || '',
      total: 0,
      totalPages: 1,
      items: [],
    };

    const syncPrestasiUrl = () => {
      const params = new URLSearchParams();
      if (prestasiState.search) params.set('q', prestasiState.search);
      if (prestasiState.category) params.set('category', prestasiState.category);
      if (prestasiState.status) params.set('status', prestasiState.status);
      if (prestasiState.page > 1) params.set('page', String(prestasiState.page));
      if (prestasiState.perPage !== 25) params.set('per_page', String(prestasiState.perPage));
      const qs = params.toString();
      const url = qs ? `${location.pathname}?${qs}` : location.pathname;
      history.replaceState({}, '', url);
    };

    const loadPrestasiPage = async () => {
      try {
        const endpoint = Core.buildEndpoint(route('admin.prestasiList'), { page: prestasiState.page, per_page: prestasiState.perPage, status: prestasiState.status });
        const res = await fetch(endpoint, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) {
          const json = await res.json();
          prestasiState.items = json.data || [];
          prestasiState.total = Number(json.meta?.total || prestasiState.items.length);
          prestasiState.totalPages = Number(json.meta?.total_pages || 1);
          prestasiState.page = Number(json.meta?.page || prestasiState.page);
          return;
        }
      } catch (e) { /* fallback */ }
      // Fallback to static data
      if (window.GenBIData && window.GenBIData.prestasi) {
        prestasiState.items = window.GenBIData.prestasi;
        prestasiState.total = prestasiState.items.length;
        prestasiState.totalPages = 1;
      }
    };

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="cms-toolbar">
          <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-neutral-600">Show ${selectControl({ id: 'prestasi-per-page', className: 'admin-inline-select', value: String(prestasiState.perPage), options: ['10', '25', '50'] })} entries</label>
            ${selectControl({ id: 'prestasi-filter-category', className: 'admin-toolbar-select', value: prestasiState.category, options: [{ value: '', label: 'Semua Kategori' }, ...prestasiCategories.map((category) => ({ value: category, label: category }))] })}
            ${selectControl({ id: 'prestasi-filter-status', className: 'admin-toolbar-select', value: prestasiState.status, options: [{ value: '', label: 'Semua Status' }, { value: 'published', label: 'Published' }, { value: 'draft', label: 'Draft' }, { value: 'archived', label: 'Archived' }] })}
          </div>
          <label class="cms-search">${Admin.icon('search')}<input id="prestasi-search" placeholder="Search prestasi..." value="${escape(prestasiState.search)}" /></label>
        </div>
        <div class="admin-responsive-table mt-5">
          <table class="cms-table" id="prestasi-table">
            <thead>
              <tr>
                <th>SL</th>
                <th>Judul</th>
                <th>Nama Anggota</th>
                <th>Peringkat</th>
                <th>Tahun</th>
                <th>Penyelenggara</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="prestasi-tbody"></tbody>
          </table>
        </div>
        <div class="admin-pagination mt-5" id="prestasi-pagination" aria-label="Pagination prestasi"></div>
      </section>
    `;

    const filterAndRender = async () => {
      syncPrestasiUrl();
      await loadPrestasiPage();

      // Client-side filter for search and category (backend doesn't support q/category filter yet)
      let displayed = prestasiState.items;
      const search = prestasiState.search.toLowerCase();
      const category = prestasiState.category.toLowerCase();
      if (search || category) {
        displayed = displayed.filter(item => {
          const title = (item.title || '').toLowerCase();
          const name = (item.name || '').toLowerCase();
          const itemCategory = (item.category || '').toLowerCase();
          if (search && !title.includes(search) && !name.includes(search) && !itemCategory.includes(search)) return false;
          if (category && itemCategory !== category) return false;
          return true;
        });
      }

      const offset = (prestasiState.page - 1) * prestasiState.perPage;
      const tbody = document.querySelector('#prestasi-tbody');
      if (tbody) tbody.innerHTML = renderPrestasiRows(displayed, offset);
      renderAdminPagination('#prestasi-pagination', prestasiState.totalPages, prestasiState.page, (pg) => {
        prestasiState.page = pg;
        filterAndRender();
      });
      bindPrestasiDeleteButtons();
      bindPrestasiApproveButtons();
    };

    enhanceAdminSelects(body);
    document.querySelector('#prestasi-search')?.addEventListener('input', (event) => { prestasiState.search = event.target.value; prestasiState.page = 1; filterAndRender(); });
    document.querySelector('#prestasi-per-page')?.addEventListener('change', (event) => { prestasiState.perPage = Number(event.target.value) || 25; prestasiState.page = 1; filterAndRender(); });
    document.querySelector('#prestasi-filter-category')?.addEventListener('change', (event) => { prestasiState.category = event.target.value; prestasiState.page = 1; filterAndRender(); });
    document.querySelector('#prestasi-filter-status')?.addEventListener('change', (event) => { prestasiState.status = event.target.value; prestasiState.page = 1; filterAndRender(); });
    filterAndRender();
  }

  function renderPrestasiRows(items, offset = 0) {
    if (items.length === 0) {
      return '<tr><td colspan="8" class="text-center text-neutral-500 py-8">Belum ada data prestasi.</td></tr>';
    }
    return items.map((item, index) => {
      const id = item.id || item.prestasi_id || 0;
      const title = item.title || item.judul_prestasi || '';
      const name = item.name || item.nama_anggota || '';
      const category = item.category || item.kategori || '';
      const year = item.year || item.tahun || '';
      const institution = item.institution || item.institusi_penyelenggara || '';
      const status = item.status || 'draft';
      const image = item.image || item.foto_prestasi || '';
      const statusClass = status === 'published' ? 'cms-pill-green' : status === 'draft' ? 'cms-pill-yellow' : '';
      const approveButton = status !== 'published' ? `<button class="cms-action edit" data-approve-prestasi="${id}">Approve</button>` : '';

      return `
        <tr>
          <td>${offset + index + 1}</td>
          <td>
            <div class="flex items-center gap-3">
              ${image ? `<img src="${image.startsWith('http') || image.startsWith('/') ? image : '/uploads/prestasi/' + image}" class="table-thumb rounded" alt="${escape(title)}" />` : ''}
              <div>
                <strong>${escape(title)}</strong>
                <p class="mt-1 text-xs text-neutral-500">${escape(item.description || item.deskripsi_singkat || '').slice(0, 60)}</p>
              </div>
            </div>
          </td>
          <td>${escape(name)}</td>
          <td><span class="cms-pill">${escape(category)}</span></td>
          <td>${escape(year)}</td>
          <td>${escape(institution)}</td>
          <td><span class="cms-pill ${statusClass}">${status}</span></td>
          <td>
            <div class="flex gap-2">
              ${approveButton}
              <a href="${adminUrl('prestasi-edit')}?id=${id}" class="cms-action edit">Edit</a>
              <button class="cms-action delete" data-delete data-prestasi-id="${id}">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderAdminPagination(selector, totalPages, currentPage, onPageChange) {
    const root = document.querySelector(selector);
    if (!root) return;
    if (totalPages <= 1) {
      root.innerHTML = '';
      return;
    }
    const pages = Array.from({ length: totalPages }, (_, index) => index + 1);
    root.innerHTML = `
      <button class="pager-button" type="button" data-page="${Math.max(1, currentPage - 1)}" ${currentPage === 1 ? 'disabled' : ''}>Sebelumnya</button>
      ${pages.map((page) => `<button class="pager-button ${page === currentPage ? 'is-active' : ''}" type="button" data-page="${page}">${page}</button>`).join('')}
      <button class="pager-button" type="button" data-page="${Math.min(totalPages, currentPage + 1)}" ${currentPage === totalPages ? 'disabled' : ''}>Berikutnya</button>
    `;
    root.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => onPageChange(Number(button.dataset.page) || 1));
    });
  }

  function bindPrestasiApproveButtons() {
    document.querySelectorAll('[data-approve-prestasi]').forEach((button) => {
      button.addEventListener('click', async () => {
        if (button.disabled || button.getAttribute('aria-disabled') === 'true') return;
        const id = button.dataset.approvePrestasi;
        if (!id) return;
        const ok = await Admin.showConfirm({
          title: 'Approve prestasi?',
          message: 'Prestasi akan dipublikasikan dan tampil di halaman publik.',
          confirmText: 'Approve',
        });
        if (!ok) return;
        button.disabled = true;
        try {
          const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(route('admin.prestasiUpdate', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify({ status: 'published', _csrf_token: token })
          });
          const json = await res.json().catch(() => ({}));
          if (!res.ok) throw new Error(json.error || 'Gagal approve prestasi.');
          Admin.showToast('Prestasi berhasil dipublikasikan.');
          const row = button.closest('tr');
          const pill = row?.querySelector('.admin-cell-status .cms-pill, td:nth-child(7) .cms-pill');
          if (pill) {
            pill.textContent = 'Published';
            pill.classList.add('cms-pill-green');
            pill.classList.remove('cms-pill-yellow');
          }
          button.remove();
        } catch (e) {
          Admin.showToast(e.message || 'Gagal approve prestasi.');
          button.disabled = false;
        }
      });
    });
  }

  function bindPrestasiDeleteButtons() {
    document.querySelectorAll('[data-delete][data-prestasi-id], [data-delete-prestasi]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.prestasiId || button.dataset.deletePrestasi;
        const ok = await Admin.showConfirm({
          title: 'Hapus prestasi?',
          message: 'Prestasi akan dihapus (soft delete). Data masih bisa dipulihkan.',
          confirmText: 'Hapus',
          danger: true
        });
        if (!ok) return;
        try {
          const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(route('admin.prestasiDelete', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin'
          });
          if (res.ok) {
            Admin.showToast('Prestasi berhasil dihapus.');
            button.closest('tr')?.remove();
          } else {
            const json = await res.json().catch(() => ({}));
            Admin.showToast(json.error || 'Gagal menghapus prestasi.');
          }
        } catch (e) {
          Admin.showToast('Gagal menghapus prestasi.');
        }
      });
    });
  }

  async function loadPrestasiMemberOptions(currentName = '') {
    try {
      const payload = await API.getTeamList({ per_page: 200 });
      const members = Array.isArray(payload?.members) ? payload.members : [];
      const uniqueMembers = [];
      const seen = new Set();
      members.forEach((member) => {
        const name = String(member.name || '').trim();
        const key = name.toLowerCase();
        if (!name || seen.has(key)) return;
        seen.add(key);
        uniqueMembers.push(member);
      });
      if (currentName && !seen.has(currentName.toLowerCase())) {
        uniqueMembers.unshift({ name: currentName, role: 'Data tersimpan' });
      }
      return uniqueMembers;
    } catch (error) {
      return currentName ? [{ name: currentName, role: 'Data tersimpan' }] : [];
    }
  }

  function buildPrestasiTitle() {
    const name = document.querySelector('#prestasi-member-search')?.value?.trim() || '';
    const rank = document.querySelector('#prestasi-category')?.value?.trim() || '';
    const institution = document.querySelector('#prestasi-institution')?.value?.trim() || '';
    return [rank, institution, name ? `- ${name}` : ''].filter(Boolean).join(' ');
  }

  function getPrestasiGalleryUrls() {
    const field = document.querySelector('#prestasi-gallery-json');
    try {
      const parsed = JSON.parse(field?.value || '[]');
      return Array.isArray(parsed) ? parsed.map((url) => String(url || '').trim()).filter(Boolean) : [];
    } catch (e) {
      return [];
    }
  }

  function setPrestasiGalleryUrls(urls) {
    const normalized = [];
    urls.forEach((url) => {
      const value = String(url || '').trim();
      if (value && !normalized.includes(value)) normalized.push(value);
    });
    const imageUrl = document.querySelector('#prestasi-image-url')?.value?.trim() || '';
    if (imageUrl && !normalized.includes(imageUrl)) normalized.unshift(imageUrl);
    const field = document.querySelector('#prestasi-gallery-json');
    if (field) field.value = JSON.stringify(normalized);
    renderPrestasiGalleryList(normalized);
  }

  function renderPrestasiGalleryList(urls = getPrestasiGalleryUrls()) {
    const list = document.querySelector('#prestasi-gallery-list');
    const status = document.querySelector('#prestasi-gallery-status');
    const counter = document.querySelector('#prestasi-gallery-counter');
    const preview = document.querySelector('#prestasi-gallery-preview');
    const empty = document.querySelector('[data-prestasi-empty]');
    const scrollLeft = document.querySelector('#prestasi-gallery-scroll-left');
    const scrollRight = document.querySelector('#prestasi-gallery-scroll-right');
    if (!list) return;
    if (status) status.textContent = urls.length ? `${urls.length} foto dipilih. Geser horizontal untuk melihat semua foto.` : 'Belum ada galeri tambahan.';
    if (counter) counter.textContent = urls.length ? `${urls.length} foto` : '0 foto';
    preview?.classList.toggle('hidden', !urls.length);
    empty?.classList.toggle('hidden', !!urls.length);
    const scrollable = urls.length > 1;
    if (scrollLeft) scrollLeft.disabled = !scrollable;
    if (scrollRight) scrollRight.disabled = !scrollable;
    if (!urls.length) {
      list.innerHTML = '';
      return;
    }
    list.innerHTML = urls.map((url, index) => `
      <article class="public-upload-preview-card admin-upload-gallery-item">
        <img src="${escape(url)}" alt="Foto prestasi ${index + 1}" loading="lazy" />
        <div>
          <strong>${index === 0 ? 'Foto utama' : `Foto ${index + 1}`}</strong>
          <button type="button" class="cms-action delete" data-remove-prestasi-image="${escape(url)}">Hapus</button>
        </div>
      </article>
    `).join('');
    list.querySelectorAll('[data-remove-prestasi-image]').forEach((button) => {
      button.addEventListener('click', () => {
        const removeUrl = button.dataset.removePrestasiImage || '';
        const next = getPrestasiGalleryUrls().filter((url) => url !== removeUrl);
        const mainField = document.querySelector('#prestasi-image-url');
        if (mainField?.value === removeUrl) mainField.value = next[0] || '';
        setPrestasiGalleryUrls(next);
        updatePrestasiMainPreview();
      });
    });
  }

  function updatePrestasiMainPreview() {
    const url = document.querySelector('#prestasi-image-url')?.value?.trim() || '';
    const urls = getPrestasiGalleryUrls();
    renderPrestasiGalleryList(url ? (urls.includes(url) ? urls : [url, ...urls]) : urls);
  }

  async function readPrestasiUploadResponse(response, fileName = 'file') {
    const json = await response.json().catch(() => ({}));
    if (response.ok && json.data?.url) return json.data.url;
    const details = Array.isArray(json.details) ? json.details.join(', ') : '';
    const reason = [json.error || `Upload ${fileName} gagal`, details].filter(Boolean).join(': ');
    throw new Error(reason || `Upload ${fileName} gagal dengan status ${response.status}.`);
  }

  function bindPrestasiImageUpload() {
    const input = document.querySelector('#prestasi-image-file');
    const button = document.querySelector('#prestasi-upload-btn');
    const imageUrl = document.querySelector('#prestasi-image-url');
    if (!input || !button || !imageUrl) return;

    renderPrestasiGalleryList();
    button.addEventListener('click', () => input.click());
    imageUrl.addEventListener('change', () => setPrestasiGalleryUrls(getPrestasiGalleryUrls()));
    const galleryList = document.querySelector('#prestasi-gallery-list');
    document.querySelector('#prestasi-gallery-scroll-left')?.addEventListener('click', () => galleryList?.scrollBy({ left: -240, behavior: 'smooth' }));
    document.querySelector('#prestasi-gallery-scroll-right')?.addEventListener('click', () => galleryList?.scrollBy({ left: 240, behavior: 'smooth' }));
    input.addEventListener('change', async (e) => {
      const files = Array.from(e.target.files || []);
      if (!files.length) return;
      const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const uploaded = [];
      const failures = [];
      const status = document.querySelector('#prestasi-gallery-status');
      if (status) status.textContent = 'Mengupload foto prestasi...';
      button.disabled = true;
      for (const file of files.slice(0, 6)) {
        const formData = new FormData();
        formData.append('image', file);
        try {
          const res = await fetch(route('admin.prestasiUpload'), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: formData
          });
          uploaded.push(await readPrestasiUploadResponse(res, file.name));
        } catch (err) {
          failures.push(`${file.name}: ${err.message || 'Upload gagal.'}`);
        }
      }
      button.disabled = false;
      if (uploaded.length) {
        if (!imageUrl.value.trim()) imageUrl.value = uploaded[0];
        setPrestasiGalleryUrls([...getPrestasiGalleryUrls(), ...uploaded]);
        updatePrestasiMainPreview();
        if (failures.length) {
          const message = `Foto berhasil sebagian. Gagal: ${failures.slice(0, 2).join(' | ')}`;
          if (status) status.textContent = message;
          Admin.showToast(message);
        } else {
          Admin.showToast('Foto berhasil diupload.');
        }
      } else {
        const message = failures[0] || 'Gagal upload foto.';
        if (status) status.textContent = message;
        Admin.showToast(`Gagal upload foto: ${message}`);
      }
      input.value = '';
    });
  }

  function buildPrestasiSeo(payload) {
    const title = payload.title || buildPrestasiTitle() || 'Prestasi GenBI Jambi';
    const member = payload.name ? ` ${payload.name}` : '';
    const category = payload.category || 'Prestasi';
    const year = payload.year || new Date().getFullYear();
    const institution = payload.institution ? ` oleh ${payload.institution}` : '';
    return {
      meta_title: payload.meta_title || `${title} | GenBI Jambi`,
      meta_keyword: payload.meta_keyword || [category, 'prestasi GenBI Jambi', payload.name, payload.institution, year].filter(Boolean).join(', '),
      meta_description: payload.meta_description || payload.description || `${category}${member}${institution} tahun ${year}. Dokumentasi prestasi GenBI Jambi.`
    };
  }

  function bindPrestasiSeoAutofill() {
    const fields = {
      meta_title: document.querySelector('#prestasi-meta-title'),
      meta_keyword: document.querySelector('#prestasi-meta-keyword'),
      meta_description: document.querySelector('#prestasi-meta-desc'),
    };
    const touched = { meta_title: false, meta_keyword: false, meta_description: false };
    Object.entries(fields).forEach(([key, field]) => {
      field?.addEventListener('input', () => { touched[key] = true; });
    });
    const sourceSelectors = ['#prestasi-member-search', '#prestasi-category', '#prestasi-institution', '#prestasi-year', '#prestasi-desc-field'];
    const refresh = () => {
      const payload = {
        name: document.querySelector('#prestasi-member-search')?.value?.trim() || '',
        title: buildPrestasiTitle(),
        category: document.querySelector('#prestasi-category')?.value?.trim() || '',
        year: document.querySelector('#prestasi-year')?.value?.trim() || '',
        description: document.querySelector('#prestasi-desc-field')?.value?.trim() || '',
        institution: document.querySelector('#prestasi-institution')?.value?.trim() || '',
        meta_title: '',
        meta_keyword: '',
        meta_description: '',
      };
      const generated = buildPrestasiSeo(payload);
      Object.entries(fields).forEach(([key, field]) => {
        if (field && !touched[key] && !field.value.trim()) field.value = generated[key] || '';
      });
    };
    sourceSelectors.forEach((selector) => document.querySelector(selector)?.addEventListener('input', refresh));
    refresh();
  }

  function appendPrestasiGalleryToContent(content) {
    const cleanContent = String(content || '').replace(/\n?Dokumentasi\s*:\s*[^\n<]*/iu, '').trim();
    const urls = getPrestasiGalleryUrls();
    if (!urls.length) return cleanContent;
    return `${cleanContent}${cleanContent ? '\n\n' : ''}Dokumentasi: ${urls.join(', ')}`;
  }

  async function renderPrestasiEditor(isEdit) {
    const id = Number(new URLSearchParams(location.search).get('id')) || 0;

    // Check if SSR form markup exists - if so, hydrate instead of rebuilding
    const ssrForm = document.querySelector('#prestasi-editor-form[data-ssr="true"]');
    if (ssrForm) {
      const ssrIsEdit = ssrForm.dataset.edit === '1';
      const ssrItemId = Number(ssrForm.dataset.itemId) || 0;

      // Load member options for datalist
      const memberOptions = await loadPrestasiMemberOptions(
        document.querySelector('#prestasi-member-search')?.value?.trim() || ''
      );
      const datalist = document.querySelector('#prestasi-member-list');
      if (datalist) {
        datalist.innerHTML = memberOptions.map(member =>
          `<option value="${escape(member.name)}">${escape(member.role || member.division || '')}</option>`
        ).join('');
      }

      const editor = initPrestasiEditor({
        content: document.querySelector('#prestasi-content-field')?.value || '',
      });
      bindPrestasiImageUpload();
      bindPrestasiSeoAutofill();

      // Enhance custom selects
      enhanceAdminSelects(document.querySelector('#cms-body') || document);

      // Bind form submission
      bindPrestasiFormSubmit(ssrIsEdit, ssrItemId, editor);
      return;
    }

    let item = {
      title: '',
      name: '',
      category: '',
      year: new Date().getFullYear().toString(),
      description: '',
      content: '',
      image: '',
      institution: '',
      status: 'draft',
      meta_title: '',
      meta_description: '',
      meta_keyword: '',
    };

    // Load from backend if editing
    if (isEdit && id > 0) {
      try {
        const res = await fetch(route('admin.prestasiShow', { id }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) {
          const json = await res.json();
          const d = json.data || {};
          item = {
            ...item,
            id: d.id || d.prestasi_id,
            title: d.title || d.judul_prestasi || '',
            name: d.name || d.nama_anggota || '',
            category: d.category || d.kategori || '',
            year: d.year || d.tahun || '',
            description: d.description || d.deskripsi_singkat || '',
            content: d.content || d.deskripsi_detail || '',
            image: d.image || d.foto_prestasi || '',
            images: Array.isArray(d.images) ? d.images : [],
            institution: d.institution || '',
            status: d.status || 'draft',
            meta_title: d.meta_title || '',
            meta_description: d.meta_description || '',
            meta_keyword: d.meta_keyword || '',
          };
        }
      } catch (e) {
        // Fallback: use empty form
      }
    }

    const body = renderShell(
      isEdit ? 'Edit Prestasi' : 'Add Prestasi',
      'Kelola data prestasi anggota GenBI Jambi.',
      `<a href="${adminUrl('prestasi')}" class="btn btn-secondary">View All</a>`
    );

    const memberOptions = await loadPrestasiMemberOptions(item.name);

    body.innerHTML = `
      <form class="prestasi-editor-form" id="prestasi-editor-form">
        <div class="editor-config-sidebar prestasi-config-panel">
          <section class="config-card medium-config-card">
            <h2>Informasi Prestasi</h2>
            ${control('Nama', `<input class="config-input" id="prestasi-member-search" list="prestasi-member-list" value="${escape(item.name)}" placeholder="Cari nama anggota..." autocomplete="off" />
              <datalist id="prestasi-member-list">${memberOptions.map(member => `<option value="${escape(member.name)}">${escape(member.role || member.division || '')}</option>`).join('')}</datalist>`)}
            ${control('Penyelenggara', `<input class="config-input" id="prestasi-institution" value="${escape(item.institution)}" placeholder="Nama penyelenggara" />`)}
            ${control('Tahun', `<input class="config-input" id="prestasi-year" type="number" min="1900" max="2099" step="1" value="${escape(item.year)}" placeholder="2026" />`)}
            ${control('Kategori', `<input class="config-input" id="prestasi-category" value="${escape(item.category)}" list="prestasi-rank-list" placeholder="Tulis kategori atau pilih rekomendasi" />
              <datalist id="prestasi-rank-list">${prestasiCategories.map(c => `<option value="${escape(c)}"></option>`).join('')}</datalist>`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Deskripsi</h2>
            ${control('Deskripsi Singkat', `<textarea class="config-input" id="prestasi-desc-field" rows="5" placeholder="Tulis deskripsi singkat prestasi...">${escape(item.description)}</textarea>`)}
          </section>
          <section class="config-card medium-config-card prestasi-photo-card prestasi-photo-uploader">
            <h2>Foto Prestasi</h2>
            <div class="public-upload-field public-prestasi-photo-card prestasi-photo-uploader" data-prestasi-upload-field>
              <div class="public-upload-empty ${item.image ? 'hidden' : ''}" data-prestasi-empty><strong>Belum ada foto</strong></div>
              <input id="prestasi-image-file" class="hidden" type="file" accept="image/*" multiple />
              <button type="button" id="prestasi-upload-btn" class="btn btn-secondary w-full">Upload Foto</button>
              <input class="config-input" id="prestasi-image-url" value="${escape(item.image)}" placeholder="URL gambar (opsional)" />
              <input type="hidden" id="prestasi-gallery-json" value="${escape(JSON.stringify(item.images || (item.image ? [item.image] : [])))}" />
              <p id="prestasi-gallery-status" class="config-hint">Pilih beberapa foto sekaligus. Foto pertama menjadi foto utama.</p>
              <div class="public-upload-preview" id="prestasi-gallery-preview">
                <div class="public-upload-preview-controls">
                  <strong>Preview foto terpilih</strong>
                  <span id="prestasi-gallery-counter">0 foto</span>
                </div>
                <div class="public-upload-preview-slider">
                  <button type="button" class="public-upload-scroll" id="prestasi-gallery-scroll-left" aria-label="Geser preview ke kiri">‹</button>
                  <div id="prestasi-gallery-list" class="admin-upload-gallery public-upload-preview-strip" aria-label="Preview foto prestasi"></div>
                  <button type="button" class="public-upload-scroll" id="prestasi-gallery-scroll-right" aria-label="Geser preview ke kanan">›</button>
                </div>
              </div>
            </div>
          </section>
          <section class="config-card medium-config-card">
            <h2>SEO Information</h2>
            ${control('Meta Title', `<input class="config-input" id="prestasi-meta-title" value="${escape(item.meta_title || item.title)}" />`)}
            ${control('Meta Keywords', `<textarea class="config-input" id="prestasi-meta-keyword" rows="3">${escape(item.meta_keyword || `${item.category}, GenBI Jambi, prestasi`)}</textarea>`)}
            ${control('Meta Description', `<textarea class="config-input" id="prestasi-meta-desc" rows="4">${escape(item.meta_description || item.description)}</textarea>`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Status</h2>
            ${control('Publish Status', selectControl({ id: 'prestasi-status', value: item.status || 'draft', options: [{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }, { value: 'archived', label: 'Archived' }] }))}
          </section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update Prestasi' : 'Submit Prestasi'}</button>
        </div>
      </form>
    `;

    enhanceAdminSelects(body);
    bindPrestasiImageUpload();
    bindPrestasiSeoAutofill();

    // Form submission
    bindPrestasiFormSubmit(isEdit, id, null);
  }

  function blocksToPrestasiHtml(blocks = []) {
    return blocksToNewsHtml(blocks);
  }

  function fallbackPrestasiContent() {
    return document.querySelector('#prestasi-content-field')?.value?.trim()
      || document.querySelector('#prestasi-desc-field')?.value?.trim()
      || '';
  }

  function bindPrestasiFormSubmit(isEdit, itemId, editor = null) {
    document.querySelector('#prestasi-editor-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();

      let contentHtml = fallbackPrestasiContent();
      if (editor?.save) {
        try {
          const outputData = await editor.save();
          const savedHtml = blocksToPrestasiHtml(outputData.blocks || []);
          if (savedHtml.trim()) contentHtml = savedHtml;
        } catch (error) {
          contentHtml = fallbackPrestasiContent();
        }
      }

      const ok = await Admin.showConfirm({
        title: isEdit ? 'Update prestasi?' : 'Submit prestasi?',
        message: isEdit ? 'Data prestasi akan diperbarui di database.' : 'Prestasi baru akan disimpan ke database.',
        confirmText: isEdit ? 'Update' : 'Submit'
      });
      if (!ok) return;

      const descField = document.querySelector('#prestasi-desc-field');

      const payload = {
        name: document.querySelector('#prestasi-member-search')?.value?.trim() || '',
        title: buildPrestasiTitle(),
        category: document.querySelector('#prestasi-category')?.value || '',
        year: document.querySelector('#prestasi-year')?.value || '',
        description: descField?.value?.trim() || '',
        content: appendPrestasiGalleryToContent(contentHtml),
        image: document.querySelector('#prestasi-image-url')?.value?.trim() || '',
        institution: document.querySelector('#prestasi-institution')?.value?.trim() || '',
        status: document.querySelector('#prestasi-status')?.value || 'draft',
        meta_title: document.querySelector('#prestasi-meta-title')?.value?.trim() || '',
        meta_keyword: document.querySelector('#prestasi-meta-keyword')?.value?.trim() || '',
        meta_description: document.querySelector('#prestasi-meta-desc')?.value?.trim() || '',
      };
      Object.assign(payload, buildPrestasiSeo(payload));

      const csrfToken = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const url = isEdit ? route('admin.prestasiUpdate', { id: itemId }) : route('admin.prestasiStore');
      if (isEdit && !itemId) {
        Admin.showToast('ID prestasi tidak valid. Buka ulang halaman edit dari daftar prestasi.', 'error');
        return;
      }

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
          credentials: 'same-origin',
          body: JSON.stringify({ ...payload, _csrf_token: csrfToken })
        });
        const result = await res.json();
        if (res.ok) {
          Admin.showToast(isEdit ? 'Prestasi berhasil diperbarui.' : 'Prestasi berhasil ditambahkan.');
          if (!isEdit && result.data?.id) {
            setTimeout(() => { window.location.href = `${adminUrl('prestasi-edit')}?id=${result.data.id}`; }, 1200);
          }
        } else {
          const details = result.details ? result.details.join(', ') : '';
          Admin.showToast(`${result.error || 'Gagal menyimpan prestasi.'}${details ? ' ' + details : ''} (HTTP ${res.status})`, 'error');
        }
      } catch (e) {
        Admin.showToast(`Gagal menyimpan prestasi. ${e?.message || 'Periksa koneksi.'}`, 'error');
      }
    });
  }

  function bindPrestasiDetailButtons() {
    document.querySelectorAll('[data-detail-prestasi]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.detailPrestasi;
        if (!id) return;
        try {
          const csrfToken = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(route('admin.prestasiShow', { id }), {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
          });
          if (!res.ok) {
            Admin.showToast('Gagal memuat detail prestasi.');
            return;
          }
          const json = await res.json();
          const item = json.data;
          if (!item) {
            Admin.showToast('Data prestasi tidak ditemukan.');
            return;
          }
          const statusClass = (item.status || 'draft') === 'published' ? 'cms-pill-green' : (item.status || 'draft') === 'draft' ? 'cms-pill-yellow' : '';
          await Admin.showConfirm({
            title: 'Detail Prestasi',
            message: `<div class="text-left mt-3">
              ${item.image ? `<img src="${escape(item.image)}" class="w-full max-h-48 object-cover rounded-xl mb-4" alt="${escape(item.title || '')}" />` : ''}
              <h3 class="text-lg font-bold text-neutral-950">${escape(item.title || '')}</h3>
              <div class="mt-3 grid gap-2 text-sm">
                <div><strong>Nama:</strong> ${escape(item.name || '')}</div>
                <div><strong>Kategori:</strong> ${escape(item.category || '')}</div>
                <div><strong>Tahun:</strong> ${escape(item.year || '')}</div>
                <div><strong>Penyelenggara:</strong> ${escape(item.institution || '')}</div>
                <div><strong>Komisariat:</strong> ${escape(item.campus || '')}</div>
                <div><strong>Status:</strong> <span class="cms-pill ${statusClass}">${escape(item.status || 'draft')}</span></div>
              </div>
              ${item.description ? `<div class="mt-4 text-sm text-neutral-600">${escape(item.description)}</div>` : ''}
              ${item.content && item.content !== item.description ? `<div class="prose-soft mt-4 border-t border-neutral-900/10 pt-4">${item.content}</div>` : ''}
            </div>`,
            confirmText: 'Edit',
            cancelText: 'Tutup',
            html: true,
            panelClass: 'is-wide'
          }).then((edit) => {
            if (edit) {
              window.location.href = `${adminUrl('prestasi-edit')}?id=${item.id || item.prestasi_id}`;
            }
          });
        } catch (e) {
          Admin.showToast('Gagal memuat detail prestasi.');
        }
      });
    });
  }

  // ─── Prestasi Token Management ──────────────────────────────────────────────

  const PRESTASI_TOKEN_URL_CACHE_KEY = 'genbi.prestasiTokenUrls';

  function readPrestasiTokenUrlCache() {
    try {
      const parsed = JSON.parse(window.sessionStorage.getItem(PRESTASI_TOKEN_URL_CACHE_KEY) || '{}');
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (e) {
      return {};
    }
  }

  function writePrestasiTokenUrlCache(cache) {
    try {
      window.sessionStorage.setItem(PRESTASI_TOKEN_URL_CACHE_KEY, JSON.stringify(cache));
    } catch (e) {
      // Ignore storage failures and keep copy support best-effort.
    }
  }

  function cachePrestasiTokenUrl(tokenId, submitUrl) {
    if (!tokenId || !submitUrl) return;
    const cache = readPrestasiTokenUrlCache();
    cache[String(tokenId)] = submitUrl;
    writePrestasiTokenUrlCache(cache);
  }

  function getPrestasiTokenUrl(tokenId) {
    if (!tokenId) return '';
    const cache = readPrestasiTokenUrlCache();
    return typeof cache[String(tokenId)] === 'string' ? cache[String(tokenId)] : '';
  }

  async function copyPrestasiTokenUrl(submitUrl) {
    if (!submitUrl) {
      Admin.showToast('URL token ini tidak tersedia lagi. Generate ulang jika perlu.', 'error');
      return;
    }

    try {
      await navigator.clipboard.writeText(submitUrl);
      Admin.showToast('Link token disalin ke clipboard.');
    } catch (e) {
      Admin.showToast('Gagal menyalin. Silakan coba lagi.', 'error');
    }
  }

  async function renderPrestasiTokenList(generated = null) {
    if (!generated && document.querySelector('#admin-prestasi-token-list[data-ssr="true"]')) {
      document.querySelector('#generate-token-btn')?.addEventListener('click', () => showGenerateModal());
      bindTokenRevokeButtons();
      return;
    }
    const body = renderShell(
      'Prestasi Token',
      'Generate dan kelola token form prestasi sekali pakai untuk dibagikan ke anggota yang mengisi dari luar admin.',
      `<button id="generate-token-btn" class="btn btn-primary">Buat Link Form Prestasi</button>`
    );
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data token...</div>';

    let items = [];
    try {
      const res = await fetch(route('admin.prestasiTokens'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (res.ok) {
        const json = await res.json();
        items = json.data || [];
      }
    } catch (e) { /* fallback empty */ }

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="admin-responsive-table">
          <table class="cms-table" id="token-table">
            <thead>
              <tr>
                <th>SL</th>
                <th>Label</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Kedaluwarsa</th>
                <th>Digunakan</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="token-tbody">
              ${renderTokenRows(items)}
            </tbody>
          </table>
        </div>
      </section>
      <div id="generated-token-display" class="mt-6 ${generated?.url ? '' : 'hidden'}">
        <section class="admin-card p-6 border-2 border-green-200 bg-green-50">
          <h3 class="text-lg font-bold text-green-900">Token Berhasil Dibuat</h3>
          <p class="mt-2 text-sm text-green-800">Salin link di bawah. Token hanya ditampilkan sekali dan tidak bisa dilihat lagi.</p>
          <div class="mt-4 flex items-center gap-3">
            <input id="generated-token-url" class="config-input flex-1 font-mono text-sm" readonly />
            <button id="copy-token-url" class="btn btn-primary">Copy</button>
          </div>
        </section>
      </div>
    `;

    // Bind generate button
    document.querySelector('#generate-token-btn')?.addEventListener('click', () => showGenerateModal());

    if (generated?.url) {
      const urlInput = document.querySelector('#generated-token-url');
      if (urlInput) {
        urlInput.value = generated.url;
      }
      document.querySelector('#copy-token-url')?.addEventListener('click', async () => {
        await copyPrestasiTokenUrl(generated.url);
      });
    }

    // Bind revoke buttons
    bindTokenRevokeButtons();
    bindTokenCopyButtons();
  }

  function renderTokenRows(items) {
    if (items.length === 0) {
      return '<tr><td colspan="7" class="text-center text-neutral-500 py-8">Belum ada token.</td></tr>';
    }
    return items.map((item, index) => {
      const status = item.status || 'active';
      const statusClass = status === 'active' ? 'cms-pill-green' : status === 'used' ? 'cms-pill-yellow' : 'cms-pill-red';
      const tokenId = item.id || item.token_id;
      const rowUrl = item.submit_url ? new URL(item.submit_url, window.location.origin).toString() : '';
      const cachedUrl = rowUrl || getPrestasiTokenUrl(tokenId);
      return `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${escape(item.label || '')}</strong></td>
          <td><span class="cms-pill ${statusClass}">${status}</span></td>
          <td class="text-xs">${item.created_at || '-'}</td>
          <td class="text-xs">${item.expires_at || 'Tidak ada'}</td>
          <td class="text-xs">${item.used_at || '-'}</td>
          <td>
            <div class="flex flex-wrap gap-2">
              <button class="cms-action edit" data-copy-token-url data-token-id="${tokenId}" data-token-url="${escape(cachedUrl)}">Copy URL</button>
              ${status === 'active' ? `<button class="cms-action delete" data-revoke data-token-id="${tokenId}">Revoke</button>` : '<span class="text-neutral-400 text-xs">-</span>'}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function bindTokenCopyButtons() {
    document.querySelectorAll('[data-copy-token-url][data-token-id]').forEach((button) => {
      button.addEventListener('click', async () => {
        const submitUrl = button.dataset.tokenUrl || getPrestasiTokenUrl(button.dataset.tokenId);
        await copyPrestasiTokenUrl(submitUrl);
      });
    });
  }

  function bindTokenRevokeButtons() {
    document.querySelectorAll('[data-revoke][data-token-id]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.tokenId;
        const ok = await Admin.showConfirm({
          title: 'Revoke token?',
          message: 'Token akan dinonaktifkan dan tidak bisa digunakan lagi.',
          confirmText: 'Revoke',
          danger: true
        });
        if (!ok) return;
        try {
          const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(route('admin.prestasiTokenRevoke', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin'
          });
          if (res.ok) {
            Admin.showToast('Token berhasil direvoke.');
            if (document.querySelector('#admin-prestasi-token-list[data-ssr="true"]')) window.location.reload();
            else renderPrestasiTokenList();
          } else {
            Admin.showToast('Gagal merevoke token.');
          }
        } catch (e) {
          Admin.showToast('Gagal merevoke token.');
        }
      });
    });
  }

  async function showGenerateModal() {
    const ok = await Admin.showConfirm({
      title: 'Generate Token Baru',
      message: `<div class="text-left mt-3">
        <label class="config-field"><span>Label / Keterangan</span><input id="token-label-input" class="config-input" placeholder="Contoh: Form prestasi KTI 2025" /></label>
        <label class="config-field mt-3"><span>Kedaluwarsa (opsional)</span><input id="token-expires-input" class="config-input" type="datetime-local" /></label>
      </div>`,
      confirmText: 'Generate',
      cancelText: 'Batal',
      html: true,
      panelClass: 'is-wide'
    });
    if (!ok) return;

    const label = document.querySelector('#token-label-input')?.value?.trim() || '';
    const expiresAt = document.querySelector('#token-expires-input')?.value || '';

    if (!label) {
      Admin.showToast('Label wajib diisi.');
      return;
    }

    try {
      const csrfToken = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const res = await fetch(route('admin.prestasiTokens'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        credentials: 'same-origin',
        body: JSON.stringify({ label, expires_at: expiresAt || null })
      });
      let result = null;
      try {
        result = await res.json();
      } catch (parseError) {
        result = null;
      }

      if (res.ok && result.data?.token) {
        const baseUrl = window.location.origin;
        const submitUrl = result.data.submit_url
          ? new URL(result.data.submit_url, baseUrl).toString()
          : `${baseUrl}${route('public.prestasiSubmit', { token: result.data.token })}`;
        cachePrestasiTokenUrl(result.data.id, submitUrl);
        await renderPrestasiTokenList({ id: result.data.id, url: submitUrl });
        Admin.showToast('Token berhasil dibuat. Salin link sekarang!');
      } else {
        Admin.showToast(result?.error || 'Gagal membuat token.');
      }
    } catch (e) {
      Admin.showToast('Gagal membuat token. Periksa koneksi.');
    }
  }

  function initPrestasiEditor(item) {
    const holder = document.querySelector('#prestasi-editor');
    const fallback = document.querySelector('#prestasi-editor-fallback');
    if (!holder) return null;

    if (!window.EditorJS) {
      holder.classList.add('hidden');
      fallback?.classList.remove('hidden');
      return null;
    }

    // Parse existing content into blocks
    const initialBlocks = [];
    if (item.content) {
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = item.content;
      tempDiv.childNodes.forEach(node => {
        if (node.nodeType === 3 && node.textContent.trim()) {
          initialBlocks.push({ type: 'paragraph', data: { text: node.textContent.trim() } });
        } else if (node.nodeType === 1) {
          const tag = node.tagName.toLowerCase();
          if (['h1', 'h2', 'h3', 'h4'].includes(tag)) {
            initialBlocks.push({ type: 'header', data: { text: node.innerHTML, level: parseInt(tag[1]) } });
          } else if (tag === 'blockquote') {
            initialBlocks.push({ type: 'quote', data: { text: node.textContent } });
          } else if (tag === 'figure' || tag === 'img') {
            const img = tag === 'img' ? node : node.querySelector('img');
            if (img) initialBlocks.push({ type: 'image', data: { file: { url: img.src }, caption: img.alt || '' } });
          } else if (['ul', 'ol'].includes(tag)) {
            const listItems = Array.from(node.querySelectorAll('li')).map(li => li.innerHTML);
            initialBlocks.push({ type: 'list', data: { style: tag === 'ol' ? 'ordered' : 'unordered', items: listItems } });
          } else {
            initialBlocks.push({ type: 'paragraph', data: { text: node.innerHTML || node.textContent } });
          }
        }
      });
    }
    if (initialBlocks.length === 0) {
      initialBlocks.push({ type: 'paragraph', data: { text: '' } });
    }

    const tools = {};
    if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 3 } };
    const ListTool = window.EditorjsList || window.List;
    if (ListTool) tools.list = { class: ListTool, inlineToolbar: true };
    if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true };
    if (window.ImageTool) {
      tools.image = {
        class: window.ImageTool,
        config: {
          uploader: {
            async uploadByFile(file) {
              const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
              const formData = new FormData();
              formData.append('image', file);
              try {
                const res = await fetch(route('admin.prestasiUpload'), {
                  method: 'POST',
                  headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                  credentials: 'same-origin',
                  body: formData
                });
                return { success: 1, file: { url: await readPrestasiUploadResponse(res, file.name) } };
              } catch (e) {
                Admin.showToast?.(`Gagal upload gambar prestasi: ${e.message || 'Upload gagal.'}`);
              }
              return { success: 0 };
            },
            async uploadByUrl(url) {
              return { success: 1, file: { url } };
            }
          }
        }
      };
    }

    try {
      return new window.EditorJS({
        holder: 'prestasi-editor',
        tools,
        data: { blocks: initialBlocks },
        placeholder: 'Tulis detail prestasi...',
      });
    } catch (e) {
      holder.classList.add('hidden');
      fallback?.classList.remove('hidden');
      return null;
    }
  }

  // Bind multi-select dropdown for SSR pages
  function bindAdminMultiSelect() {
    const multiSelect = document.querySelector('.admin-multi-select[data-ssr="true"]');
    if (!multiSelect) return;

    const button = multiSelect.querySelector('.admin-multi-select-button');
    const menu = multiSelect.querySelector('.admin-multi-select-menu');
    const label = multiSelect.querySelector('#category-label');
    const checkboxes = multiSelect.querySelectorAll('input[type="checkbox"]');
    const clearBtn = multiSelect.querySelector('.admin-multi-select-clear');
    const applyBtn = multiSelect.querySelector('.admin-multi-select-apply');

    if (!button || !menu) return;

    if (multiSelect.dataset.dropdownReady !== '1' && UI?.createDropdownController) {
      multiSelect.dataset.dropdownReady = '1';
      UI.createDropdownController({
        root: multiSelect,
        button,
        menu,
        portalTarget: document.body,
        offset: 8,
      });
    }

    // Update label when checkboxes change
    const updateLabel = () => {
      const checked = Array.from(checkboxes).filter(cb => cb.checked);
      if (label) {
        label.textContent = checked.length === 0 ? 'Semua Kategori' : `${checked.length} dipilih`;
      }
    };

    checkboxes.forEach(cb => cb.addEventListener('change', updateLabel));

    // Clear button
    if (clearBtn) {
      clearBtn.addEventListener('click', (e) => {
        e.preventDefault();
        checkboxes.forEach(cb => cb.checked = false);
        updateLabel();
      });
    }

    // Apply button submits the form
    if (applyBtn) {
      applyBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const form = multiSelect.closest('form');
        if (form) form.submit();
      });
    }
  }

  // Bind team batch mode toggle for SSR pages
  function bindTeamBatchMode() {
    const batchToggle = document.querySelector('#team-batch-toggle');
    const batchBar = document.querySelector('#team-batch-bar');
    const teamList = document.querySelector('#admin-team-list[data-ssr="true"]');
    const clearBtn = document.querySelector('#team-selection-clear');
    
    if (!batchToggle || !batchBar || !teamList) return;

    let batchMode = false;
    const selection = new Set();

    const updateCount = () => {
      const selectionCount = document.querySelector('#team-selection-count');
      if (selectionCount) selectionCount.textContent = String(selection.size);
    };

    const toggleBatchMode = () => {
      batchMode = !batchMode;
      batchBar.classList.toggle('hidden', !batchMode);
      teamList.querySelectorAll('.team-admin-card').forEach((card) => {
        card.classList.toggle('is-batch', batchMode);
      });
      teamList.querySelectorAll('.team-select-check').forEach((control) => {
        control.classList.toggle('hidden', !batchMode);
      });
      if (!batchMode) {
        selection.clear();
        teamList.querySelectorAll('[data-team-select]').forEach((cb) => { cb.checked = false; });
        updateCount();
      } else {
        updateCount();
      }
    };

    batchToggle.addEventListener('click', toggleBatchMode);

    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        selection.clear();
        teamList.querySelectorAll('[data-team-select]').forEach((cb) => { cb.checked = false; });
        updateCount();
      });
    }

    teamList.querySelectorAll('[data-team-select]').forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        const id = Number(checkbox.dataset.teamSelect);
        if (checkbox.checked) {
          selection.add(id);
        } else {
          selection.delete(id);
        }
        updateCount();
      });
    });

    batchBar.querySelectorAll('[data-team-bulk]').forEach(button => {
      button.addEventListener('click', async () => {
        if (selection.size < 1) {
          Admin.showToast('Pilih minimal satu anggota.');
          return;
        }

        const action = button.dataset.teamBulk;
        const isDelete = action === 'delete';
        const isAlumni = action === 'alumni';
        const ok = await Admin.showConfirm({
          title: isDelete ? 'Delete anggota terpilih?' : 'Jadikan alumni?',
          message: `${selection.size} anggota akan diproses.`,
          confirmText: isDelete ? 'Delete' : isAlumni ? 'Jadikan Alumni' : 'Proses',
          danger: isDelete,
        });
        if (!ok) return;
        const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';

        try {
          const res = await fetch(route('admin.teamMembersBulk'), {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify({ action, ids: Array.from(selection), _csrf_token: token }),
          });
          if (!res.ok) throw new Error('Batch operation gagal.');
        } catch (e) {
          Admin.showToast('Batch operation gagal.');
          return;
        }

        Admin.showToast(isDelete ? 'Anggota terpilih dihapus.' : 'Anggota terpilih dijadikan alumni.');
        setTimeout(() => location.reload(), 800);
      });
    });
  }

  // Bind team home toggle for SSR pages
  function bindTeamHomeToggle() {
    document.querySelectorAll('[data-team-home]').forEach(button => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.teamHome);
        const card = button.closest('.team-admin-card');
        const adding = !(card?.classList.contains('is-home'));
        const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';

        try {
          const res = await fetch(route('admin.teamMemberHome', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify({ show_on_home: adding, _csrf_token: token }),
          });

          if (res.ok) {
            Admin.showToast(adding ? 'Ditambahkan ke BPI Beranda.' : 'Dihapus dari BPI Beranda.');
            card?.classList.toggle('is-home', adding);
            card?.querySelectorAll('[data-team-home]').forEach((teamHomeButton) => {
              teamHomeButton.title = adding ? 'Hapus BPI dari Beranda' : 'Tambah Anggota ke Beranda';
              teamHomeButton.textContent = adding ? 'Hapus BPI' : 'BPI Beranda';
            });
          } else {
            Admin.showToast('Gagal memperbarui BPI Beranda.');
          }
        } catch (e) {
          Admin.showToast('Gagal memperbarui BPI Beranda.');
        }
      });
    });
  }

  function bindTeamAlumniButtons() {
    document.querySelectorAll('[data-team-alumni]').forEach(button => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.teamAlumni);
        if (!id) return;
        const ok = await Admin.showConfirm({
          title: 'Jadikan alumni?',
          message: 'Anggota akan dipindahkan ke komisariat Alumni dan tidak masuk daftar anggota aktif.',
          confirmText: 'Jadikan Alumni',
        });
        if (!ok) return;
        const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
        try {
          const res = await fetch(route('admin.teamMemberAlumni', { id }), {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify({ _csrf_token: token }),
          });
          if (!res.ok) throw new Error('Gagal menjadikan anggota alumni.');
          Admin.showToast('Anggota dijadikan alumni.');
          setTimeout(() => location.reload(), 700);
        } catch (e) {
          Admin.showToast('Gagal menjadikan anggota alumni.');
        }
      });
    });
  }
})();
