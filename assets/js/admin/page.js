(function () {
  'use strict';

  const { site } = window.GenBIData;
  const { renderAdminShell, showToast, icon } = window.GenBIAdmin;

  const pageTabs = [
    { key: 'home', label: 'Home', title: 'Home Page', description: 'Atur meta, hero, welcome section, video, progress, program, counter, team, dan news dari satu canvas editor.' },
    { key: 'about', label: 'About', title: 'About Page', description: 'Konten tentang GenBI, visi, misi, tujuan, dan narasi organisasi.' },
    { key: 'faq', label: 'FAQ', title: 'FAQ Page', description: 'Kelola pertanyaan umum dengan blok tanya jawab yang mudah dibaca.' },
    { key: 'service', label: 'Service', title: 'Service Page', description: 'Modul bawaan CMS tetap disiapkan, tetapi tampil sebagai section editor yang rapi.' },
    { key: 'testimonial', label: 'Testimonial', title: 'Testimonial Page', description: 'Konten testimonial dapat diaktifkan atau disembunyikan dari halaman publik.' },
    { key: 'news', label: 'News', title: 'News Page', description: 'Atur heading, meta, dan pengantar halaman news.' },
    { key: 'event', label: 'Event', title: 'Event Page', description: 'Atur heading dan meta untuk halaman event.' },
    { key: 'contact', label: 'Contact', title: 'Contact Page', description: 'Atur alamat, email, telepon, dan kode embed peta.' },
    { key: 'search', label: 'Search', title: 'Search Page', description: 'Atur heading halaman pencarian.' },
    { key: 'terms', label: 'Terms', title: 'Terms Page', description: 'Kelola terms and conditions dengan editor panjang.' },
    { key: 'privacy', label: 'Privacy', title: 'Privacy Page', description: 'Kelola privacy policy dengan editor panjang.' },
    { key: 'team', label: 'Team', title: 'Team Page', description: 'Atur heading dan meta direktori anggota.' },
    { key: 'portfolio', label: 'Portfolio', title: 'Portfolio Page', description: 'Modul portfolio tetap ada untuk kompatibilitas CMS lama.' },
  ];

  const content = {
    home: [
      { type: 'group', title: 'Meta Items', blocks: [
        ['Title', 'Official GenBI Jambi'],
        ['Meta Keyword', 'GenBI Jambi'],
        ['Meta Description', 'GenBI Provinsi Jambi'],
      ] },
      { type: 'group', title: 'Welcome Section', blocks: [
        ['Title', 'A TRUE LEADER CAN'],
        ['Subtitle', 'HELP YOU IN CHARACTER'],
        ['Text', '<p><strong>GenBI Provinsi Jambi</strong> merupakan komunitas yang terdiri dari kumpulan mahasiswa penerima beasiswa Bank Indonesia dari Universitas Jambi dan UIN STS Jambi.</p>', { rich: true, long: true }],
        ['Video Embed', '<iframe src="https://www.youtube.com/embed/ashDIp7d29s?si=PYdS4EG3z5KA69Ur" allowfullscreen></iframe>', { code: true, long: true }],
        ['Progress Bar 1 Text', 'Universitas Jambi'],
        ['Progress Bar 1 Value', '50'],
        ['Progress Bar 2 Text', 'UIN STS Jambi'],
        ['Progress Bar 2 Value', '50'],
        ['Progress Bar 3 Text', 'All Team'],
        ['Progress Bar 3 Value', '100'],
        ['Show on Home?', 'Show', { choice: true }],
      ] },
      { type: 'group', title: 'Why Choose Us Section', blocks: [
        ['Title', 'WHY CHOOSE GENBI JAMBI'],
        ['Subtitle', 'Because GenBI Jambi Is Very Synergized For The Country'],
        ['Show on Home?', 'Show', { choice: true }],
      ] },
      { type: 'group', title: 'Feature Section', blocks: [
        ['Title', 'SPECIAL ACTIVITY'],
        ['Subtitle', "Types Of Activities We've Done"],
        ['Show on Home?', 'Show', { choice: true }],
      ] },
      { type: 'group', title: 'Team Section', blocks: [
        ['Title', 'BPI GENBI PROVINSI JAMBI'],
        ['Subtitle', 'STRUKTUR ORGANISASIS BPI GENBI JAMBI'],
        ['Show on Home?', 'Show', { choice: true }],
      ] },
      { type: 'group', title: 'Blog Section', blocks: [
        ['Title', 'LATEST NEWS'],
        ['Subtitle', 'All our latest news are listed below'],
        ['How many item to show?', '3'],
        ['Show on Home?', 'Show', { choice: true }],
      ] },
    ],
    about: [
      { type: 'group', title: 'About Content', blocks: [
        ['About Heading', 'ABOUT US'],
        ['About Content', '<h2>Tentang GenBI</h2><p>GenBI adalah singkatan dari Generasi Baru Indonesia, sebuah komunitas yang terdiri dari mahasiswa penerima beasiswa Bank Indonesia dari berbagai perguruan tinggi negeri di seluruh Indonesia. Komunitas ini diresmikan pada tanggal 11 November 2011 oleh Gubernur BI ke-14, Dr. Darmin Nasution.</p><p>Generasi Baru Indonesia Provinsi Jambi terdiri dari 2 Universitas pilihan yaitu Universitas Jambi dan UIN Sulthan Thaha Saifuddin Jambi.</p>', { rich: true, long: true }],
        ['Meta Title', 'About Us - GenBI Provinsi Jambi'],
        ['Meta Keyword', 'GenBI Jambi'],
        ['Meta Description', 'GenBI Provinsi Jambi'],
      ] },
    ],
    faq: simpleMeta('FAQ Heading', 'FAQ', 'FAQ - GenBI Provinsi Jambi'),
    service: simpleMeta('Service Heading', 'Our Services', 'Our Services - GenBI Provinsi Jambi'),
    testimonial: simpleMeta('Testimonial Heading', 'TESTIMONIAL', 'Testimonial - GenBI Provinsi Jambi'),
    news: simpleMeta('News Heading', 'NEWS', 'News - GenBI Provinsi Jambi'),
    event: simpleMeta('Event Heading', 'EVENTS', 'Events - GenBI Provinsi Jambi'),
    contact: [
      { type: 'group', title: 'Contact Items', blocks: [
        ['Contact Heading', 'CONTACT'],
        ['Contact Address', site.address, { long: true }],
        ['Contact Email', site.email],
        ['Contact Phone', 'Admin: 0895 1009 4970'],
        ['Contact Map iframe Code', '<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" loading="lazy"></iframe>', { code: true, long: true }],
        ['Meta Title', 'Contact - GenBI Provinsi Jambi'],
        ['Meta Keyword', 'GenBI Jambi'],
        ['Meta Description', 'GenBI Provinsi Jambi'],
      ] },
    ],
    search: simpleMeta('Search Heading', 'SEARCH BY:', 'Search - GenBI Provinsi Jambi'),
    terms: legalBlocks('Term & Condition Heading', 'TERMS & CONDITIONS', 'Terms and Conditions - GenBI Provinsi Jambi'),
    privacy: legalBlocks('Privacy Policy Heading', 'PRIVACY POLICY', 'Privacy Policy - GenBI Provinsi Jambi'),
    team: simpleMeta('Team Heading', 'Our Team', 'Team - GenBI Provinsi Jambi'),
    portfolio: simpleMeta('Portfolio Heading', 'PORTFOLIO', 'Portfolio - GenBI Provinsi Jambi'),
  };

  let active = window.location.hash?.replace('#', '') || 'home';
  if (!pageTabs.some((tab) => tab.key === active)) active = 'home';

  renderAdminShell('page');
  renderTabs();
  renderPage();
  document.querySelector('#save-page')?.addEventListener('click', () => showToast('Halaman disimpan pada mode simulasi.'));

  function simpleMeta(headingLabel, headingValue, metaTitle) {
    return [
      { type: 'group', title: 'Meta Items', blocks: [
        [headingLabel, headingValue],
        ['Meta Title', metaTitle],
        ['Meta Keyword', 'GenBI Jambi'],
        ['Meta Description', 'GenBI Provinsi Jambi'],
      ] },
    ];
  }

  function legalBlocks(label, value, metaTitle) {
    return [
      { type: 'group', title: 'Legal Content', blocks: [
        [label, value],
        ['Content', '<p>Tulis kebijakan resmi di sini. Gunakan paragraf pendek agar mudah dibaca pengunjung.</p><p>Bagian ini dapat diganti dengan dokumen legal final ketika sudah tersedia.</p>', { rich: true, long: true }],
        ['Meta Title', metaTitle],
        ['Meta Keyword', 'GenBI Jambi'],
        ['Meta Description', 'GenBI Provinsi Jambi'],
      ] },
    ];
  }

  function escapeHtml(value = '') {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderTabs() {
    const root = document.querySelector('#page-tabs');
    if (!root) return;
    root.innerHTML = pageTabs.map((tab) => `
      <button type="button" class="admin-tab ${tab.key === active ? 'is-active' : ''}" data-tab="${tab.key}">${tab.label}</button>
    `).join('');
    root.querySelectorAll('button').forEach((button) => {
      button.addEventListener('click', () => {
        active = button.dataset.tab;
        history.replaceState(null, '', `#${active}`);
        renderTabs();
        renderPage();
      });
    });
  }

  function renderPage() {
    const canvas = document.querySelector('#page-editor-canvas');
    const overview = document.querySelector('#page-editor-overview');
    const tab = pageTabs.find((item) => item.key === active) || pageTabs[0];
    const groups = content[active] || content.home;
    if (!canvas) return;
    canvas.innerHTML = `
      <section class="block-page-hero slide-in">
        <p class="eyebrow">Admin Page</p>
        <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-neutral-950">${tab.title}</h2>
        <p class="mt-4 max-w-3xl text-base leading-7 text-neutral-600">${tab.description}</p>
      </section>
      ${groups.map(renderGroup).join('')}
    `;
    if (overview) {
      overview.innerHTML = `
        <p class="eyebrow">Outline</p>
        <div class="mt-4 grid gap-2">
          ${groups.map((group, index) => `
            <button type="button" class="outline-link" data-target="group-${index}">
              <span>${group.title}</span>
              <span>${group.blocks.length}</span>
            </button>
          `).join('')}
        </div>
        <div class="mt-6 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
          Editor ini memakai blok editable agar pengetikan terasa seperti area konten, bukan form sempit.
        </div>
      `;
      overview.querySelectorAll('.outline-link').forEach((button) => {
        button.addEventListener('click', () => document.querySelector(`#${button.dataset.target}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      });
    }
    attachEditorEvents(canvas);
  }

  function renderGroup(group, index) {
    return `
      <section id="group-${index}" class="block-editor-group slide-in">
        <div class="block-group-header">
          <span>${icon('documentText')}</span>
          <h3>${group.title}</h3>
        </div>
        <div class="grid gap-4">
          ${group.blocks.map((block) => renderBlock(block[0], block[1], block[2] || {})).join('')}
        </div>
      </section>
    `;
  }

  function renderBlock(label, value, options = {}) {
    if (options.choice) {
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
    const long = options.long || String(value).length > 90;
    const htmlValue = options.rich || options.code ? value : escapeHtml(value).replaceAll('\n', '<br>');
    return `
      <section class="block-editor-item ${long ? 'is-long' : ''} ${options.code ? 'is-code' : ''}" data-block>
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
})();
