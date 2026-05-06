(function () {
  'use strict';

  const { news, teamMembers, site, programs, publicEvents } = window.GenBIData;
  const Admin = window.GenBIAdmin;
  const API = window.GenBIAPI;
  const Core = window.GenBIAPICore;
  const { adminUrl } = window.GenBIApp;
  const page = document.body.dataset.cmsPage || 'news';
  const mode = document.body.dataset.cmsMode || 'list';

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
    bindDeleteButtons('Kategori akan dihapus dari daftar simulasi.');
  }

  async function renderNewsList() {
    const body = renderShell('View News', 'Daftar berita dari database. Aksi hapus memakai custom confirmation modal.', `<a href="${adminUrl('news-add')}" class="btn btn-primary">Add News</a>`);
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data berita...</div>';

    let items = news; // fallback to static data
    try {
      const res = await fetch('/admin/news/list', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
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
            <tbody>${items.map((item, index) => `
              <tr>
                <td>${index + 1}</td>
                <td><strong>${escape(item.title || item.news_title || '')}</strong><p class="mt-1 text-xs text-neutral-500">${item.date || item.published_at || ''}</p></td>
                <td><p class="news-caption-cell">${escape(item.excerpt || item.news_content_short || '')}</p></td>
                <td>${item.photo ? `<img src="${item.photo.startsWith('http') || item.photo.startsWith('/') ? item.photo : 'https://genbijambi.com/public/uploads/' + item.photo}" class="table-thumb" alt="${escape(item.title || '')}" />` : '<span class="text-neutral-400">-</span>'}</td>
                <td><span class="cms-pill">${escape(item.category || item.category_name || '')}</span></td>
                <td><span class="cms-pill ${item.status === 'published' ? 'cms-pill-green' : item.status === 'draft' ? 'cms-pill-yellow' : ''}">${item.status || 'published'}</span></td>
                <td>
                  <div class="flex gap-2"><a href="${adminUrl('news-edit')}?id=${item.id || item.news_id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-delete data-news-id="${item.id || item.news_id}">Delete</button></div>
                </td>
              </tr>
            `).join('')}</tbody>
          </table>
        </div>
      </section>
    `;
    bindNewsDeleteButtons();
  }

  function bindNewsDeleteButtons() {
    document.querySelectorAll('[data-delete][data-news-id]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = button.dataset.newsId;
        const ok = await Admin.showConfirm({ title: 'Hapus berita?', message: 'Berita akan dihapus (soft delete). Data masih bisa dipulihkan.', confirmText: 'Hapus', danger: true });
        if (!ok) return;
        try {
          const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
          const res = await fetch(`/admin/news/${id}/delete`, {
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
        const res = await fetch(`/admin/news/${id}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
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
      const catRes = await fetch('/admin/news/categories', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
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
            <article contenteditable="true">${(item.body || ['']).map((p) => `<p>${escape(p)}</p>`).join('')}</article>
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
          </section>
          <section class="config-card medium-config-card">
            <h2>Publishing</h2>
            ${control('News Publish Date', `<input class="config-input" type="date" value="${dateInput(item.date)}" />`)}
            ${control('Category', `<select class="config-input" id="news-category-select">${backendCategories.map((cat) => `<option value="${cat.id}" ${cat.id === item.category_id || cat.name === item.category ? 'selected' : ''}>${cat.name}</option>`).join('')}</select>`)}
            ${control('Comment', '<select class="config-input"><option>On</option><option>Off</option></select>')}
          </section>
          <section class="config-card medium-config-card">
            <h2>Photo and Banner</h2>
            ${item.image ? `<img src="${item.image}" class="config-preview" alt="Featured photo" />` : '<div class="config-empty">Belum ada foto utama</div>'}
            ${control('Featured Photo', '<input class="config-input" type="file" />')}
            ${control('Banner', '<input class="config-input" type="file" />')}
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
            ${control('Publish Status', `<select class="config-input" id="news-status"><option value="draft" ${(item.status || 'draft') === 'draft' ? 'selected' : ''}>Draft</option><option value="published" ${item.status === 'published' ? 'selected' : ''}>Published</option><option value="archived" ${item.status === 'archived' ? 'selected' : ''}>Archived</option></select>`)}
          </section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update News' : 'Submit News'}</button>
        </aside>
      </form>
    `;

    const editor = initMediumEditor(item, isEdit);
    bindMediumEditorActions(editor);

    document.querySelector('#news-editor-form').addEventListener('submit', async (event) => {
      event.preventDefault();
      let editorContent = '';
      if (editor?.save) {
        try {
          const outputData = await editor.save();
          // Convert Editor.js blocks to HTML
          editorContent = outputData.blocks.map(block => {
            if (block.type === 'paragraph') return `<p>${block.data.text}</p>`;
            if (block.type === 'header') return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`;
            if (block.type === 'list') {
              const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
              return `<${tag}>${block.data.items.map(i => `<li>${i}</li>`).join('')}</${tag}>`;
            }
            if (block.type === 'quote') return `<blockquote><p>${block.data.text}</p>${block.data.caption ? `<cite>${block.data.caption}</cite>` : ''}</blockquote>`;
            if (block.type === 'image') return `<figure><img src="${block.data.file?.url || block.data.url || ''}" alt="${block.data.caption || ''}" />${block.data.caption ? `<figcaption>${block.data.caption}</figcaption>` : ''}</figure>`;
            return `<p>${block.data.text || ''}</p>`;
          }).join('\n');
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
      };

      const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const url = isEdit ? `/admin/news/${id}/update` : '/admin/news';

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
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
              const token = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
              const formData = new FormData();
              formData.append('image', file);
              try {
                const res = await fetch('/admin/news/upload', {
                  method: 'POST',
                  headers: { 'X-CSRF-TOKEN': token },
                  credentials: 'same-origin',
                  body: formData
                });
                if (res.ok) {
                  const json = await res.json();
                  return { success: 1, file: { url: json.data.url } };
                }
              } catch (e) { /* fallback below */ }
              // Fallback to data URL if upload fails
              return readFileAsDataUrl(file).then((url) => ({ success: 1, file: { url } }));
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
    const blocks = [];
    (item.body || []).filter(Boolean).forEach((paragraph) => {
      blocks.push({ type: 'paragraph', data: { text: paragraph } });
    });
    if (isEdit) {
      blocks.push({ type: 'paragraph', data: { text: `PEWARTA : ${item.author || 'Muhammad David'}` } });
      blocks.push({ type: 'paragraph', data: { text: `EDITOR : ${item.editor || 'Mukhtada Billah Nst'}` } });
    }
    return blocks;
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
      const url = await readFileAsDataUrl(file);
      insertEditorBlock(editor, 'image', { file: { url }, caption: file.name, withBorder: false, withBackground: false, stretched: false });
      imageFile.value = '';
    });
  }

  function insertEditorBlock(editor, type, data) {
    if (!editor?.blocks?.insert) return Admin.showToast('Editor belum siap.');
    editor.blocks.insert(type, data);
  }

  function readFileAsDataUrl(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
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

  function renderTeamList() {
    const body = renderShell('View Team Members', 'Direktori anggota memakai mode card. Bisa berpindah antara grid dan list tanpa tabel sempit.', `<a href="${adminUrl('team-member-add')}" class="btn btn-primary">Add Team Member</a>`);
    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="cms-toolbar">
          <label class="cms-search">${Admin.icon('search')}<input id="team-search" placeholder="Cari nama, jabatan, komisariat, divisi..." /></label>
          <div class="view-toggle" role="group" aria-label="Mode tampilan">
            <button type="button" class="is-active" data-view="grid">${Admin.icon('grid')} Grid</button>
            <button type="button" data-view="list">${Admin.icon('list')} List</button>
          </div>
        </div>
        <div id="team-cards" class="team-card-grid mt-6"></div>
      </section>
    `;
    const state = { view: 'grid', query: '' };
    const render = () => {
      const root = document.querySelector('#team-cards');
      const items = teamMembers.filter((item) => `${item.name} ${item.role} ${item.commission} ${item.division}`.toLowerCase().includes(state.query.toLowerCase()));
      root.className = state.view === 'grid' ? 'team-card-grid mt-6' : 'team-card-list mt-6';
      root.innerHTML = items.map((item, index) => teamCard(item, index)).join('');
      bindDeleteButtons('Anggota akan dihapus dari daftar simulasi.');
    };
    document.querySelectorAll('[data-view]').forEach((button) => button.addEventListener('click', () => {
      state.view = button.dataset.view;
      document.querySelectorAll('[data-view]').forEach((item) => item.classList.toggle('is-active', item === button));
      render();
    }));
    document.querySelector('#team-search').addEventListener('input', (event) => { state.query = event.target.value; render(); });
    render();
  }

  function renderTeamEditor() {
    const id = Number(new URLSearchParams(location.search).get('id')) || 4;
    const isEdit = location.pathname.includes('edit');
    const item = isEdit ? (teamMembers.find((entry) => entry.id === id) || teamMembers[0]) : { name: '', role: '', division: '', commission: '', campus: '', status: '', bio: '' };
    const body = renderShell(isEdit ? 'Edit Team Member' : 'Add Team Member', 'Form tambah dan edit dibuat sama. Field tambahan: Komisariat, Divisi, Jabatan, dan Divisi Lain.', `<a href="${adminUrl('team-member')}" class="btn btn-secondary">View All</a>`);
    body.innerHTML = `
      <form class="editor-workspace compact" id="team-form">
        <main class="block-writing-surface">
          <div class="news-title-block" contenteditable="true" data-placeholder="Nama anggota...">${escape(item.name)}</div>
          <div class="team-form-grid">
            ${miniBlock('Jabatan', item.role)}
            ${miniSelectBlock('Komisariat', ['Universitas Jambi', 'UIN Sultan Thaha', 'Alumni'], normalizeCommission(item))}
            ${miniBlock('Divisi', item.division)}
            ${miniBlock('Divisi Lain', '')}
            ${miniBlock('Kampus', item.campus)}
            ${miniBlock('Status', item.status)}
          </div>
          <article class="news-body-block smaller" contenteditable="true" data-placeholder="Bio singkat anggota...">${escape(item.bio)}</article>
        </main>
        <aside class="editor-config-sidebar">
          <section class="config-card">
            <h2>Photo</h2>
            <div class="member-preview-avatar">${Admin.initials(item.name || 'Member')}</div>
            ${control('Profile Photo', '<input class="config-input" type="file" />')}
          </section>
          <section class="config-card"><h2>Visibility</h2>${control('Show on Public Page', '<select class="config-input"><option>Show</option><option>Hide</option></select>')}${control('Year', '<input class="config-input" value="2026" />')}</section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update Member' : 'Submit Member'}</button>
        </aside>
      </form>
    `;
    document.querySelector('#team-form').addEventListener('submit', async (event) => { event.preventDefault(); if (await Admin.showConfirm({ title: isEdit ? 'Update anggota?' : 'Submit anggota?', message: 'Data anggota akan disimpan pada mode simulasi.' })) Admin.showToast(isEdit ? 'Anggota diperbarui pada mode simulasi.' : 'Anggota ditambahkan pada mode simulasi.'); });
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

  function teamCard(item, index) {
    const photo = memberPhotos[index % memberPhotos.length];
    return `
      <article class="team-admin-card">
        <div class="team-admin-photo"><img src="${photo}" alt="${escape(item.name)}" onerror="this.remove(); this.parentElement.textContent='${Admin.initials(item.name)}';" /></div>
        <div class="team-admin-content">
          <h2>${escape(item.name)}</h2>
          <p>${escape(item.role)}</p>
          <div class="team-tags"><span>${escape(item.commission)}</span><span>${escape(item.division)}</span><span>${escape(item.status)}</span></div>
        </div>
        <div class="team-card-actions"><a href="${adminUrl('team-member-edit')}?id=${item.id}" class="cms-action edit">Edit</a><button class="cms-action delete" data-delete>Delete</button></div>
      </article>
    `;
  }

  function renderSearchToolbar(label) {
    return `
      <div class="cms-toolbar">
        <label class="text-sm text-neutral-600">Show <select class="rounded-xl border border-neutral-900/10 bg-white px-3 py-2"><option>10</option><option>25</option></select> entries</label>
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

  const prestasiCategories = ['QRIS', 'KTI', 'Essay', 'Inovasi Desa', 'Kreativitas', 'Ekonomi Syariah'];
  const prestasiCampuses = ['Universitas Jambi', 'UIN Sultan Thaha', 'Alumni'];

  async function renderPrestasiList() {
    const body = renderShell(
      'View Prestasi',
      'Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.',
      `<a href="${adminUrl('prestasi-add')}" class="btn btn-primary">Add Prestasi</a>`
    );
    body.innerHTML = '<div class="admin-card p-8 text-center text-neutral-500">Memuat data prestasi...</div>';

    let items = [];
    try {
      const res = await fetch('/admin/prestasi', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (res.ok) {
        const json = await res.json();
        items = json.data || [];
      }
    } catch (e) { /* use empty fallback */ }

    // Fallback to static data if backend unavailable
    if (items.length === 0 && window.GenBIData && window.GenBIData.prestasi) {
      items = window.GenBIData.prestasi;
    }

    body.innerHTML = `
      <section class="admin-card p-4 md:p-6">
        <div class="cms-toolbar">
          <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-neutral-600">Show <select id="prestasi-per-page" class="rounded-xl border border-neutral-900/10 bg-white px-3 py-2"><option value="10">10</option><option value="25">25</option><option value="50" selected>50</option></select> entries</label>
            <select id="prestasi-filter-category" class="rounded-xl border border-neutral-900/10 bg-white px-3 py-2 text-sm">
              <option value="">Semua Kategori</option>
              ${prestasiCategories.map(c => `<option value="${c}">${c}</option>`).join('')}
            </select>
            <select id="prestasi-filter-campus" class="rounded-xl border border-neutral-900/10 bg-white px-3 py-2 text-sm">
              <option value="">Semua Komisariat</option>
              ${prestasiCampuses.map(c => `<option value="${c}">${c}</option>`).join('')}
            </select>
            <select id="prestasi-filter-status" class="rounded-xl border border-neutral-900/10 bg-white px-3 py-2 text-sm">
              <option value="">Semua Status</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <label class="cms-search">${Admin.icon('search')}<input id="prestasi-search" placeholder="Search prestasi..." /></label>
        </div>
        <div class="admin-responsive-table mt-5">
          <table class="cms-table" id="prestasi-table">
            <thead>
              <tr>
                <th>SL</th>
                <th>Judul</th>
                <th>Nama Anggota</th>
                <th>Kategori</th>
                <th>Tahun</th>
                <th>Komisariat</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="prestasi-tbody">
              ${renderPrestasiRows(items)}
            </tbody>
          </table>
        </div>
      </section>
    `;

    // Bind filters and search
    const allItems = items;
    const filterAndRender = () => {
      const search = (document.querySelector('#prestasi-search')?.value || '').toLowerCase();
      const category = document.querySelector('#prestasi-filter-category')?.value || '';
      const campus = document.querySelector('#prestasi-filter-campus')?.value || '';
      const status = document.querySelector('#prestasi-filter-status')?.value || '';

      const filtered = allItems.filter(item => {
        const title = (item.title || item.judul_prestasi || '').toLowerCase();
        const name = (item.name || item.nama_anggota || '').toLowerCase();
        const itemCategory = (item.category || item.kategori || '').toLowerCase();
        const itemCampus = (item.campus || item.komisariat || '').toLowerCase();
        const itemStatus = (item.status || '').toLowerCase();

        if (search && !title.includes(search) && !name.includes(search) && !itemCategory.includes(search)) return false;
        if (category && itemCategory !== category.toLowerCase()) return false;
        if (campus && itemCampus !== campus.toLowerCase()) return false;
        if (status && itemStatus !== status) return false;
        return true;
      });

      const tbody = document.querySelector('#prestasi-tbody');
      if (tbody) tbody.innerHTML = renderPrestasiRows(filtered);
      bindPrestasiDeleteButtons();
    };

    document.querySelector('#prestasi-search')?.addEventListener('input', filterAndRender);
    document.querySelector('#prestasi-filter-category')?.addEventListener('change', filterAndRender);
    document.querySelector('#prestasi-filter-campus')?.addEventListener('change', filterAndRender);
    document.querySelector('#prestasi-filter-status')?.addEventListener('change', filterAndRender);

    bindPrestasiDeleteButtons();
  }

  function renderPrestasiRows(items) {
    if (items.length === 0) {
      return '<tr><td colspan="8" class="text-center text-neutral-500 py-8">Belum ada data prestasi.</td></tr>';
    }
    return items.map((item, index) => {
      const id = item.id || item.prestasi_id || 0;
      const title = item.title || item.judul_prestasi || '';
      const name = item.name || item.nama_anggota || '';
      const category = item.category || item.kategori || '';
      const year = item.year || item.tahun || '';
      const campus = item.campus || item.komisariat || '';
      const status = item.status || 'draft';
      const image = item.image || item.foto_prestasi || '';
      const statusClass = status === 'published' ? 'cms-pill-green' : status === 'draft' ? 'cms-pill-yellow' : status === 'pending' ? 'cms-pill-blue' : '';

      return `
        <tr>
          <td>${index + 1}</td>
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
          <td>${escape(campus)}</td>
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
          const res = await fetch(`/admin/prestasi/${id}/delete`, {
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

  async function renderPrestasiEditor(isEdit) {
    const id = Number(new URLSearchParams(location.search).get('id')) || 0;
    let item = {
      title: '',
      name: '',
      campus: 'Universitas Jambi',
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
        const res = await fetch(`/admin/prestasi/${id}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (res.ok) {
          const json = await res.json();
          const d = json.data || {};
          item = {
            ...item,
            id: d.id || d.prestasi_id,
            title: d.title || d.judul_prestasi || '',
            name: d.name || d.nama_anggota || '',
            campus: d.campus || d.komisariat || 'Universitas Jambi',
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

    const currentYear = new Date().getFullYear();
    const yearOptions = Array.from({ length: 10 }, (_, i) => currentYear - i);

    body.innerHTML = `
      <form class="medium-editor-layout" id="prestasi-editor-form">
        <main class="medium-editor-canvas">
          <div class="medium-editor-kicker">Prestasi editor</div>
          <section class="story-main-block">
            <label for="prestasi-title-field">Judul Prestasi</label>
            <div id="prestasi-title-field" class="story-title-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis judul prestasi...">${escape(item.title)}</div>
          </section>
          <section class="story-main-block">
            <label for="prestasi-desc-field">Deskripsi Singkat</label>
            <div id="prestasi-desc-field" class="story-excerpt-field" contenteditable="true" spellcheck="true" data-placeholder="Ringkasan singkat untuk list prestasi...">${escape(item.description)}</div>
          </section>
          <div class="medium-editor-divider">
            <div class="medium-editor-kicker">Detail prestasi</div>
          </div>
          <div id="prestasi-editor" class="medium-editor-host"></div>
          <div id="prestasi-editor-fallback" class="editor-fallback${window.EditorJS ? ' hidden' : ''}">
            <article contenteditable="true" data-placeholder="Tulis detail prestasi...">${item.content || ''}</article>
          </div>
          <p class="medium-editor-help">Tekan <strong>Enter</strong> untuk membuat blok baru.</p>
        </main>
        <aside class="editor-config-sidebar medium-config-sidebar">
          <section class="config-card medium-config-card">
            <h2>Informasi Anggota</h2>
            ${control('Nama Anggota', `<input class="config-input" id="prestasi-name" value="${escape(item.name)}" placeholder="Nama penerima prestasi" />`)}
            ${control('Komisariat', `<select class="config-input" id="prestasi-campus">${prestasiCampuses.map(c => `<option ${c === item.campus ? 'selected' : ''}>${c}</option>`).join('')}</select>`)}
            ${control('Institusi', `<input class="config-input" id="prestasi-institution" value="${escape(item.institution)}" placeholder="Nama institusi (opsional)" />`)}
          </section>
          <section class="config-card medium-config-card">
            <h2>Kategori & Tahun</h2>
            ${control('Kategori', `<select class="config-input" id="prestasi-category"><option value="">Pilih kategori</option>${prestasiCategories.map(c => `<option ${c === item.category ? 'selected' : ''}>${c}</option>`).join('')}</select>`)}
            ${control('Tahun', `<select class="config-input" id="prestasi-year">${yearOptions.map(y => `<option ${String(y) === String(item.year) ? 'selected' : ''}>${y}</option>`).join('')}</select>`)}
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
            ${control('Publish Status', `<select class="config-input" id="prestasi-status">
              <option value="draft" ${item.status === 'draft' ? 'selected' : ''}>Draft</option>
              <option value="published" ${item.status === 'published' ? 'selected' : ''}>Published</option>
              <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pending</option>
              <option value="archived" ${item.status === 'archived' ? 'selected' : ''}>Archived</option>
            </select>`)}
          </section>
          <button type="submit" class="btn btn-primary w-full">${isEdit ? 'Update Prestasi' : 'Submit Prestasi'}</button>
        </aside>
      </form>
    `;

    // Initialize Editor.js for content
    const prestasiEditorInstance = initPrestasiEditor(item);

    // Image upload handler
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
        const res = await fetch('/admin/prestasi/upload', {
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

      // Get editor content
      let editorContent = '';
      if (prestasiEditorInstance?.save) {
        try {
          const outputData = await prestasiEditorInstance.save();
          editorContent = outputData.blocks.map(block => {
            if (block.type === 'paragraph') return `<p>${block.data.text}</p>`;
            if (block.type === 'header') return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`;
            if (block.type === 'list') {
              const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
              return `<${tag}>${block.data.items.map(i => `<li>${i}</li>`).join('')}</${tag}>`;
            }
            if (block.type === 'quote') return `<blockquote><p>${block.data.text}</p></blockquote>`;
            if (block.type === 'image') return `<figure><img src="${block.data.file?.url || block.data.url || ''}" alt="${block.data.caption || ''}" /></figure>`;
            return `<p>${block.data.text || ''}</p>`;
          }).join('\n');
        } catch (err) {
          const fallbackEl = document.querySelector('#prestasi-editor-fallback article');
          if (fallbackEl) editorContent = fallbackEl.innerHTML;
        }
      } else {
        const fallbackEl = document.querySelector('#prestasi-editor-fallback article');
        if (fallbackEl) editorContent = fallbackEl.innerHTML;
      }

      const ok = await Admin.showConfirm({
        title: isEdit ? 'Update prestasi?' : 'Submit prestasi?',
        message: isEdit ? 'Data prestasi akan diperbarui di database.' : 'Prestasi baru akan disimpan ke database.',
        confirmText: isEdit ? 'Update' : 'Submit'
      });
      if (!ok) return;

      const payload = {
        title: document.querySelector('#prestasi-title-field')?.textContent?.trim() || '',
        name: document.querySelector('#prestasi-name')?.value?.trim() || '',
        campus: document.querySelector('#prestasi-campus')?.value || '',
        category: document.querySelector('#prestasi-category')?.value || '',
        year: document.querySelector('#prestasi-year')?.value || '',
        description: document.querySelector('#prestasi-desc-field')?.textContent?.trim() || '',
        content: editorContent || item.content || '',
        image: document.querySelector('#prestasi-image-url')?.value?.trim() || '',
        institution: document.querySelector('#prestasi-institution')?.value?.trim() || '',
        status: document.querySelector('#prestasi-status')?.value || 'draft',
        meta_title: document.querySelector('#prestasi-meta-title')?.value?.trim() || '',
        meta_keyword: document.querySelector('#prestasi-meta-keyword')?.value?.trim() || '',
        meta_description: document.querySelector('#prestasi-meta-desc')?.value?.trim() || '',
      };

      const csrfToken = (API && API.getCsrfToken) ? API.getCsrfToken() : '';
      const url = isEdit ? `/admin/prestasi/${id}/update` : '/admin/prestasi';

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
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
      const res = await fetch('/admin/prestasi-tokens', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
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
          const res = await fetch(`/admin/prestasi-tokens/${id}/revoke`, {
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
      const res = await fetch('/admin/prestasi-tokens', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        credentials: 'same-origin',
        body: JSON.stringify({ label, expires_at: expiresAt || null })
      });
      const result = await res.json();
      if (res.ok && result.data?.token) {
        const baseUrl = window.location.origin;
        const submitUrl = `${baseUrl}/prestasi/submit/${result.data.token}`;
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
                const res = await fetch('/admin/prestasi/upload', {
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
