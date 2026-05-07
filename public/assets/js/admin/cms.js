(function () {
  'use strict';

  const { news, teamMembers, site, programs, publicEvents } = window.GenBIData;
  const Admin = window.GenBIAdmin;
  const API = window.GenBIAPI;
  const Core = window.GenBIAPICore;
  const { adminUrl } = window.GenBIApp;
  const page = document.body.dataset.cmsPage || 'news';
  const mode = document.body.dataset.cmsMode || 'list';
  const csrfMeta = document.querySelector('meta[name="csrf-token"]')?.content || '';
  if (csrfMeta && API && !API.getCsrfToken) {
    API.getCsrfToken = () => csrfMeta;
  }

  function route(name, params = {}) {
    return Core.routeUrl(name, params, window.location);
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

  const events = [
    { id: 1, title: 'GenBI PEKA', start: '2025-01-23', end: '2025-01-23', status: 'Past Event', excerpt: 'Kegiatan pengabdian dan edukasi publik.' },
    { id: 2, title: 'GenBI Ceria', start: '2024-12-21', end: '2024-12-21', status: 'Past Event', excerpt: 'Aktivitas sosial dan literasi untuk komunitas.' },
    { id: 3, title: 'GenBI for UMKM', start: '2024-12-20', end: '2024-12-20', status: 'Past Event', excerpt: 'Pendampingan literasi digital untuk UMKM.' },
    { id: 4, title: 'PTBI 2024', start: '2024-11-29', end: '2024-11-29', status: 'Past Event', excerpt: 'Partisipasi GenBI pada agenda tahunan Bank Indonesia.' },
    { id: 5, title: 'Pelatihan Pencatatan Keuangan dan Literasi SIAPIK', start: '2024-12-02', end: '2024-12-03', status: 'Past Event', excerpt: 'Pelatihan pencatatan keuangan sederhana.' },
    { id: 6, title: 'SERTIFIKASI GENBI PROVINSI JAMBI', start: '2024-11-02', end: '2024-11-03', status: 'Past Event', excerpt: 'Sertifikasi kompetensi anggota GenBI.' }
  ];

  const sliders = [
    { id: 1, photo: 'https://genbijambi.com/public/uploads/slider-1.png', heading: 'WE ARE GENBI PROVINSI JAMBI', button1: 'Read More', url1: 'https://wa.me/6289627896750', button2: 'Contact Us', url2: 'https://wa.me/6289627896750', position: 'Left' },
    { id: 2, photo: 'https://genbijambi.com/public/uploads/slider-4.png', heading: 'WE ARE GENBI PROVINSI JAMBI', button1: 'Read More', url1: 'https://wa.me/6289627896750', button2: 'Contact Us', url2: 'https://wa.me/6289627896750', position: 'Right' }
  ];

  const memberPhotos = [
    'https://official.genbijambi.com/storage/team-members/Ilham-Jaya-Kusuma.png',
    'https://official.genbijambi.com/storage/team-members/Ananda-Marisa-Pertiwi.png',
    'https://official.genbijambi.com/storage/team-members/Depi-Susanti.png'
  ];

  const routes = {
    language: () => { Admin.renderAdminShell('language'); renderLanguage(); },
    category: () => { Admin.renderAdminShell('category'); renderCategoryList(); },
    comment: () => { Admin.renderAdminShell('comment'); renderCommentSetup(); },
    news: () => { Admin.renderAdminShell('news-list'); mode === 'editor' ? renderNewsEditor(false) : renderNewsList(); },
    'news-edit': () => { Admin.renderAdminShell('news-list'); renderNewsEditor(true); },
    prestasi: () => { Admin.renderAdminShell('prestasi'); mode === 'editor' ? renderPrestasiEditor(false) : renderPrestasiList(); },
    'prestasi-edit': () => { Admin.renderAdminShell('prestasi'); renderPrestasiEditor(true); },
    'prestasi-token': () => { Admin.renderAdminShell('prestasi'); renderPrestasiTokenList(); },
    event: () => { Admin.renderAdminShell('event'); mode === 'editor' ? renderEventEditor() : renderEventList(); },
    slider: () => { Admin.renderAdminShell('slider'); mode === 'editor' ? renderSliderEditor() : renderSliderList(); },
    team: () => { Admin.renderAdminShell('team'); mode === 'editor' ? renderTeamEditor() : renderTeamList(); },
    feature: () => { Admin.renderAdminShell('feature'); mode === 'editor' ? renderFeatureEditor() : renderFeatureList(); },
    why: () => { Admin.renderAdminShell('why'); mode === 'editor' ? renderWhyChooseEditor() : renderWhyChooseList(); },
    faq: () => { Admin.renderAdminShell('faq'); mode === 'editor' ? renderFaqEditor() : renderFaqList(); },
    social: () => { Admin.renderAdminShell('social'); renderSocialMedia(); },
    photo: () => { Admin.renderAdminShell('gallery'); mode === 'editor' ? renderPhotoEditor() : renderPhotoList(); },
  };

  const teamSelection = new Set();

  (routes[page] || routes.news)();

  function renderShell(title, subtitle, actions = '') {
    const root = document.querySelector('#admin-content');
    if (!root) return null;
    root.innerHTML = `
      <section class="mx-auto max-w-7xl">
        <header class="cms-header slide-in">
          <div>
            <p class="eyebrow">Admin CMS</p>
            <h1 class="section-title mt-3">${title}</h1>
            ${subtitle ? `<p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">${subtitle}</p>` : ''}
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
    root.querySelectorAll('select.js-admin-custom-select').forEach((select) => {
      if (select.dataset.customSelectReady === '1') return;
      select.dataset.customSelectReady = '1';

      const wrapper = document.createElement('div');
      wrapper.className = 'admin-custom-select';
      select.parentNode.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'admin-select-button';
      button.setAttribute('aria-expanded', 'false');
      button.innerHTML = `<span>${escape(select.options[select.selectedIndex]?.text || 'Pilih')}</span><span aria-hidden="true">⌄</span>`;

      const menu = document.createElement('div');
      menu.className = 'admin-select-menu hidden';
      Array.from(select.options).forEach((option) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.dataset.value = option.value;
        item.textContent = option.text;
        item.className = option.selected ? 'is-active' : '';
        item.addEventListener('click', () => {
          select.value = option.value;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          button.querySelector('span').textContent = option.text;
          menu.querySelectorAll('button').forEach((entry) => entry.classList.toggle('is-active', entry === item));
          close();
        });
        menu.appendChild(item);
      });

      const close = () => {
        button.setAttribute('aria-expanded', 'false');
        menu.classList.add('hidden');
        menu.style.removeProperty('--select-button-width');
      };
      const open = () => {
        document.querySelectorAll('.admin-select-button[aria-expanded="true"]').forEach((openButton) => openButton.click());
        positionAdminSelectMenu(button, menu);
        button.setAttribute('aria-expanded', 'true');
        menu.classList.remove('hidden');
      };

      button.addEventListener('click', () => button.getAttribute('aria-expanded') === 'true' ? close() : open());
      document.addEventListener('click', (event) => { if (!wrapper.contains(event.target)) close(); }, { signal: window.AdminSelectAbortSignal });
      document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); }, { signal: window.AdminSelectAbortSignal });

      wrapper.appendChild(button);
      document.body.appendChild(menu);
    });
  }

  function positionAdminSelectMenu(button, menu) {
    const rect = button.getBoundingClientRect();
    const gap = 6;
    const maxMenuHeight = Math.min(256, window.innerHeight - 24);
    const spaceBelow = window.innerHeight - rect.bottom - gap;
    const spaceAbove = rect.top - gap;
    const estimatedHeight = Math.min(maxMenuHeight, Math.max(48, menu.scrollHeight || 180));
    const openUp = spaceBelow < estimatedHeight && spaceAbove > spaceBelow;

    menu.style.setProperty('--select-button-width', `${rect.width}px`);
    menu.style.left = `${rect.left + window.scrollX}px`;
    menu.style.width = `${rect.width}px`;
    menu.style.maxHeight = `${Math.max(120, Math.min(maxMenuHeight, openUp ? spaceAbove : spaceBelow))}px`;
    menu.style.top = `${(openUp ? rect.top - Math.min(estimatedHeight, spaceAbove) - gap : rect.bottom + gap) + window.scrollY}px`;
  }

  function renderLanguage() {
    const body = renderShell('Edit Language Data', 'Ubah label bahasa dengan layout block. Label teknis tetap terbaca, teks terjemahan bisa diedit langsung.', '<button id="save-language" class="btn btn-primary">Update Language</button>');
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">NB: bagian ini untuk mengubah teks kecil yang tidak diatur dari section lain.</div>
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

  function renderCategoryList() {
    const body = renderShell('View Categories', 'Kategori berita dirapikan menjadi list yang mudah dipindai.', '<button class="btn btn-primary">Add New</button>');
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Category')}
        <div class="admin-responsive-table mt-5">
          <table class="cms-table">
            <thead><tr><th>SL</th><th>Category Name</th><th>Category Banner</th><th>Action</th></tr></thead>
            <tbody>${categories.map((item, index) => `
              <tr>
                <td>${index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td>${item.banner ? `<img src="${item.banner}" class="table-banner" alt="${escape(item.name)}" />` : '<span class="text-neutral-500">Belum ada banner</span>'}</td>
                <td>${rowActions('Kategori')}</td>
              </tr>
            `).join('')}</tbody>
          </table>
        </div>
      </section>
    `;
    enhanceAdminSelects(body);
    bindDeleteButtons('Kategori akan dihapus dari daftar simulasi.');
  }

  async function renderNewsList() {
    // Check if SSR markup exists - if so, only bind delete behavior
    if (document.querySelector('#admin-news-list[data-ssr="true"]')) {
      bindNewsDeleteButtons();
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
            <h2>Photo and Banner</h2>
            ${item.image ? `<img src="${item.image}" class="config-preview" alt="Featured photo" />` : '<div class="config-empty">Belum ada foto utama</div>'}
            <input id="news-photo-file" class="hidden" type="file" accept="image/*" />
            <button type="button" id="news-photo-upload-btn" class="btn btn-secondary w-full mt-2">Upload Featured Photo</button>
            <input class="config-input mt-2" id="news-photo-url" value="${escape(item.image)}" placeholder="URL foto utama" />
            <input id="news-banner-file" class="hidden" type="file" accept="image/*" />
            <button type="button" id="news-banner-upload-btn" class="btn btn-secondary w-full mt-2">Upload Banner</button>
            <input class="config-input mt-2" id="news-banner-url" value="${escape(item.banner || '')}" placeholder="URL banner" />
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
        banner: document.querySelector('#news-banner-url')?.value?.trim() || '',
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
        Admin.showToast('Gagal menyimpan berita. Periksa koneksi.');
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
        banner: document.querySelector('#news-banner-url')?.value?.trim() || '',
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
        Admin.showToast('Gagal menyimpan berita. Periksa koneksi.');
      }
    });
  }

  function initMediumEditor(item, isEdit) {
    const holder = document.querySelector('#news-editor');
    const fallback = document.querySelector('#editor-fallback');
    if (!holder) return null;

    if (!window.EditorJS) {
      holder.classList.add('hidden');
      fallback?.classList.remove('hidden');
      Admin.showToast('Editor.js CDN belum termuat. Fallback editor aktif.');
      return null;
    }

    const initialBlocks = buildNewsBlocks(item, isEdit);
    const tools = {};
    if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [1, 2, 3], defaultLevel: 2 } };
    const ListTool = window.EditorjsList || window.List;
    if (ListTool) tools.list = { class: ListTool, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
    if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Tulis kutipan...', captionPlaceholder: 'Sumber kutipan' } };
    if (window.ImageTool) {
      tools.image = {
        class: window.ImageTool,
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

    return new window.EditorJS({
      holder: 'news-editor',
      autofocus: true,
      minHeight: 520,
      placeholder: 'Mulai tulis berita. Tekan Tab atau klik plus untuk memilih blok.',
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
        return `<${tag}>${(block.data.items || []).map((item) => `<li>${item}</li>`).join('')}</${tag}>`;
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

  function bindNewsImageUploads() {
    bindNewsImageUpload('#news-photo-upload-btn', '#news-photo-file', '#news-photo-url');
    bindNewsImageUpload('#news-banner-upload-btn', '#news-banner-file', '#news-banner-url');
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
        const preview = document.querySelector('.config-preview');
        if (preview && buttonSelector === '#news-photo-upload-btn') preview.src = url;
        Admin.showToast('Gambar berhasil diupload.');
      } catch (err) {
        Admin.showToast('Gagal upload gambar.');
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
    if (!res.ok || !url) throw new Error(json.error || 'Gagal upload gambar.');
    return url;
  }

  function getAdminCsrfToken() {
    return (API && API.getCsrfToken && API.getCsrfToken()) || document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function insertEditorBlock(editor, type, data) {
    if (!editor?.blocks?.insert) return Admin.showToast('Editor belum siap.');
    editor.blocks.insert(type, data);
  }

  async function renderCommentSetup() {
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
          <p class="text-sm text-neutral-500">${escape(item.email)}</p>
          <p class="comment-moderation-text">${escape(item.text)}</p>
        </div>
        <aside class="comment-moderation-side">
          <span class="comment-status-admin ${item.status.toLowerCase()}">${item.status}</span>
          <button class="cms-action edit" data-action="approve" data-id="${item.id}">Approve</button>
          <button class="cms-action hold" data-action="reject" data-id="${item.id}">Reject</button>
          <button class="cms-action delete" data-action="delete" data-id="${item.id}">Delete</button>
        </aside>
      </article>
    `).join('') : '<div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">Tidak ada komentar yang cocok dengan filter.</div>';

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

  function renderEventList() {
    const body = renderShell('View Event', 'Agenda komunitas tampil sebagai tabel bersih dengan modal konfirmasi.', `<a href="${adminUrl('event-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Event')}
        <div class="admin-responsive-table mt-5">
          <table class="cms-table">
            <thead><tr><th>SL</th><th>Title</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>${events.map((item, index) => `
              <tr><td>${index + 1}</td><td><strong>${item.title}</strong><p class="mt-1 text-xs text-neutral-500">${item.excerpt}</p></td><td>${item.start}</td><td>${item.end}</td><td><span class="cms-pill muted">${item.status}</span></td><td>${rowActions('Event')}</td></tr>
            `).join('')}</tbody>
          </table>
        </div>
      </section>
    `;
    bindDeleteButtons('Event akan dihapus dari daftar simulasi.');
  }

  function renderEventEditor() {
    const body = renderShell('Add Event', 'Form event dirapikan menjadi blok editor dan konfigurasi terstruktur.', `<a href="${adminUrl('event')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace" id="event-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Judul event..."></div>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Ringkasan pendek event..."></div>
          <article class="news-body-block" contenteditable="true" data-placeholder="Deskripsi event..."></article>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Lokasi event..."></div>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Embed map atau URL lokasi..."></div>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Event Date</h2>${control('Start Date', '<input class="config-input" type="date" value="2026-05-05" />')}${control('End Date', '<input class="config-input" type="date" value="2026-05-05" />')}</section>
          <section class="config-card"><h2>Photo and Banner</h2>${control('Featured Photo', '<input class="config-input" type="file" />')}${control('Banner', '<input class="config-input" type="file" />')}</section>
          <section class="config-card"><h2>SEO Information</h2>${control('Meta Title', '<input class="config-input" />')}${control('Meta Keywords', '<textarea class="config-input" rows="4"></textarea>')}${control('Meta Description', '<textarea class="config-input" rows="5"></textarea>')}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Event</button>
        </aside>
      </form>
    `;
    document.querySelector('#event-form').addEventListener('submit', async (event) => { event.preventDefault(); if (await Admin.showConfirm({ title: 'Submit event?', message: 'Event akan ditambahkan pada mode simulasi.' })) Admin.showToast('Event ditambahkan pada mode simulasi.'); });
  }

  function renderSliderList() {
    const body = renderShell('View Sliders', 'Slider 1 dan slider 4 menjadi sumber background hero landing page.', `<a href="${adminUrl('slider-add')}" class="btn btn-primary">Add Slider</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Slider')}
        <div class="slider-card-grid mt-5">
          ${sliders.map((item) => `
            <article class="slider-card">
              <img src="${item.photo}" alt="${escape(item.heading)}" />
              <div><h2>${item.heading}</h2><p>${item.button1} · ${item.button2}</p><span>${item.position}</span></div>
              <div class="flex gap-2">${rowActions('Slider')}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    bindDeleteButtons('Slider akan dihapus dari daftar simulasi.');
  }

  function renderSliderEditor() {
    const body = renderShell('Add Slider', 'Form slider dibuat block based agar teks headline dan CTA mudah disusun.', `<a href="${adminUrl('slider')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="slider-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Heading slider..."></div>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Konten pendek slider..."></div>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="news-excerpt-block" contenteditable="true" data-placeholder="Button 1 Text..."></div>
            <div class="news-excerpt-block" contenteditable="true" data-placeholder="Button 1 URL..."></div>
            <div class="news-excerpt-block" contenteditable="true" data-placeholder="Button 2 Text..."></div>
            <div class="news-excerpt-block" contenteditable="true" data-placeholder="Button 2 URL..."></div>
          </div>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Slider Config</h2>${control('Photo', '<input class="config-input" type="file" />')}${control('Position', '<select class="config-input"><option>Left</option><option>Right</option></select>')}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Slider</button>
        </aside>
      </form>
    `;
    document.querySelector('#slider-form').addEventListener('submit', async (event) => { event.preventDefault(); if (await Admin.showConfirm({ title: 'Submit slider?', message: 'Slider akan ditambahkan pada mode simulasi.' })) Admin.showToast('Slider ditambahkan pada mode simulasi.'); });
  }

  async function renderTeamList() {
    const body = renderShell('View Team Members', 'Direktori anggota memakai mode card. Bisa berpindah antara grid dan list tanpa tabel sempit.', `<a href="${adminUrl('team-member-add')}" class="btn btn-primary">Add Team Member</a>`);
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
          <section class="config-card"><h2>Visibility</h2>${control('Tampilkan di BPI Beranda', selectControl({ id: 'team-show-home', value: item.show_on_home ? '1' : '0', options: [{ value: '1', label: 'Show' }, { value: '0', label: 'Hide' }] }))}</section>
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
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': API.getCsrfToken?.() || '' },
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
    const body = renderShell('View Features', 'Program utama tampil sebagai daftar editorial. Isi dapat dipindai tanpa visual yang ramai.', `<a href="${adminUrl('feature-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Feature')}
        <div class="simple-card-grid mt-5">
          ${programs.map((item) => `
            <article class="simple-admin-card">
              <div class="meta">${escape(item.focus)}</div>
              <h2>${escape(item.title)} · ${escape(item.name)}</h2>
              <p>${escape(item.description)}</p>
              <div class="mt-4 flex gap-2">${rowActions('Feature')}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    bindDeleteButtons('Program akan dihapus dari daftar simulasi.');
  }

  function renderFeatureEditor() {
    const body = renderShell('Add Feature', 'Tambah program dengan field besar dan tombol input custom.', `<a href="${adminUrl('feature')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="feature-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Nama program..."></div>
          <div class="news-excerpt-block" contenteditable="true" data-placeholder="Fokus program..."></div>
          <article class="news-body-block smaller" contenteditable="true" data-placeholder="Manfaat program untuk anggota dan publik..."></article>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Program Config</h2>${control('Icon', '<input class="config-input" placeholder="users, bank, phone..." />')}${control('Show on Home', '<select class="config-input"><option>Show</option><option>Hide</option></select>')}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Feature</button>
        </aside>
      </form>
    `;
    bindSimpleSubmit('#feature-form', 'Submit program?', 'Program ditambahkan pada mode simulasi.');
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
          <section class="config-card"><h2>Visibility</h2>${control('Show on Home', '<select class="config-input"><option>Yes</option><option>No</option></select>')}</section>
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

  function renderPhotoList() {
    const photos = news.slice(0, 6).map((item) => ({ title: item.title, image: item.image }));
    const body = renderShell('View Photos', 'Galeri foto tampil sebagai kartu agar preview lebih jelas.', `<a href="${adminUrl('photo-add')}" class="btn btn-primary">Add New</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        ${renderSearchToolbar('Photo')}
        <div class="simple-card-grid mt-5">
          ${photos.map((item) => `
            <article class="simple-admin-card">
              <img src="${item.image}" alt="${escape(item.title)}" />
              <h2 class="mt-4">${escape(item.title)}</h2>
              <div class="mt-4 flex gap-2">${rowActions('Photo')}</div>
            </article>
          `).join('')}
        </div>
      </section>
    `;
    bindDeleteButtons('Foto akan dihapus dari galeri simulasi.');
  }

  function renderPhotoEditor() {
    const body = renderShell('Add Photo', 'Upload foto memakai tombol custom yang lebih rapi dari input default.', `<a href="${adminUrl('photo')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="photo-form">
        <main class="block-writing-surface">
          <div class="admin-upload-zone">
            <p class="font-bold text-blue-950">Upload Photo</p>
            <input class="admin-file-input" type="file" accept="image/*" />
            <p class="text-sm text-neutral-500">Only jpg, jpeg, gif, and png are allowed.</p>
          </div>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card"><h2>Photo Info</h2>${control('Caption', '<input class="config-input" placeholder="Caption foto..." />')}${control('Visibility', '<select class="config-input"><option>Show</option><option>Hide</option></select>')}</section>
          <button type="submit" class="btn btn-primary w-full">Submit Photo</button>
        </aside>
      </form>
    `;
    bindSimpleSubmit('#photo-form', 'Submit foto?', 'Foto ditambahkan pada mode simulasi.');
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
    return `
      <article class="team-admin-card ${homeClass}" data-team-id="${item.id}">
        ${batchMode ? `<label class="team-select-check"><input type="checkbox" data-team-select="${item.id}" ${checked} /> Select</label>` : ''}
        <button type="button" class="team-home-toggle" data-team-home="${item.id}" title="${item.show_on_home ? 'Hapus BPI dari Beranda' : 'Tambah BPI ke Beranda'}">${item.show_on_home ? '−' : '+'}</button>
        <div class="team-admin-photo"><img src="${photo}" alt="${escape(item.name)}" onerror="this.remove(); this.parentElement.textContent='${Admin.initials(item.name)}';" /></div>
        <div class="team-admin-content">
          <h2>${escape(item.name)}</h2>
          <p>${escape(item.role)}</p>
          <div class="team-tags"><span>${escape(item.commission)}</span><span>${escape(item.division)}</span><span>${escape(item.status)}</span></div>
        </div>
        <div class="team-card-actions"><a href="${adminUrl('team-member-edit')}?id=${item.id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-team-delete="${item.id}">Delete</button></div>
      </article>
    `;
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
        const adding = button.textContent.trim() === '+';
        const res = await fetch(route('admin.teamMemberHome', { id }), {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': API.getCsrfToken?.() || '' },
          credentials: 'same-origin',
          body: JSON.stringify({ show_on_home: adding }),
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
      <button type="button" class="cms-action edit" data-team-bulk="home_add">Tambah BPI ke Beranda</button>
      <button type="button" class="cms-action" data-team-bulk="home_remove">Hapus BPI dari Beranda</button>
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
      const ok = await Admin.showConfirm({
        title: 'Jalankan batch operation?',
        message: `${teamSelection.size} anggota akan diproses.`,
        confirmText: 'Proses',
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
      document.querySelector('#team-photo-preview').innerHTML = `<img src="${json.data.url}" alt="Preview" />`;
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
    return `<section class="mini-block"><span>${label}</span><select class="team-commission-select">${options.map((option) => `<option ${option === selected ? 'selected' : ''}>${option}</option>`).join('')}</select></section>`;
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

  // ─── Prestasi CMS ───────────────────────────────────────────────────────────

  const prestasiCategories = ['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', 'Harapan 2', 'Finalis', 'Peserta Terbaik'];

  async function renderPrestasiList() {
    const body = renderShell(
      'View Prestasi',
      'Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.',
      `<a href="${adminUrl('prestasi-add')}" class="btn btn-primary">Add Prestasi</a>`
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

  function bindPrestasiDeleteButtons() {
    document.querySelectorAll('[data-delete][data-prestasi-id]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.prestasiId;
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

  async function renderPrestasiEditor(isEdit) {
    const id = Number(new URLSearchParams(location.search).get('id')) || 0;
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
            ${control('Peringkat', `<input class="config-input" id="prestasi-category" value="${escape(item.category)}" list="prestasi-rank-list" placeholder="Contoh: Juara 1" />
              <datalist id="prestasi-rank-list">${prestasiCategories.map(c => `<option value="${escape(c)}"></option>`).join('')}</datalist>`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Deskripsi</h2>
            ${control('Deskripsi Singkat', `<textarea class="config-input" id="prestasi-desc-field" rows="5" placeholder="Tulis deskripsi singkat prestasi...">${escape(item.description)}</textarea>`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Foto Prestasi</h2>
            ${item.image ? `<img src="${item.image}" class="config-preview rounded" alt="Foto prestasi" />` : '<div class="config-empty">Belum ada foto</div>'}
            <input id="prestasi-image-file" class="hidden" type="file" accept="image/*" />
            <button type="button" id="prestasi-upload-btn" class="btn btn-secondary w-full mt-2">Upload Foto</button>
            <input class="config-input mt-2" id="prestasi-image-url" value="${escape(item.image)}" placeholder="URL gambar (opsional)" />
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

    // Image upload handler
    enhanceAdminSelects(body);

    document.querySelector('#prestasi-upload-btn')?.addEventListener('click', () => {
      document.querySelector('#prestasi-image-file')?.click();
    });
    document.querySelector('#prestasi-image-file')?.addEventListener('change', async (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const formData = new FormData();
      formData.append('image', file);
      try {
        const res = await fetch(route('admin.prestasiUpload'), {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: formData
        });
        if (res.ok) {
          const json = await res.json();
          const url = json.data?.url || '';
          document.querySelector('#prestasi-image-url').value = url;
          const preview = document.querySelector('.config-preview');
          if (preview) { preview.src = url; }
          else {
            const container = document.querySelector('#prestasi-upload-btn')?.parentElement;
            const empty = container?.querySelector('.config-empty');
            if (empty) { empty.outerHTML = `<img src="${url}" class="config-preview rounded" alt="Foto prestasi" />`; }
          }
          Admin.showToast('Foto berhasil diupload.');
        } else {
          Admin.showToast('Gagal upload foto.');
        }
      } catch (err) {
        Admin.showToast('Gagal upload foto.');
      }
    });

    // Form submission
    document.querySelector('#prestasi-editor-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();

      const ok = await Admin.showConfirm({
        title: isEdit ? 'Update prestasi?' : 'Submit prestasi?',
        message: isEdit ? 'Data prestasi akan diperbarui di database.' : 'Prestasi baru akan disimpan ke database.',
        confirmText: isEdit ? 'Update' : 'Submit'
      });
      if (!ok) return;

      const payload = {
        name: document.querySelector('#prestasi-member-search')?.value?.trim() || '',
        title: buildPrestasiTitle(),
        category: document.querySelector('#prestasi-category')?.value || '',
        year: document.querySelector('#prestasi-year')?.value || '',
        description: document.querySelector('#prestasi-desc-field')?.value?.trim() || '',
        content: document.querySelector('#prestasi-desc-field')?.value?.trim() || '',
        image: document.querySelector('#prestasi-image-url')?.value?.trim() || '',
        institution: document.querySelector('#prestasi-institution')?.value?.trim() || '',
        status: document.querySelector('#prestasi-status')?.value || 'draft',
        meta_title: document.querySelector('#prestasi-meta-title')?.value?.trim() || '',
        meta_keyword: document.querySelector('#prestasi-meta-keyword')?.value?.trim() || '',
        meta_description: document.querySelector('#prestasi-meta-desc')?.value?.trim() || '',
      };

      const csrfToken = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const url = isEdit ? route('admin.prestasiUpdate', { id }) : route('admin.prestasiStore');

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
          Admin.showToast(result.error || 'Gagal menyimpan prestasi.' + (details ? ' ' + details : ''));
        }
      } catch (e) {
        Admin.showToast('Gagal menyimpan prestasi. Periksa koneksi.');
      }
    });
  }

  // ─── Prestasi Token Management ──────────────────────────────────────────────

  async function renderPrestasiTokenList() {
    const body = renderShell(
      'Prestasi Token',
      'Generate dan kelola token form prestasi sekali pakai.',
      `<button id="generate-token-btn" class="btn btn-primary">Generate Token</button>`
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
      <div id="generated-token-display" class="mt-6 hidden">
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

    // Bind revoke buttons
    bindTokenRevokeButtons();
  }

  function renderTokenRows(items) {
    if (items.length === 0) {
      return '<tr><td colspan="7" class="text-center text-neutral-500 py-8">Belum ada token.</td></tr>';
    }
    return items.map((item, index) => {
      const status = item.status || 'active';
      const statusClass = status === 'active' ? 'cms-pill-green' : status === 'used' ? 'cms-pill-yellow' : 'cms-pill-red';
      return `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${escape(item.label || '')}</strong></td>
          <td><span class="cms-pill ${statusClass}">${status}</span></td>
          <td class="text-xs">${item.created_at || '-'}</td>
          <td class="text-xs">${item.expires_at || 'Tidak ada'}</td>
          <td class="text-xs">${item.used_at || '-'}</td>
          <td>
            ${status === 'active' ? `<button class="cms-action delete" data-revoke data-token-id="${item.id || item.token_id}">Revoke</button>` : '<span class="text-neutral-400 text-xs">-</span>'}
          </td>
        </tr>
      `;
    }).join('');
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
            renderPrestasiTokenList(); // Refresh list
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
      html: true
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
      const result = await res.json();
      if (res.ok && result.data?.token) {
        const baseUrl = window.location.origin;
        const submitUrl = `${baseUrl}${route('public.prestasiSubmit', { token: result.data.token })}`;
        const display = document.querySelector('#generated-token-display');
        const urlInput = document.querySelector('#generated-token-url');
        if (display && urlInput) {
          urlInput.value = submitUrl;
          display.classList.remove('hidden');
        }
        document.querySelector('#copy-token-url')?.addEventListener('click', async () => {
          try {
            await navigator.clipboard.writeText(submitUrl);
            Admin.showToast('Link token disalin ke clipboard.');
          } catch (e) {
            Admin.showToast('Gagal menyalin. Salin manual dari input.');
          }
        });
        Admin.showToast('Token berhasil dibuat. Salin link sekarang!');
        renderPrestasiTokenList(); // Refresh table
      } else {
        Admin.showToast(result.error || 'Gagal membuat token.');
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
                  headers: { 'X-CSRF-TOKEN': token },
                  credentials: 'same-origin',
                  body: formData
                });
                if (res.ok) {
                  const json = await res.json();
                  return { success: 1, file: { url: json.data.url } };
                }
              } catch (e) { /* fallback */ }
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
})();
