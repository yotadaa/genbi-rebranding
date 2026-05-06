(function () {
  'use strict';

  const { site, settingTabs } = window.GenBIData;
  const { renderAdminShell, icon, showToast } = window.GenBIAdmin;

  let active = 'logo';

  renderAdminShell('settings');
  renderTabs();
  renderPanel();
  document.querySelector('#save-all')?.addEventListener('click', () => showToast('Semua perubahan disimpan pada mode simulasi.'));

  function escapeHtml(value = '') {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderTabs() {
    const root = document.querySelector('#settings-tabs');
    if (!root) return;
    root.innerHTML = settingTabs.map((tab) => `
      <button type="button" class="admin-tab ${tab.key === active ? 'is-active' : ''}" data-tab="${tab.key}">
        ${icon(tab.icon)}
        ${tab.label}
      </button>
    `).join('');
    root.querySelectorAll('button').forEach((button) => {
      button.addEventListener('click', () => {
        active = button.dataset.tab;
        renderTabs();
        renderPanel();
      });
    });
  }

  function panelLayout(title, description, content, aside = '') {
    return `
      <div class="admin-editor-shell slide-in">
        <header class="admin-editor-head">
          <div>
            <p class="eyebrow">${title}</p>
            <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-neutral-950">${title} Section</h2>
            <p class="mt-4 max-w-3xl text-base leading-7 text-neutral-600">${description}</p>
          </div>
          ${aside ? `<div class="admin-editor-aside">${aside}</div>` : ''}
        </header>
        <div class="admin-editor-canvas">${content}</div>
      </div>
    `;
  }

  function editorBlock(label, value = '', options = {}) {
    const isLong = options.long || String(value).length > 80;
    const htmlValue = options.rich ? value : escapeHtml(value).replaceAll('\n', '<br>');
    return `
      <section class="block-editor-item ${isLong ? 'is-long' : ''}" data-block>
        <div class="block-editor-toolbar" aria-hidden="true">
          <button type="button" data-command="bold"><strong>B</strong></button>
          <button type="button" data-command="italic"><em>I</em></button>
          <button type="button" data-command="insertUnorderedList">• List</button>
        </div>
        <label class="block-editor-label">${label}</label>
        <div class="block-editor-input" contenteditable="true" spellcheck="false" data-placeholder="Tulis ${label.toLowerCase()}...">${htmlValue}</div>
      </section>
    `;
  }

  function fileBlock(label, note = '') {
    return `
      <section class="block-editor-item">
        <label class="block-editor-label">${label}</label>
        <div class="admin-upload-zone">
          <div>
            <p class="font-bold text-neutral-950">Pilih file baru</p>
            <p class="mt-1 text-sm leading-6 text-neutral-600">${note || 'Upload masih simulasi frontend. Backend bisa disambungkan nanti.'}</p>
          </div>
          <input type="file" class="admin-file-input" />
        </div>
      </section>
    `;
  }

  function imagePreview(title, image, description) {
    return `
      <div class="admin-media-preview">
        <img src="${image}" alt="${escapeHtml(title)}" />
        <div>
          <p class="font-bold text-neutral-950">${title}</p>
          <p class="mt-1 text-sm leading-6 text-neutral-600">${description}</p>
        </div>
      </div>
    `;
  }

  function selectBlock(label, value = 'Show') {
    return `
      <section class="block-editor-item">
        <label class="block-editor-label">${label}</label>
        <div class="block-choice-row" role="group" aria-label="${label}">
          <button type="button" class="block-choice ${value === 'Show' ? 'is-active' : ''}">Show</button>
          <button type="button" class="block-choice ${value === 'Hide' ? 'is-active' : ''}">Hide</button>
        </div>
      </section>
    `;
  }

  function attachEditorEvents(root) {
    root.querySelectorAll('.block-editor-toolbar button').forEach((button) => {
      button.addEventListener('mousedown', (event) => event.preventDefault());
      button.addEventListener('click', () => {
        const editor = button.closest('[data-block]')?.querySelector('.block-editor-input');
        if (!editor) return;
        editor.focus();
        document.execCommand(button.dataset.command, false, null);
      });
    });
    root.querySelectorAll('.block-choice').forEach((button) => {
      button.addEventListener('click', () => {
        button.parentElement.querySelectorAll('.block-choice').forEach((item) => item.classList.remove('is-active'));
        button.classList.add('is-active');
      });
    });
  }

  function renderPanel() {
    const root = document.querySelector('#settings-panel');
    if (!root) return;
    const renderers = {
      logo: renderLogo,
      favicon: renderFavicon,
      topbar: renderTopbar,
      footer: renderFooter,
      email: renderEmail,
      banner: renderBanner,
      sidebar: renderSidebar,
      color: renderColor,
    };
    root.innerHTML = (renderers[active] || renderLogo)();
    attachEditorEvents(root);
    root.querySelectorAll('[data-save-setting]').forEach((button) => {
      button.addEventListener('click', () => showToast(`${button.dataset.saveSetting} disimpan pada mode simulasi.`));
    });
  }

  function renderLogo() {
    return panelLayout('Logo', 'Logo resmi dipakai konsisten di header publik, footer, dan admin panel. Area edit dibuat lega agar tidak terasa seperti form sempit.', `
      ${imagePreview(site.name, site.logo, 'Logo utama dari public uploads.')}
      ${fileBlock('New Photo', 'Gunakan PNG transparan atau SVG agar tetap tajam di header dan footer.')}
      <button data-save-setting="Logo" class="btn btn-primary">Update Logo</button>
    `, `<span class="editor-status-pill">Identity</span>`);
  }

  function renderFavicon() {
    return panelLayout('Favicon', 'Favicon perlu tetap sederhana dan mudah dikenali saat browser menampilkan tab kecil.', `
      ${imagePreview('Favicon GenBI', site.logo, 'Gunakan simbol paling ringkas dari logo agar terbaca pada ukuran kecil.')}
      ${fileBlock('New Favicon', 'Disarankan file .ico, .png, atau SVG sederhana.')}
      <button data-save-setting="Favicon" class="btn btn-primary">Update Favicon</button>
    `, `<span class="editor-status-pill">Browser tab</span>`);
  }

  function renderTopbar() {
    return panelLayout('Top Bar', 'Top bar menyimpan kontak cepat yang tampil di bagian atas website publik.', `
      ${editorBlock('Top Bar Email', site.email)}
      ${editorBlock('Top Bar Phone Number', site.phone)}
      <button data-save-setting="Top Bar" class="btn btn-primary">Update Top Bar</button>
    `);
  }

  function renderFooter() {
    return panelLayout('Footer', 'Footer dibuat ringkas agar kontak, alamat, copyright, dan CTA tetap jelas tanpa terlalu panjang.', `
      ${editorBlock('Column 1 Title', site.name)}
      ${editorBlock('Column 4 Title', 'ADDRESS')}
      ${editorBlock('Footer Copyright', 'Copyright © 2026, GenBI Provinsi Jambi')}
      ${editorBlock('Footer Address', site.address, { long: true })}
      ${editorBlock('Footer Email', site.email)}
      ${editorBlock('Footer Phone', `Support: ${site.phone}`)}
      ${editorBlock('Number of Recent News', '3')}
      ${editorBlock('Number of Recent Portfolios', '0')}
      <button data-save-setting="Footer" class="btn btn-primary">Update Footer</button>
    `);
  }

  function renderEmail() {
    return panelLayout('Email', 'Email routing perlu eksplisit agar form kontak tidak mengirim pesan ke alamat yang salah.', `
      ${editorBlock('Send Email From', site.email)}
      ${editorBlock('Receive Email To', site.email)}
      <button data-save-setting="Email" class="btn btn-primary">Update Email</button>
    `);
  }

  function renderBanner() {
    return panelLayout('Banner', 'Hero publik memakai slider 1 dan slider 4 sebagai background utama. Pengaturan banner dibuat seperti mengatur blok konten, bukan input sempit.', `
      <div class="admin-media-grid">
        ${site.heroSlides.map((slide, index) => imagePreview(`Slider ${index === 0 ? '1' : '4'}`, slide.image, slide.caption)).join('')}
      </div>
      ${editorBlock('Hero Badge', 'Energi untuk Negeri')}
      ${editorBlock('Hero Title', 'Membangun generasi muda yang kompeten, komunikatif, dan siap menjadi agen perubahan.', { long: true })}
      ${editorBlock('Hero Subtitle', 'Kolaborasi lintas kampus dan masyarakat')}
      ${fileBlock('New Banner Photo', 'Gambar akan menjadi background hero. Pastikan kontras teks tetap terbaca.')}
      <button data-save-setting="Banner" class="btn btn-primary">Update Banner</button>
    `);
  }

  function renderSidebar() {
    return panelLayout('Sidebar', 'Sidebar admin dirapikan agar dashboard, page, dan settings menjadi prioritas sebelum modul lain aktif.', `
      ${editorBlock('News Page Heading', 'Categories')}
      ${editorBlock('Recent Posts Heading', 'Recent Posts')}
      ${editorBlock('Upcoming Events Heading', 'Upcoming Events')}
      ${editorBlock('Past Events Heading', 'Past Events')}
      ${editorBlock('Quick Contact Heading', 'Quick Contact')}
      <button data-save-setting="Sidebar" class="btn btn-primary">Update Sidebar</button>
    `);
  }

  function renderColor() {
    return panelLayout('Color', 'Aksen biru memakai warna identitas website awal agar admin dan publik terasa satu sistem.', `
      <section class="block-editor-item">
        <label class="block-editor-label">Primary Color</label>
        <div class="color-editor-row">
          <div class="block-editor-input" contenteditable="true">114B9A</div>
          <span class="color-swatch-blue"></span>
        </div>
      </section>
      <div class="rounded-2xl bg-blue-50 p-5 text-sm leading-6 text-blue-950">
        Primary: #114B9A. Hover: #0C3572. Soft background: #EEF6FF.
      </div>
      <button data-save-setting="Color" class="btn btn-primary">Update Color</button>
    `);
  }
})();
