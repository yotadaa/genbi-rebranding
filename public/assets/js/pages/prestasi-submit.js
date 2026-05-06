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
      <section class="bg-stone py-16 md:py-24">
        <div class="site-container fade-up text-center">
          <p class="eyebrow text-red-600">Token Tidak Valid</p>
          <h1 class="page-title mt-5">Akses Ditolak</h1>
          <p class="lead mt-7 mx-auto max-w-2xl text-red-700">${message}</p>
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
      <section class="bg-stone py-14 md:py-20">
        <div class="site-container fade-up">
          <div class="mx-auto max-w-2xl text-center">
            <p class="eyebrow">Form Prestasi</p>
            <h1 class="page-title mt-5">Pengajuan Prestasi</h1>
            ${label ? `<p class="mt-4 text-sm text-neutral-600">Token: <strong>${escapeHtml(label)}</strong></p>` : ''}
            <p class="lead mt-5">Isi form di bawah untuk mengajukan prestasi. Form ini hanya bisa digunakan sekali.</p>
          </div>
        </div>
      </section>
      <section class="bg-cream py-12 md:py-16">
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
                  <select class="input-soft" name="category" required>
                    <option value="">Pilih kategori</option>
                    ${categories.map(c => `<option value="${c}">${c}</option>`).join('')}
                  </select>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                  <label class="config-field">
                    <span>Tahun <span class="text-red-500">*</span></span>
                    <select class="input-soft" name="year" required>
                      ${years.map(y => `<option value="${y}">${y}</option>`).join('')}
                    </select>
                  </label>
                  <label class="config-field">
                    <span>Komisariat <span class="text-red-500">*</span></span>
                    <select class="input-soft" name="campus" required>
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

            <div class="mt-8 flex items-center justify-between">
              <p class="text-sm text-neutral-500">Form hanya bisa disubmit sekali.</p>
              <button type="submit" class="btn btn-primary">Submit Prestasi</button>
            </div>
          </form>
        </div>
      </section>
    `;

    // Bind form submission
    document.querySelector('#prestasi-submit-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      const formData = new FormData(form);
      const submitBtn = form.querySelector('[type="submit"]');

      const payload = {
        title: formData.get('title')?.trim() || '',
        category: formData.get('category') || '',
        year: formData.get('year') || '',
        campus: formData.get('campus') || '',
        name: formData.get('name')?.trim() || '',
        institution: formData.get('institution')?.trim() || '',
        description: formData.get('description')?.trim() || '',
        content: formData.get('content')?.trim() || '',
      };

      // Client-side validation
      if (!payload.title || !payload.category || !payload.year || !payload.campus || !payload.name) {
        showToast('Mohon lengkapi semua field yang wajib diisi.', 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';

      try {
        const csrfToken = API.getCsrfToken ? API.getCsrfToken() : '';
        const res = await fetch(Core.routeUrl('public.prestasiSubmit', { token }), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });
        const result = await res.json();

        if (res.ok && result.data) {
          renderSuccess();
        } else {
          const details = result.details ? result.details.join(', ') : '';
          showToast(result.error || 'Gagal mengirim prestasi.' + (details ? ' ' + details : ''), 'error');
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
