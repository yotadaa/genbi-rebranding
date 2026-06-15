(function () {
  'use strict';

  const Core = window.GenBIAPICore;
  const API = window.GenBIAPI || {};
  const Admin = window.GenBIAdmin || {};
  const escape = Admin.escapeHtml || ((value = '') => String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;'));

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
    if (!response.ok) throw new Error(json.error || `Request failed: ${response.status}`);
    return json;
  }

  function listPayload(payload) {
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload?.items)) return payload.items;
    return [];
  }

  function normalizeMember(item = {}) {
    return {
      id: Number(item.id || 0),
      name: String(item.name || ''),
      role: String(item.role || item.designation || ''),
      division: String(item.division || ''),
      campus: String(item.campus || item.commission || ''),
      year: String(item.year || item.tahun || ''),
    };
  }

  async function fetchMembers(query) {
    const params = { q: query, limit: 12, type: 'member' };
    const payload = API.getTeamMemberOptions
      ? await API.getTeamMemberOptions(params)
      : await requestJson(Core.buildEndpoint(route('admin.teamMemberOptions'), params));

    return listPayload(payload).map(normalizeMember).filter((member) => member.id && member.name);
  }

  function initForm() {
    const form = document.querySelector('#genbi-point-form');
    if (!form) return;

    const memberInput = form.querySelector('#genbi-point-member-search');
    const teamInput = form.querySelector('#genbi-point-team-id');
    const suggestions = form.querySelector('#genbi-point-member-suggestions');
    const activityInput = form.querySelector('#genbi-point-activity-name');
    const pointInput = form.querySelector('#genbi-point-amount');
    const dateInput = form.querySelector('#genbi-point-date');
    const status = form.querySelector('#genbi-point-form-status');
    let searchTimer = 0;
    let selectedName = String(memberInput?.value || '').trim();

    const setStatus = (message, isError = false) => {
      if (!status) return;
      status.textContent = message;
      status.style.color = isError ? '#b91c1c' : '';
    };

    const renderSuggestions = (items) => {
      if (!suggestions) return;
      if (!items.length) {
        suggestions.innerHTML = '<p>Tidak ada anggota ditemukan.</p>';
        suggestions.classList.remove('hidden');
        return;
      }
      suggestions.innerHTML = items.map((member) => `
        <button type="button" data-member-id="${member.id}" data-member-name="${escape(member.name)}">
          <strong>${escape(member.name)}</strong>
          <span>${escape([member.role, member.division, member.campus, member.year].filter(Boolean).join(' - '))}</span>
        </button>
      `).join('');
      suggestions.classList.remove('hidden');
      suggestions.querySelectorAll('[data-member-id]').forEach((button) => {
        button.addEventListener('click', () => {
          const name = button.dataset.memberName || '';
          if (teamInput) teamInput.value = button.dataset.memberId || '';
          if (memberInput) memberInput.value = name;
          selectedName = name;
          suggestions.classList.add('hidden');
        });
      });
    };

    memberInput?.addEventListener('input', () => {
      const query = String(memberInput.value || '').trim();
      if (query !== selectedName && teamInput) teamInput.value = '';
      window.clearTimeout(searchTimer);
      if (query.length < 2) {
        suggestions?.classList.add('hidden');
        return;
      }
      searchTimer = window.setTimeout(async () => {
        try {
          renderSuggestions(await fetchMembers(query));
        } catch {
          renderSuggestions([]);
        }
      }, 180);
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('[data-genbi-point-member-picker]')) {
        suggestions?.classList.add('hidden');
      }
    });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = {
        team_id: Number(teamInput?.value || 0),
        activity_name: String(activityInput?.value || '').trim(),
        points: Number(pointInput?.value || 0),
        activity_date: String(dateInput?.value || '').trim(),
      };
      if (!payload.team_id || !payload.activity_name || !Number.isInteger(payload.points) || payload.points < 0) {
        setStatus('Pilih anggota dari dropdown, isi kegiatan, dan jumlah poin yang valid.', true);
        return;
      }

      const isEdit = form.dataset.edit === '1';
      const id = Number(form.dataset.itemId || 0);
      const endpoint = isEdit ? route('admin.genbiPoinActivityUpdate', { id }) : route('admin.genbiPoinActivityStore');
      try {
        setStatus('Menyimpan aktivitas...');
        await requestJson(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        Admin.showToast?.('Aktivitas poin tersimpan.');
        window.setTimeout(() => {
          window.location.href = route('admin.genbiPoin');
        }, 500);
      } catch (error) {
        setStatus(error.message || 'Gagal menyimpan aktivitas poin.', true);
        Admin.showToast?.(error.message || 'Gagal menyimpan aktivitas poin.');
      }
    });
  }

  function init() {
    Admin.renderAdminShell?.(document.body.dataset.cmsPage || 'genbi-poin');
    initForm();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
