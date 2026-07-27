(function () {
  'use strict';

  const Core = window.GenBIAPICore;
  const API = window.GenBIAPI || {};

  function escape(value = '') {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function route(name, params = {}) {
    return Core?.routeUrl ? Core.routeUrl(name, params, window.location) : '';
  }

  async function requestJson(path, options = {}) {
    if (API.requestJson) return API.requestJson(path, options);
    const headers = { Accept: 'application/json', ...(options.headers || {}) };
    const method = (options.method || 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD') {
      const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
      if (token) headers['X-CSRF-TOKEN'] = token;
    }
    const response = await fetch(path, { ...options, headers, credentials: 'same-origin' });
    const json = await response.json().catch(() => ({}));
    if (!response.ok) {
      const details = Array.isArray(json.details) ? json.details.join(' ') : '';
      throw new Error(details || json.error || `Request failed: ${response.status}`);
    }
    return json;
  }

  function normalizeMember(member = {}) {
    return {
      id: Number(member.id || member.team_id || 0),
      name: String(member.name || member.member_name || ''),
      role: String(member.role || member.designation || ''),
      campus: String(member.campus || member.commission || ''),
      year: String(member.year || member.tahun || ''),
    };
  }

  function initPublicPresensi() {
    document.body.classList.add('page-ready');

    const form = document.querySelector('#public-presensi-form');
    if (!form) return;
    window.GenBIUI?.enhanceProjectSelects?.(form);

    const token = form.dataset.token || '';
    const nameInput = form.querySelector('#public-presensi-name');
    const teamIdInput = form.querySelector('#public-presensi-team-id');
    const suggestions = form.querySelector('#public-presensi-suggestions');
    const status = form.querySelector('#public-presensi-status');
    const submitButton = form.querySelector('[data-presensi-submit]') || form.querySelector('button[type="submit"]');
    const submitIdleHtml = submitButton?.innerHTML || 'Submit Presensi';
    let selectedName = '';
    let searchTimer = 0;
    let isSubmitting = false;

    const setStatus = (message, isError = false) => {
      if (!status) return;
      status.textContent = message;
      status.classList.toggle('text-red-700', isError);
      status.classList.toggle('text-green-700', !isError && message !== '');
    };

    const clearSelection = () => {
      if (teamIdInput) teamIdInput.value = '';
      selectedName = '';
    };

    const setSubmitting = (nextState) => {
      isSubmitting = nextState;
      form.classList.toggle('is-submitting', nextState);
      form.setAttribute('aria-busy', nextState ? 'true' : 'false');
      if (!submitButton) return;
      submitButton.disabled = nextState;
      submitButton.innerHTML = nextState
        ? '<span class="presensi-submit-spinner" aria-hidden="true"></span><span>Mengirim Presensi...</span>'
        : submitIdleHtml;
    };

    const selectMember = (member) => {
      const normalized = normalizeMember(member);
      if (!normalized.id) return;
      if (teamIdInput) teamIdInput.value = String(normalized.id);
      if (nameInput) nameInput.value = normalized.name;
      selectedName = normalized.name;
      suggestions?.classList.add('hidden');
    };

    const searchMembers = () => {
      window.clearTimeout(searchTimer);
      clearSelection();
      const query = String(nameInput?.value || '').trim();
      if (!suggestions || query.length < 2) {
        suggestions?.classList.add('hidden');
        return;
      }
      searchTimer = window.setTimeout(async () => {
        try {
          const json = await requestJson(Core.buildEndpoint(route('public.presensiMembers', { token }), { q: query }));
          const items = Array.isArray(json.data) ? json.data : [];
          suggestions.innerHTML = items.length
            ? items.map((item) => {
              const member = normalizeMember(item);
              return `<button type="button" data-member='${escape(JSON.stringify(member))}'><strong>${escape(member.name)}</strong><span>${escape([member.role, member.campus, member.year].filter(Boolean).join(' - '))}</span></button>`;
            }).join('')
            : '<p>Nama tidak ada dalam daftar event.</p>';
          suggestions.classList.remove('hidden');
          suggestions.querySelectorAll('[data-member]').forEach((button) => {
            button.addEventListener('click', () => {
              try {
                selectMember(JSON.parse(button.dataset.member || '{}'));
              } catch {
                clearSelection();
              }
            });
          });
        } catch {
          suggestions.innerHTML = '<p>Gagal memuat daftar anggota.</p>';
          suggestions.classList.remove('hidden');
        }
      }, 220);
    };

    nameInput?.addEventListener('input', searchMembers);
    nameInput?.addEventListener('blur', () => {
      if (selectedName && nameInput.value !== selectedName) clearSelection();
    });
    document.addEventListener('click', (event) => {
      if (!event.target.closest('[data-public-member-picker]')) suggestions?.classList.add('hidden');
    });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (isSubmitting) return;
      setStatus('');
      const teamId = Number(teamIdInput?.value || 0);
      const role = form.querySelector('#public-presensi-role')?.value || '';
      const photo = form.querySelector('#public-presensi-photo')?.files?.[0] || null;
      if (!teamId) {
        setStatus('Nama wajib dipilih dari dropdown.', true);
        return;
      }
      if (!role) {
        setStatus('Role wajib dipilih.', true);
        return;
      }
      if (!photo) {
        setStatus('Bukti foto wajib diunggah.', true);
        return;
      }

      const formData = new FormData();
      formData.set('team_id', String(teamId));
      formData.set('role', role);
      formData.set('photo', photo);

      try {
        setSubmitting(true);
        await (API.submitPresensi ? API.submitPresensi(token, formData) : requestJson(route('public.presensiShow', { token }), { method: 'POST', body: formData }));
        setStatus('Presensi berhasil dikirim dan menunggu approval admin.');
        form.querySelectorAll('input, select, button').forEach((node) => {
          node.disabled = true;
        });
        if (submitButton) submitButton.innerHTML = '<span>Terkirim</span>';
      } catch (error) {
        setSubmitting(false);
        setStatus(error.message || 'Gagal mengirim presensi.', true);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicPresensi);
  } else {
    initPublicPresensi();
  }
})();
