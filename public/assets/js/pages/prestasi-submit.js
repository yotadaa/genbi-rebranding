(function () {
  'use strict';
  const { renderShell, getParam } = window.GenBIApp;
  const API = window.GenBIAPI;
  const Core = window.GenBIAPICore;

  renderShell('prestasi');

  const root = document.querySelector('#prestasi-submit-root');
  const token = getTokenFromUrl();

  if (!token) {
    renderInvalid('Token tidak ditemukan di URL.');
  } else {
    validateAndRender(token);
  }

  function getTokenFromUrl() {
    // Extract token from /prestasi/submit/{token} path
    const path = window.location.pathname;
    const match = path.match(/\/prestasi\/submit\/([a-f0-9]+)/i);
    if (match) return match[1];
    // Fallback: query param
    return getParam('token') || '';
  }

  async function validateAndRender(token) {
    try {
      const res = await fetch(Core.routeUrl('public.prestasiSubmit', { token }), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      });
      if (!res.ok) {
        const json = await res.json().catch(() => ({}));
        renderInvalid(json.error || 'Token tidak valid atau sudah digunakan.');
        return;
      }
      const json = await res.json();
      if (json.data?.valid) {
        renderForm(token, json.data.label || '');
      } else {
        renderInvalid('Token tidak valid.');
      }
    } catch (e) {
      renderInvalid('Gagal memvalidasi token. Periksa koneksi internet.');
    }
  }

  function renderInvalid(message) {
    root.innerHTML = `
      <section class="prestasi-submit-hero bg-stone py-16 md:py-24">
        <div class="site-container fade-up text-center">
          <p class="eyebrow text-red-600">Token Tidak Valid</p>
          <h1 class="page-title mt-5">Akses Ditolak</h1>
          <p class="lead mt-7 mx-auto max-w-2xl text-red-700">${escapeHtml(message)}</p>
          <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6 max-w-lg mx-auto">
            <p class="text-sm text-red-800">Token ini mungkin sudah digunakan, kedaluwarsa, atau tidak valid. Hubungi admin GenBI Jambi untuk mendapatkan token baru.</p>
          </div>
        </div>
      </section>
    `;
  }

  function renderForm(token, label) {
    const categories = ['QRIS', 'KTI', 'Essay', 'Inovasi Desa', 'Kreativitas', 'Ekonomi Syariah'];
    const campuses = ['Universitas Jambi', 'UIN Sultan Thaha', 'Alumni'];
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 10 }, (_, i) => currentYear - i);

    root.innerHTML = `
      <section class="prestasi-submit-hero bg-stone py-14 md:py-20">
        <div class="site-container fade-up">
          <div class="mx-auto max-w-2xl text-center">
            <p class="eyebrow">Form Prestasi</p>
            <h1 class="page-title mt-5">Pengajuan Prestasi</h1>
            ${label ? `<p class="mt-4 text-sm text-neutral-600">Token: <strong>${escapeHtml(label)}</strong></p>` : ''}
            <p class="lead mt-5">Isi form di bawah untuk mengajukan prestasi. Form ini hanya bisa digunakan sekali.</p>
          </div>
        </div>
      </section>
      <section class="prestasi-submit-body bg-cream py-12 md:py-16">
        <div class="site-container">
          <form id="prestasi-submit-form" class="mx-auto max-w-2xl">
            <div class="soft-card p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Informasi Prestasi</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Judul Prestasi <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="title" placeholder="Nama prestasi yang diraih" required />
                </label>
                <label class="config-field">
                  <span>Kategori <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="category" list="prestasi-category-options" placeholder="Tulis kategori atau pilih rekomendasi" required />
                  <datalist id="prestasi-category-options">
                    ${categories.map(c => `<option value="${c}"></option>`).join('')}
                  </datalist>
                  <p class="config-hint mt-2">Bisa isi kategori baru jika tidak ada di pilihan rekomendasi.</p>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                  <label class="config-field">
                    <span>Tahun <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="year" required>
                      ${years.map(y => `<option value="${y}">${y}</option>`).join('')}
                    </select>
                  </label>
                  <label class="config-field">
                    <span>Komisariat <span class="text-red-500">*</span></span>
                    <select class="input-soft js-custom-select" name="campus" required>
                      ${campuses.map(c => `<option value="${c}">${c}</option>`).join('')}
                    </select>
                  </label>
                </div>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Informasi Anggota</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Nama Anggota <span class="text-red-500">*</span></span>
                  <input class="input-soft" name="name" placeholder="Nama lengkap penerima prestasi" required />
                </label>
                <label class="config-field">
                  <span>Institusi (opsional)</span>
                  <input class="input-soft" name="institution" placeholder="Nama institusi penyelenggara" />
                </label>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Deskripsi</h2>
              <div class="mt-6 grid gap-4">
                <label class="config-field">
                  <span>Deskripsi Singkat</span>
                  <textarea class="input-soft" name="description" rows="3" placeholder="Ringkasan singkat prestasi (maks 500 karakter)"></textarea>
                </label>
                <label class="config-field">
                  <span>Detail Prestasi</span>
                  <textarea class="input-soft" name="content" rows="6" placeholder="Ceritakan detail prestasi yang diraih..."></textarea>
                </label>
              </div>
            </div>

            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-lg font-bold text-neutral-950">Foto Prestasi</h2>
              <div class="mt-6 grid gap-4">
                <div class="public-upload-field public-prestasi-photo-card prestasi-photo-uploader" data-public-upload-field>
                  <input id="prestasi-photo-input" class="hidden" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple />
                  <div class="public-upload-empty" id="prestasi-photo-empty">
                    <strong>Belum ada foto</strong>
                  </div>
                  <button type="button" class="btn btn-secondary w-full" id="prestasi-photo-trigger">Upload Foto</button>
                  <input class="input-soft" id="prestasi-photo-url" name="image_url" placeholder="URL gambar (opsional)" />
                  <p id="prestasi-photo-gallery-status" class="text-sm text-neutral-500">Belum ada galeri tambahan.</p>
                  <div class="public-upload-list hidden" id="prestasi-photo-list" aria-live="polite"></div>
                    <div class="public-upload-preview hidden" id="prestasi-photo-preview">
                      <div class="public-upload-preview-controls">
                        <strong>Preview foto terpilih</strong>
                        <span id="prestasi-photo-counter">1 / 1</span>
                      </div>
                      <div class="public-upload-preview-slider">
                        <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-left" aria-label="Geser preview ke kiri">‹</button>
                        <div class="public-upload-preview-strip" id="prestasi-photo-preview-strip" aria-label="Preview foto terpilih"></div>
                        <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-right" aria-label="Geser preview ke kanan">›</button>
                      </div>
                    </div>
                    <div class="public-upload-actions">
                      <button type="button" class="chip chip-dark hidden" id="prestasi-photo-clear">Hapus Semua</button>
                    </div>
                </div>
                <p class="text-sm text-neutral-500">Bisa unggah 1 atau lebih foto. Maksimal 6 foto, masing-masing maksimal 5MB. Format: JPG, PNG, WebP, atau GIF.</p>
              </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
              <p class="text-sm text-neutral-500">Form hanya bisa disubmit sekali.</p>
              <button type="submit" class="btn btn-primary">Submit Prestasi</button>
            </div>
          </form>
        </div>
      </section>
    `;

    bindPhotoUploadField();
    if (window.GenBIUI?.enhanceProjectSelects) {
      window.GenBIUI.enhanceProjectSelects(root);
    } else {
      window.GenBIUI?.enhanceNativeSelects?.(root, 'select.js-custom-select', {
        iconHtml: '<span aria-hidden="true">⌄</span>',
        portal: false,
        wrapperClass: 'custom-select custom-select-root',
      });
    }

    // Bind form submission
    document.querySelector('#prestasi-submit-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      const formData = new FormData(form);
      const submitBtn = form.querySelector('[type="submit"]');
      const photoFiles = Array.from(formData.getAll('photos[]')).filter((file) => file instanceof File && file.name);

      const payload = {
        title: String(formData.get('title') || '').trim(),
        category: formData.get('category') || '',
        year: formData.get('year') || '',
        campus: formData.get('campus') || '',
        name: String(formData.get('name') || '').trim(),
        institution: String(formData.get('institution') || '').trim(),
        description: String(formData.get('description') || '').trim(),
        content: String(formData.get('content') || '').trim(),
      };

      // Client-side validation
      if (!payload.title || !payload.category || !payload.year || !payload.campus || !payload.name) {
        showToast('Mohon lengkapi semua field yang wajib diisi.', 'error');
        return;
      }

      if (photoFiles.length > 6) {
        showToast('Maksimal 6 foto dapat diunggah.', 'error');
        return;
      }

      for (const file of photoFiles) {
        if (file.size > 5 * 1024 * 1024) {
          showToast(`Ukuran foto ${file.name} melebihi batas 5MB.`, 'error');
          return;
        }
      }

      formData.set('title', payload.title);
      formData.set('name', payload.name);
      formData.set('institution', payload.institution);
      formData.set('description', payload.description);
      formData.set('content', payload.content);

      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';

      try {
        const csrfToken = API.getCsrfToken ? API.getCsrfToken() : '';
        if (csrfToken) formData.set('_csrf_token', csrfToken);
        const res = await fetch(Core.routeUrl('public.prestasiSubmit', { token }), {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          credentials: 'same-origin',
          body: formData
        });
        const result = await res.json();

        if (res.ok && result.data) {
          renderSuccess();
        } else {
          const details = Array.isArray(result.details) ? result.details.join(', ') : '';
          const diagnostic = [result.detail, result.code, result.message].filter(Boolean).join(' · ');
          showToast((result.error || 'Gagal mengirim prestasi.') + (details ? ' ' + details : '') + (diagnostic ? ' (' + diagnostic + ')' : ''), 'error');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Prestasi';
        }
      } catch (e) {
        showToast('Gagal mengirim. Periksa koneksi internet.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Prestasi';
      }
    });
  }

  function renderSuccess() {
    root.innerHTML = `
      <section class="bg-stone py-16 md:py-24">
        <div class="site-container fade-up text-center">
          <p class="eyebrow text-green-600">Berhasil</p>
          <h1 class="page-title mt-5">Prestasi Terkirim</h1>
          <p class="lead mt-7 mx-auto max-w-2xl">Data prestasi berhasil dikirim dan sedang menunggu review admin. Terima kasih atas kontribusinya!</p>
          <div class="mt-8 rounded-2xl border border-green-200 bg-green-50 p-6 max-w-lg mx-auto">
            <p class="text-sm text-green-800">Token ini sudah tidak bisa digunakan lagi. Jika perlu mengajukan prestasi lain, hubungi admin untuk token baru.</p>
          </div>
        </div>
      </section>
    `;
  }

  function bindPhotoUploadField() {
    const input = document.querySelector('#prestasi-photo-input');
    const trigger = document.querySelector('#prestasi-photo-trigger');
    const clearButton = document.querySelector('#prestasi-photo-clear');
    const list = document.querySelector('#prestasi-photo-list');
    const empty = document.querySelector('#prestasi-photo-empty');
    const preview = document.querySelector('#prestasi-photo-preview');
    const counter = document.querySelector('#prestasi-photo-counter');
    const previewStrip = document.querySelector('#prestasi-photo-preview-strip');
    const scrollLeft = document.querySelector('#prestasi-photo-scroll-left');
    const scrollRight = document.querySelector('#prestasi-photo-scroll-right');
    const status = document.querySelector('#prestasi-photo-gallery-status');
    if (!input || !trigger || !clearButton || !list || !empty || !preview || !counter || !previewStrip || !scrollLeft || !scrollRight || !status) {
      return;
    }

    let previewItems = [];
    let selectedFiles = [];

    const syncInputFiles = () => {
      const dataTransfer = new DataTransfer();
      selectedFiles.forEach((file) => dataTransfer.items.add(file));
      input.files = dataTransfer.files;
    };

    const renderPreview = () => {
      if (!previewItems.length) {
        preview.classList.add('hidden');
        counter.textContent = '0 / 0';
        previewStrip.innerHTML = '';
        return;
      }
      preview.classList.remove('hidden');
      counter.textContent = `${previewItems.length} foto`;
      previewStrip.innerHTML = previewItems.map((item, index) => `
        <article class="public-upload-preview-card">
          <img src="${item.src}" alt="Preview ${escapeHtml(item.name)}" />
          <div>
            <strong>${escapeHtml(item.name)}</strong>
            <span>${formatFileSize(item.size)}</span>
            <button type="button" class="public-upload-remove" data-remove-photo-index="${index}">Hapus foto ini</button>
          </div>
        </article>
      `).join('');
      previewStrip.querySelectorAll('[data-remove-photo-index]').forEach((button) => {
        button.addEventListener('click', () => {
          const index = Number(button.dataset.removePhotoIndex);
          if (!Number.isInteger(index)) return;
          selectedFiles = selectedFiles.filter((_, fileIndex) => fileIndex !== index);
          syncInputFiles();
          renderSelectedFiles();
        });
      });
      const scrollable = previewItems.length > 1;
      scrollLeft.disabled = !scrollable;
      scrollRight.disabled = !scrollable;
    };

    const readFilePreview = (file) => new Promise((resolve) => {
      if (!isPreviewableImage(file)) {
        resolve(null);
        return;
      }
      const reader = new FileReader();
      reader.onload = () => resolve({ src: String(reader.result || ''), name: file.name || 'foto', size: file.size || 0 });
      reader.onerror = () => resolve(null);
      reader.readAsDataURL(file);
    });

    const renderSelectedFiles = async () => {
      const files = selectedFiles;
      if (files.length === 0) {
        empty.classList.remove('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';
        clearButton.classList.add('hidden');
        previewItems = [];
        status.textContent = 'Belum ada galeri tambahan.';
        renderPreview();
        return;
      }

      empty.classList.add('hidden');
      list.classList.add('hidden');
      clearButton.classList.remove('hidden');
      previewItems = (await Promise.all(files.map(readFilePreview))).filter(Boolean);
      status.textContent = `${files.length} foto dipilih. Geser horizontal untuk melihat semua foto.`;
      renderPreview();
      list.innerHTML = '';
    };

    trigger.addEventListener('click', () => input.click());
    scrollLeft.addEventListener('click', () => previewStrip.scrollBy({ left: -220, behavior: 'smooth' }));
    scrollRight.addEventListener('click', () => previewStrip.scrollBy({ left: 220, behavior: 'smooth' }));
    clearButton.addEventListener('click', () => {
      selectedFiles = [];
      input.value = '';
      renderSelectedFiles();
    });
    input.addEventListener('change', () => {
      const incoming = Array.from(input.files || []);
      if (!incoming.length) return;
      selectedFiles = [...selectedFiles, ...incoming].slice(0, 6);
      syncInputFiles();
      renderSelectedFiles();
    });
    renderSelectedFiles();
  }

  function isPreviewableImage(file) {
    if (!(file instanceof File)) return false;
    if (file.type && file.type.startsWith('image/')) return true;
    return /\.(jpe?g|png|webp|gif)$/i.test(file.name || '');
  }

  function formatFileSize(bytes = 0) {
    const size = Number(bytes) || 0;
    if (size >= 1024 * 1024) {
      return `${(size / (1024 * 1024)).toFixed(1)} MB`;
    }
    if (size >= 1024) {
      return `${Math.round(size / 1024)} KB`;
    }
    return `${size} B`;
  }

  function showToast(message, type = 'info') {
    let toast = document.querySelector('#public-mini-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'public-mini-toast';
      toast.className = 'public-mini-toast';
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.toggle('toast-error', type === 'error');
    toast.classList.add('is-visible');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.remove('is-visible'), 3000);
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();
