(function () {
  'use strict';

  const { site } = window.GenBIData;
  const { renderAdminShell, showToast, icon } = window.GenBIAdmin;
  const { routeUrl } = window.GenBIApp;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const pageTabs = [
    { key: 'home', label: 'Home', title: 'Home Page', description: 'Atur meta, hero, welcome section, video, progress, program, counter, team, dan news dari satu canvas editor.' },
    { key: 'about', label: 'About', title: 'About Page', description: 'Konten tentang GenBI, visi, misi, tujuan, dan narasi organisasi.' },
    { key: 'faq', label: 'FAQ', title: 'FAQ Page', description: 'Kelola pertanyaan umum dengan blok tanya jawab yang mudah dibaca.' },
    { key: 'service', label: 'Service', title: 'Service Page', description: 'Modul bawaan CMS tetap disiapkan, tetapi tampil sebagai section editor yang rapi.' },
    { key: 'testimonial', label: 'Testimonial', title: 'Testimonial Page', description: 'Konten testimonial dapat diaktifkan atau disembunyikan dari halaman publik.' },
    { key: 'news', label: 'News', title: 'News Page', description: 'Atur heading, meta, dan pengantar halaman news.' },
    { key: 'event', label: 'Event', title: 'Event Page', description: 'Atur heading dan meta untuk halaman event.' },
    { key: 'contact', label: 'Contact', title: 'Contact Page', description: 'Atur alamat, email, telepon, dan peta lokasi Google Maps.' },
    { key: 'search', label: 'Search', title: 'Search Page', description: 'Atur heading halaman pencarian.' },
    { key: 'terms', label: 'Terms', title: 'Terms Page', description: 'Kelola terms and conditions dengan editor panjang.' },
    { key: 'privacy', label: 'Privacy', title: 'Privacy Page', description: 'Kelola privacy policy dengan editor panjang.' },
    { key: 'team', label: 'Team', title: 'Team Page', description: 'Atur heading dan meta direktori anggota.' },
    { key: 'portfolio', label: 'Portfolio', title: 'Portfolio Page', description: 'Modul portfolio tetap ada untuk kompatibilitas CMS lama.' },
  ];

  const content = {
    home: simpleMeta('Title', 'Official GenBI Jambi'),
    about: simpleMeta('About Heading', 'ABOUT US', 'About Us - GenBI Provinsi Jambi'),
    faq: simpleMeta('FAQ Heading', 'FAQ', 'FAQ - GenBI Provinsi Jambi'),
    service: simpleMeta('Service Heading', 'Our Services', 'Our Services - GenBI Provinsi Jambi'),
    testimonial: simpleMeta('Testimonial Heading', 'TESTIMONIAL', 'Testimonial - GenBI Provinsi Jambi'),
    news: simpleMeta('News Heading', 'NEWS', 'News - GenBI Provinsi Jambi'),
    event: simpleMeta('Event Heading', 'EVENTS', 'Events - GenBI Provinsi Jambi'),
    search: simpleMeta('Search Heading', 'SEARCH BY:', 'Search - GenBI Provinsi Jambi'),
    terms: legalBlocks('Term & Condition Heading', 'TERMS & CONDITIONS', 'Terms and Conditions - GenBI Provinsi Jambi'),
    privacy: legalBlocks('Privacy Policy Heading', 'PRIVACY POLICY', 'Privacy Policy - GenBI Provinsi Jambi'),
    team: simpleMeta('Team Heading', 'Our Team', 'Team - GenBI Provinsi Jambi'),
    portfolio: simpleMeta('Portfolio Heading', 'PORTFOLIO', 'Portfolio - GenBI Provinsi Jambi'),
  };

  const initialContact = {
    place_name: 'Bank Indonesia Jambi',
    address: site.address,
    email: site.email,
    phone: site.phone,
    coordinates_label: '9HRM+74 Telanaipura, Kota Jambi, Jambi',
    maps_url: 'https://www.google.com/maps/place/Bank+Indonesia+Jambi/@-1.6092871,103.5827899,17z/data=!3m1!4b1!4m6!3m5!1s0x2e25885c04515687:0xe424228e0264e09a!8m2!3d-1.6092871!4d103.5827899!16s%2Fg%2F1pzr95__x?hl=id&entry=ttu',
    latitude: '-1.609287',
    longitude: '103.582790',
    meta_title: 'Contact | GenBI Provinsi Jambi',
    meta_keyword: 'GenBI Jambi, Contact',
    meta_description: 'Hubungi GenBI Provinsi Jambi untuk kolaborasi, informasi kegiatan, dan kebutuhan komunikasi resmi.',
  };

  let active = window.location.hash?.replace('#', '') || 'home';
  if (!pageTabs.some((tab) => tab.key === active)) active = 'home';

  renderAdminShell('page');
  renderTabs();
  renderPage();
  document.querySelector('#save-page')?.addEventListener('click', () => {
    if (active === 'contact') {
      submitContactSettings();
      return;
    }
    showToast('Halaman disimpan pada mode simulasi.');
  });

  function simpleMeta(label, value, metaTitle = 'GenBI Provinsi Jambi') {
    return [
      { type: 'group', title: 'Meta Items', blocks: [
        [label, value],
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

  function route(name, params = {}) {
    return routeUrl(`admin.${name}`, params);
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
    if (!canvas) return;

    if (active === 'contact') {
      renderContactSettings(canvas, overview, tab);
      return;
    }

    const groups = content[active] || content.home;
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
    const long = options.long || String(value).length > 90;
    const htmlValue = options.rich || options.code ? value : escapeHtml(value).replaceAll('\n', '<br>');
    return `
      <section class="block-editor-item ${long ? 'is-long' : ''} ${options.code ? 'is-code' : ''}" data-block>
        <div class="block-editor-toolbar" aria-hidden="true">
          <button type="button" data-command="bold"><strong>B</strong></button>
          <button type="button" data-command="italic"><em>I</em></button>
          <button type="button" data-command="insertUnorderedList">&#8226; List</button>
        </div>
        <label class="block-editor-label">${label}</label>
        <div class="block-editor-input" contenteditable="true" spellcheck="false" data-placeholder="Tulis ${label.toLowerCase()}...">${htmlValue}</div>
      </section>
    `;
  }

  function escapeHtml(value = '') {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
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
  }

  function contactEmbedUrl(payload) {
    const lat = String(payload.latitude || '').trim();
    const lng = String(payload.longitude || '').trim();
    if (!lat || !lng) return '';
    return `https://www.google.com/maps?q=${encodeURIComponent(`${lat},${lng}`)}&z=17&output=embed`;
  }

  async function loadContactSettings() {
    try {
      const res = await fetch(route('contactSetting'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (!res.ok) throw new Error('Fetch failed');
      const json = await res.json();
      return { ...initialContact, ...(json.data || {}) };
    } catch (_) {
      return { ...initialContact };
    }
  }

  function renderContactSettings(canvas, overview, tab) {
    canvas.innerHTML = `
      <section class="block-page-hero slide-in">
        <p class="eyebrow">Admin Page</p>
        <h2 class="serif mt-3 text-4xl font-semibold tracking-tight text-neutral-950">${tab.title}</h2>
        <p class="mt-4 max-w-3xl text-base leading-7 text-neutral-600">${tab.description}</p>
      </section>
      <section class="admin-contact-grid mt-6 slide-in" id="admin-contact-grid">
        <form class="admin-contact-form admin-card p-5 md:p-6" id="admin-contact-form"></form>
        <aside class="admin-contact-preview admin-card p-5 md:p-6" id="admin-contact-preview"></aside>
      </section>
    `;
    if (overview) {
      overview.innerHTML = `
        <p class="eyebrow">Contact</p>
        <div class="mt-4 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
          Data kontak ini dipakai langsung oleh halaman publik <strong>/contact</strong>.
        </div>
      `;
    }

    const form = canvas.querySelector('#admin-contact-form');
    const preview = canvas.querySelector('#admin-contact-preview');
    if (!form || !preview) return;

    loadContactSettings().then((data) => {
      form.innerHTML = renderContactFormFields(data);
      bindContactForm(form, preview);
      refreshContactPreview(form, preview);
    });
  }

  function renderContactFormFields(data) {
    return `
      <h3 class="serif text-2xl font-semibold tracking-tight text-neutral-950">Contact Settings</h3>
      <p class="mt-2 text-sm leading-6 text-neutral-600">Kelola lokasi dan metadata kontak untuk halaman publik.</p>
      <div class="mt-5 grid gap-4">
        ${contactInput('Place Name', 'place_name', data.place_name)}
        ${contactTextarea('Address', 'address', data.address)}
        <div class="grid gap-4 md:grid-cols-2">
          ${contactInput('Email', 'email', data.email, 'email')}
          ${contactInput('Phone', 'phone', data.phone)}
        </div>
        ${contactInput('Coordinates Label', 'coordinates_label', data.coordinates_label)}
        ${contactInput('Google Maps Link', 'maps_url', data.maps_url)}
        <div class="grid gap-4 md:grid-cols-2">
          ${contactInput('Latitude', 'latitude', data.latitude, 'text', 'Contoh: -1.609287')}
          ${contactInput('Longitude', 'longitude', data.longitude, 'text', 'Contoh: 103.582790')}
        </div>
        ${contactInput('Meta Title', 'meta_title', data.meta_title)}
        ${contactInput('Meta Keyword', 'meta_keyword', data.meta_keyword)}
        ${contactTextarea('Meta Description', 'meta_description', data.meta_description)}
      </div>
      <div class="mt-6">
        <button type="submit" class="btn btn-primary">Simpan Contact Settings</button>
      </div>
    `;
  }

  function contactInput(label, name, value, type = 'text', placeholder = '') {
    return `
      <label class="config-field">
        <span>${label}</span>
        <input class="config-input" type="${type}" name="${name}" value="${escapeHtml(value || '')}" placeholder="${escapeHtml(placeholder)}" />
      </label>
    `;
  }

  function contactTextarea(label, name, value) {
    return `
      <label class="config-field">
        <span>${label}</span>
        <textarea class="config-input min-h-24" name="${name}" rows="3">${escapeHtml(value || '')}</textarea>
      </label>
    `;
  }

  function contactPayload(form) {
    return {
      place_name: form.elements.place_name.value.trim(),
      address: form.elements.address.value.trim(),
      email: form.elements.email.value.trim(),
      phone: form.elements.phone.value.trim(),
      coordinates_label: form.elements.coordinates_label.value.trim(),
      maps_url: form.elements.maps_url.value.trim(),
      latitude: form.elements.latitude.value.trim(),
      longitude: form.elements.longitude.value.trim(),
      meta_title: form.elements.meta_title.value.trim(),
      meta_keyword: form.elements.meta_keyword.value.trim(),
      meta_description: form.elements.meta_description.value.trim(),
    };
  }

  function refreshContactPreview(form, preview) {
    const payload = contactPayload(form);
    const embedUrl = contactEmbedUrl(payload);
    preview.innerHTML = `
      <p class="eyebrow">Live Preview</p>
      <article class="admin-contact-preview-card">
        <span class="blue-badge">Map preview</span>
        <h4 class="serif mt-4 text-2xl font-semibold tracking-tight text-blue-950">${escapeHtml(payload.place_name || 'Contact Location')}</h4>
        ${payload.coordinates_label ? `<p class="mt-3 text-xs font-bold uppercase tracking-[0.11em] text-blue-800">${escapeHtml(payload.coordinates_label)}</p>` : ''}
        <p class="mt-3 text-sm leading-7 text-neutral-600">${escapeHtml(payload.address || 'Alamat belum diisi')}</p>
        ${payload.maps_url ? `<a class="btn btn-primary mt-5 w-fit" href="${escapeHtml(payload.maps_url)}" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>` : ''}
      </article>
      <div class="admin-contact-preview-map">
        ${embedUrl ? `<iframe src="${escapeHtml(embedUrl)}" title="Map preview" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>` : '<div class="admin-contact-preview-empty"><strong>Map preview belum aktif.</strong><p>Isi latitude dan longitude untuk memunculkan peta.</p></div>'}
      </div>
    `;
  }

  function bindContactForm(form, preview) {
    form.addEventListener('input', () => refreshContactPreview(form, preview));
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      submitContactSettings(form);
    });
  }

  async function submitContactSettings(form = document.querySelector('#admin-contact-form')) {
    if (!form) return;
    const payload = contactPayload(form);
    const res = await fetch(route('contactSettingUpdate'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
      showToast(json.error || 'Gagal menyimpan contact settings.');
      return;
    }
    showToast('Contact settings berhasil disimpan.');
    renderPage();
  }
})();

