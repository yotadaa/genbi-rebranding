(function () {
  'use strict';

  const Core = window.GenBIAPICore;
  const API = window.GenBIAPI || {};
  const Admin = window.GenBIAdmin || {};
  const App = window.GenBIApp || {};
  const escape = Admin.escapeHtml || ((value = '') => String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;'));
  const adminUrl = App.adminUrl || ((page) => `/admin/${page}`);
  const xMarkIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';
  const usersIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>';
  const checkIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';
  const pencilIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65m-1.12-4.02a1.88 1.88 0 0 1 2.49 2.49L7.5 19l-4.5 1.5L4.5 16 17.27 3.23c.34-.34.75-.52 1.12-.11Z"/></svg>';

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

  function absoluteUrl(value = '') {
    try {
      return new URL(String(value || ''), window.location.origin).href;
    } catch {
      return String(value || '');
    }
  }

  async function copyText(value) {
    const text = absoluteUrl(value);
    if (!text) return;
    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(text);
    } else {
      const input = document.createElement('input');
      input.value = text;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      input.remove();
    }
    Admin.showToast?.('Link presensi disalin.');
  }

  function renderQr(target, text, size = 220) {
    if (!target || !text) return;
    target.innerHTML = '';
    if (window.QrCreator?.render) {
      window.QrCreator.render({
        text: absoluteUrl(text),
        radius: 0.08,
        ecLevel: 'M',
        fill: '#114b9a',
        background: '#ffffff',
        size,
      }, target);
      return;
    }
    target.textContent = absoluteUrl(text);
  }

  function openHtmlModal(title, html, afterOpen) {
    const root = document.querySelector('#admin-modal-root') || document.body;
    const modal = document.createElement('div');
    modal.className = 'category-editor-modal hidden';
    root.appendChild(modal);
    const content = `
      <div class="category-editor-backdrop" data-modal-close></div>
      <section class="category-editor-panel presensi-modal-panel" role="dialog" aria-modal="true" aria-labelledby="presensi-modal-title">
        <header class="category-editor-head">
          <div>
            <p class="eyebrow">Presensi</p>
            <h2 id="presensi-modal-title">${escape(title)}</h2>
          </div>
          <button type="button" class="category-editor-close" data-modal-close aria-label="Tutup modal">${xMarkIcon}</button>
        </header>
        <div class="presensi-modal-content">${html}</div>
      </section>
    `;
    const controller = window.GenBIUI?.createModalController?.(modal, {
      closeSelector: '[data-modal-close]',
      panelSelector: '[role="dialog"]',
      onClose: () => modal.remove(),
    });
    if (controller) {
      controller.open({ content });
    } else {
      modal.innerHTML = content;
      modal.classList.remove('hidden');
      document.body.classList.add('modal-lock');
      modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-modal-close]')) {
          modal.remove();
          document.body.classList.remove('modal-lock');
        }
      });
    }
    window.setTimeout(() => afterOpen?.(modal), 30);
  }

  function showQrModal(link, title = 'QR Presensi') {
    openHtmlModal(title, `
      <div class="presensi-qr-modal">
        <div class="presensi-qr-box" data-modal-qr></div>
        <p>${escape(absoluteUrl(link))}</p>
        <button type="button" class="btn btn-secondary" data-copy-modal-link="${escape(link)}">Copy Link</button>
      </div>
    `, (modal) => {
      renderQr(modal.querySelector('[data-modal-qr]'), link, 240);
      modal.querySelector('[data-copy-modal-link]')?.addEventListener('click', () => copyText(link));
    });
  }

  function showPhotoModal(photo = {}) {
    const url = String(photo.url || photo.photo_url || '');
    if (!url) return;
    const name = String(photo.name || photo.member_name || '');
    openHtmlModal('Bukti Foto', `
      <figure class="presensi-photo-modal">
        <img class="presensi-proof-photo" src="${escape(url)}" alt="Bukti presensi ${escape(name)}">
        ${name ? `<figcaption>${escape(name)}</figcaption>` : ''}
      </figure>
    `);
  }

  function showManualApproveModal(item = {}) {
    const eventId = Number(item.event_id || item.presensi_event_id || 0);
    const teamId = Number(item.team_id || 0);
    const memberName = String(item.member_name || item.name || '');
    const roles = Array.isArray(item.roles) ? item.roles.map((role) => String(role || '').trim()).filter(Boolean) : [];
    if (!eventId || !teamId || !roles.length) {
      Admin.showToast?.('Data approve manual tidak lengkap.');
      return;
    }

    openHtmlModal('Approve Manual', `
      <form class="category-editor-form" data-presensi-manual-approve-form>
        <p class="config-hint">Buat presensi approved untuk ${escape(memberName)} tanpa menunggu form publik.</p>
        <label class="config-field">
          <span>Role</span>
          <select class="config-input js-admin-custom-select" data-presensi-manual-role required>
            <option value="">Pilih role</option>
            ${roles.map((role) => `<option value="${escape(role)}">${escape(role)}</option>`).join('')}
          </select>
        </label>
        <p class="config-hint" data-presensi-manual-status role="status"></p>
        <div class="category-editor-actions">
          <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
          <button type="submit" class="btn btn-primary">${checkIcon} Approve</button>
        </div>
      </form>
    `, (modal) => {
      window.GenBIUI?.enhanceProjectSelects?.(modal);
      const form = modal.querySelector('[data-presensi-manual-approve-form]');
      const roleInput = modal.querySelector('[data-presensi-manual-role]');
      const status = modal.querySelector('[data-presensi-manual-status]');
      form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const role = String(roleInput?.value || '').trim();
        if (!role) {
          if (status) status.textContent = 'Pilih role terlebih dahulu.';
          return;
        }
        try {
          if (status) status.textContent = 'Memproses approve manual...';
          await requestJson(route('admin.presensiMemberApprove', { eventId, teamId }), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role }),
          });
          Admin.showToast?.('Presensi manual disetujui.');
          window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
          if (status) status.textContent = error.message || 'Gagal approve manual.';
          Admin.showToast?.(error.message || 'Gagal approve manual.');
        }
      });
    });
  }

  function bindListActions() {
    document.querySelectorAll('[data-copy-link]').forEach((button) => {
      button.addEventListener('click', () => copyText(button.dataset.copyLink || ''));
    });
    document.querySelectorAll('[data-show-qr]').forEach((button) => {
      button.addEventListener('click', () => showQrModal(button.dataset.showQr || '', button.dataset.qrTitle || 'QR Presensi'));
    });
    document.querySelectorAll('[data-delete-presensi]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.deletePresensi || 0);
        if (!id) return;
        const ok = await Admin.showConfirm?.({
          title: 'Hapus event presensi?',
          message: 'Event akan dihapus secara soft delete. Data tidak akan tampil di admin maupun link publik.',
          confirmText: 'Hapus',
          danger: true,
        });
        if (!ok) return;
        await requestJson(route('admin.presensiDelete', { id }), { method: 'POST' });
        Admin.showToast?.('Event presensi dihapus.');
        window.setTimeout(() => window.location.reload(), 500);
      });
    });
  }

  function parseJsonInput(selector, fallback = []) {
    try {
      const value = document.querySelector(selector)?.value || '';
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : fallback;
    } catch {
      return fallback;
    }
  }

  function normalizeMember(member = {}) {
    return {
      id: Number(member.id || member.team_id || 0),
      name: String(member.name || member.member_name || ''),
      role: String(member.role || member.designation || member.member_role || ''),
      divisionId: Number(member.division_id || member.divisi_id || 0),
      division: String(member.division || ''),
      campus: String(member.campus || member.commission || ''),
      year: String(member.year || member.tahun || ''),
      photo: String(member.photo || ''),
    };
  }

  function isActiveMember(member = {}) {
    const text = `${member.campus || ''} ${member.commission || ''} ${member.komsat || ''}`.toLowerCase();
    return !text.includes('alumni');
  }

  function normalizeRoleOption(role = {}) {
    if (typeof role === 'string') {
      return { name: role.trim().slice(0, 120), score: 0 };
    }
    const name = String(role.name || role.label || role.role || '').trim().slice(0, 120);
    const score = Math.max(0, Math.min(100000, Number.parseInt(role.score ?? role.points ?? role.skor ?? 0, 10) || 0));
    return { name, score };
  }

  function roleKey(role) {
    return String(role?.name || '').trim().toLowerCase();
  }

  function initForm() {
    const form = document.querySelector('#presensi-editor-form');
    if (!form) return;

    let roles = parseJsonInput('#presensi-roles-json').map(normalizeRoleOption).filter((role) => role.name);
    let members = parseJsonInput('#presensi-members-json').map(normalizeMember).filter((member) => member.id > 0);
    let editingRoleIndex = -1;
    const roleInput = form.querySelector('#presensi-role-input');
    const roleScoreInput = form.querySelector('#presensi-role-score');
    const roleList = form.querySelector('#presensi-role-list');
    const memberInput = form.querySelector('#presensi-member-search');
    const memberPickerOpen = form.querySelector('#presensi-member-modal-open');
    const suggestions = form.querySelector('#presensi-member-suggestions');
    const memberList = form.querySelector('#presensi-member-list');
    const status = form.querySelector('#presensi-form-status');

    const setStatus = (message, isError = false) => {
      if (!status) return;
      status.textContent = message;
      status.classList.toggle('text-red-700', isError);
      status.classList.toggle('text-green-700', !isError && message !== '');
    };

    const syncHidden = () => {
      const rolesInput = form.querySelector('#presensi-roles-json');
      const membersInput = form.querySelector('#presensi-members-json');
      if (rolesInput) rolesInput.value = JSON.stringify(roles);
      if (membersInput) membersInput.value = JSON.stringify(members);
    };

    const renderRoles = () => {
      if (!roleList) return;
      roleList.innerHTML = roles.length
        ? `
          <div class="admin-data-table-wrap presensi-role-table-wrap">
            <table class="admin-table admin-data-table presensi-role-table">
              <thead>
                <tr>
                  <th>Role</th>
                  <th>Skor</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                ${roles.map((role, index) => `
                  <tr>
                    <td>
                      <input class="config-input presensi-role-edit-input" data-role-name="${index}" value="${escape(role.name)}" maxlength="120" ${editingRoleIndex === index ? '' : 'disabled'}>
                    </td>
                    <td>
                      <input class="config-input presensi-role-score-edit-input" data-role-score="${index}" type="number" min="0" max="100000" step="1" value="${Number(role.score || 0)}" ${editingRoleIndex === index ? '' : 'disabled'}>
                    </td>
                    <td class="text-right">
                      ${editingRoleIndex === index
                        ? `<button type="button" class="btn btn-primary btn-sm presensi-icon-btn" data-save-role="${index}" aria-label="Simpan role ${escape(role.name)}">${checkIcon}</button>`
                        : `<button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-edit-role="${index}" aria-label="Edit role ${escape(role.name)}">${pencilIcon}</button>`}
                      <button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-remove-role="${index}" aria-label="Hapus role ${escape(role.name)}">
                        ${xMarkIcon}
                      </button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `
        : '<p class="config-hint">Tambahkan minimal satu role untuk event ini.</p>';
      roleList.querySelectorAll('[data-edit-role]').forEach((button) => {
        button.addEventListener('click', () => {
          editingRoleIndex = Number(button.dataset.editRole || -1);
          renderRoles();
          roleList.querySelector(`[data-role-name="${editingRoleIndex}"]`)?.focus();
        });
      });
      const commitRole = (index) => {
        const nameInput = roleList.querySelector(`[data-role-name="${index}"]`);
        const scoreInput = roleList.querySelector(`[data-role-score="${index}"]`);
        const name = String(nameInput?.value || '').trim().slice(0, 120);
        const score = Math.max(0, Math.min(100000, Number.parseInt(scoreInput?.value || '0', 10) || 0));
        if (!name) {
          setStatus('Nama role tidak boleh kosong.', true);
          nameInput?.focus();
          return;
        }
        if (roles.some((role, roleIndex) => roleIndex !== index && roleKey(role) === name.toLowerCase())) {
          setStatus('Nama role sudah ada.', true);
          nameInput?.focus();
          return;
        }
        roles[index] = { name, score };
        editingRoleIndex = -1;
        syncHidden();
        setStatus('');
        renderRoles();
      };
      roleList.querySelectorAll('[data-save-role]').forEach((button) => {
        button.addEventListener('click', () => {
          commitRole(Number(button.dataset.saveRole || -1));
        });
      });
      roleList.querySelectorAll('[data-role-name], [data-role-score]').forEach((input) => {
        input.addEventListener('keydown', (event) => {
          if (event.key !== 'Enter') return;
          event.preventDefault();
          const index = Number(input.dataset.roleName ?? input.dataset.roleScore ?? -1);
          commitRole(index);
        });
      });
      roleList.querySelectorAll('[data-remove-role]').forEach((button) => {
        button.addEventListener('click', () => {
          const index = Number(button.dataset.removeRole || -1);
          roles = roles.filter((role, roleIndex) => roleIndex !== index);
          if (editingRoleIndex === index) editingRoleIndex = -1;
          if (editingRoleIndex > index) editingRoleIndex -= 1;
          syncHidden();
          renderRoles();
        });
      });
    };

    const renderMembers = () => {
      if (!memberList) return;
      memberList.innerHTML = members.length
        ? `
          <div class="admin-data-table-wrap presensi-member-table-wrap">
            <table class="admin-table admin-data-table presensi-member-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Divisi</th>
                  <th>Kampus</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                ${members.map((member) => `
                  <tr>
                    <td><strong>${escape(member.name)}</strong></td>
                    <td>${escape(member.division || '-')}</td>
                    <td>${escape(member.campus || '-')}</td>
                    <td class="text-right">
                      <button type="button" class="btn btn-outline btn-sm presensi-icon-btn" data-remove-member="${member.id}" aria-label="Hapus ${escape(member.name)}">
                        ${xMarkIcon}
                      </button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `
        : '<p class="config-hint">Pilih anggota dari dropdown. Input manual tidak disimpan.</p>';
      memberList.querySelectorAll('[data-remove-member]').forEach((button) => {
        button.addEventListener('click', () => {
          const id = Number(button.dataset.removeMember || 0);
          members = members.filter((member) => member.id !== id);
          syncHidden();
          renderMembers();
        });
      });
    };

    const addRole = () => {
      const value = String(roleInput?.value || '').trim().slice(0, 120);
      const score = Math.max(0, Math.min(100000, Number.parseInt(roleScoreInput?.value || '0', 10) || 0));
      if (!value) return;
      if (!roles.some((role) => roleKey(role) === value.toLowerCase())) {
        roles.push({ name: value, score });
      }
      roleInput.value = '';
      if (roleScoreInput) roleScoreInput.value = '';
      syncHidden();
      renderRoles();
    };

    const addMember = (member) => {
      const normalized = normalizeMember(member);
      if (!normalized.id || members.some((item) => item.id === normalized.id)) return;
      members.push(normalized);
      if (memberInput) memberInput.value = '';
      suggestions?.classList.add('hidden');
      syncHidden();
      renderMembers();
    };

    let searchTimer = 0;
    const searchMembers = () => {
      window.clearTimeout(searchTimer);
      const query = String(memberInput?.value || '').trim();
      if (!suggestions || query.length < 2) {
        suggestions?.classList.add('hidden');
        return;
      }
      searchTimer = window.setTimeout(async () => {
        try {
          const json = API.getTeamMemberOptions
            ? await API.getTeamMemberOptions({ q: query, limit: 12, active_only: 1 })
            : await requestJson(Core.buildEndpoint(route('admin.teamMemberOptions'), { q: query, limit: 12, type: 'member', active_only: 1 }));
          const items = Array.isArray(json.data) ? json.data : [];
          suggestions.innerHTML = items.length
            ? items.map((item) => {
              const member = normalizeMember(item);
              return `<button type="button" data-member='${escape(JSON.stringify(member))}'><strong>${escape(member.name)}</strong><span>${escape([member.role, member.division, member.campus, member.year].filter(Boolean).join(' - '))}</span></button>`;
            }).join('')
            : '<p>Tidak ada anggota ditemukan.</p>';
          suggestions.classList.remove('hidden');
          suggestions.querySelectorAll('[data-member]').forEach((button) => {
            button.addEventListener('click', () => {
              try {
                addMember(JSON.parse(button.dataset.member || '{}'));
              } catch {
                // Ignore malformed suggestion data.
              }
            });
          });
        } catch (error) {
          suggestions.innerHTML = '<p>Gagal memuat anggota.</p>';
          suggestions.classList.remove('hidden');
        }
      }, 220);
    };

    const fetchMemberOptions = async (params = {}) => {
      const json = API.getTeamMemberOptions
        ? await API.getTeamMemberOptions({ limit: 500, active_only: 1, ...params })
        : await requestJson(Core.buildEndpoint(route('admin.teamMemberOptions'), { limit: 500, type: 'member', active_only: 1, ...params }));
      return Array.isArray(json.data) ? json.data.map(normalizeMember).filter((member) => member.id > 0) : [];
    };

    const paginationPages = (totalPages, currentPage) => {
      if (totalPages <= 7) return Array.from({ length: totalPages }, (_, index) => index + 1);
      const pages = new Set([1, totalPages, currentPage, currentPage - 1, currentPage + 1]);
      if (currentPage <= 3) {
        pages.add(2);
        pages.add(3);
        pages.add(4);
      }
      if (currentPage >= totalPages - 2) {
        pages.add(totalPages - 1);
        pages.add(totalPages - 2);
        pages.add(totalPages - 3);
      }
      return Array.from(pages)
        .filter((page) => page >= 1 && page <= totalPages)
        .sort((a, b) => a - b)
        .reduce((entries, page, index, list) => {
          if (index > 0 && page - list[index - 1] > 1) entries.push('gap');
          entries.push(page);
          return entries;
        }, []);
    };

    const loadMemberPickerOptions = async () => {
      try {
        const json = await requestJson(route('admin.teamMemberOptions'));
        return Array.isArray(json.data?.divisions) ? json.data.divisions : [];
      } catch {
        return [];
      }
    };

    const openMemberPicker = async () => {
      const divisions = await loadMemberPickerOptions();
      const divisionOptions = divisions.map((division) => `<option value="${Number(division.id || 0)}">${escape(division.nama || division.name || '')}</option>`).join('');
      openHtmlModal('Pilih Anggota Event', `
        <div class="presensi-member-picker">
          <div class="presensi-picker-toolbar">
            <label class="config-field">
              <span>Filter Divisi</span>
              <select class="config-input js-admin-custom-select" data-member-picker-division>
                <option value="">Semua Divisi</option>
                ${divisionOptions}
              </select>
            </label>
            <label class="config-field">
              <span>Search Nama</span>
              <input class="config-input" data-member-picker-search placeholder="Cari nama anggota..." autocomplete="off">
            </label>
            <label class="presensi-check-all">
              <input type="checkbox" data-member-picker-check-all>
              <span>Check all hasil filter</span>
            </label>
          </div>
          <div class="presensi-picker-list" data-member-picker-list>
            <p class="config-hint">Memuat anggota...</p>
          </div>
          <div class="presensi-picker-pagination" data-member-picker-pagination></div>
          <div class="presensi-picker-floating">
            <span data-member-picker-count>0 anggota dipilih</span>
            <button type="button" class="btn btn-primary" data-member-picker-apply>${checkIcon} Terapkan</button>
          </div>
        </div>
      `, (modal) => {
        modal.classList.add('presensi-member-picker-modal');
        const divisionInput = modal.querySelector('[data-member-picker-division]');
        const searchInput = modal.querySelector('[data-member-picker-search]');
        const checkAllInput = modal.querySelector('[data-member-picker-check-all]');
        const list = modal.querySelector('[data-member-picker-list]');
        const pagination = modal.querySelector('[data-member-picker-pagination]');
        const count = modal.querySelector('[data-member-picker-count]');
        const applyButton = modal.querySelector('[data-member-picker-apply]');
        const checked = new Map(members.filter(isActiveMember).map((member) => [member.id, member]));
        let modalItems = [];
        let modalTimer = 0;
        let modalPage = 1;
        const perPage = 10;

        window.GenBIUI?.enhanceProjectSelects?.(modal);

        const updateCount = () => {
          if (count) count.textContent = `${checked.size} anggota dipilih`;
        };

        const renderModalItems = () => {
          if (!list) return;
          const totalPages = Math.max(1, Math.ceil(modalItems.length / perPage));
          modalPage = Math.max(1, Math.min(modalPage, totalPages));
          const start = (modalPage - 1) * perPage;
          const pageItems = modalItems.slice(start, start + perPage);
          list.innerHTML = pageItems.length
            ? pageItems.map((member) => `
              <label class="presensi-picker-row">
                <input type="checkbox" data-member-picker-check="${member.id}" ${checked.has(member.id) ? 'checked' : ''}>
                <span>
                  <strong>${escape(member.name)}</strong>
                  <small>${escape([member.role, member.division, member.campus, member.year].filter(Boolean).join(' - '))}</small>
                </span>
              </label>
            `).join('')
            : '<p class="config-hint">Tidak ada anggota sesuai filter.</p>';
          if (pagination) {
            pagination.innerHTML = modalItems.length
              ? `
                <span class="presensi-picker-page-info">Menampilkan ${start + 1}-${Math.min(start + pageItems.length, modalItems.length)} dari ${modalItems.length} anggota</span>
                <div class="admin-pagination">
                  <button class="pager-button" type="button" data-member-page="${Math.max(1, modalPage - 1)}" ${modalPage === 1 ? 'disabled' : ''}>Sebelumnya</button>
                  ${paginationPages(totalPages, modalPage).map((page) => page === 'gap'
                    ? '<span class="pager-button presensi-page-gap" aria-hidden="true">...</span>'
                    : `<button class="pager-button ${page === modalPage ? 'is-active' : ''}" type="button" data-member-page="${page}">${page}</button>`).join('')}
                  <button class="pager-button" type="button" data-member-page="${Math.min(totalPages, modalPage + 1)}" ${modalPage === totalPages ? 'disabled' : ''}>Berikutnya</button>
                </div>
              `
              : '';
            pagination.querySelectorAll('[data-member-page]').forEach((button) => {
              button.addEventListener('click', () => {
                modalPage = Number(button.dataset.memberPage || 1);
                renderModalItems();
              });
            });
          }
          list.querySelectorAll('[data-member-picker-check]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
              const id = Number(checkbox.dataset.memberPickerCheck || 0);
              const item = modalItems.find((member) => member.id === id);
              if (!item) return;
              if (checkbox.checked) checked.set(id, item);
              else checked.delete(id);
              if (checkAllInput) checkAllInput.checked = modalItems.length > 0 && modalItems.every((member) => checked.has(member.id));
              updateCount();
            });
          });
          if (checkAllInput) checkAllInput.checked = modalItems.length > 0 && modalItems.every((member) => checked.has(member.id));
          updateCount();
        };

        const loadItems = () => {
          window.clearTimeout(modalTimer);
          modalTimer = window.setTimeout(async () => {
            if (list) list.innerHTML = '<p class="config-hint">Memuat anggota...</p>';
            const params = {
              q: String(searchInput?.value || '').trim(),
              division_id: String(divisionInput?.value || ''),
            };
            modalItems = await fetchMemberOptions(params);
            modalPage = 1;
            modalItems.forEach((member) => {
              if (checked.has(member.id)) checked.set(member.id, { ...checked.get(member.id), ...member });
            });
            renderModalItems();
          }, 180);
        };

        divisionInput?.addEventListener('change', loadItems);
        searchInput?.addEventListener('input', loadItems);
        checkAllInput?.addEventListener('change', () => {
          modalItems.forEach((member) => {
            if (checkAllInput.checked) checked.set(member.id, member);
            else checked.delete(member.id);
          });
          renderModalItems();
        });
        applyButton?.addEventListener('click', () => {
          members = Array.from(checked.values()).sort((a, b) => a.name.localeCompare(b.name));
          syncHidden();
          renderMembers();
          modal.querySelector('[data-modal-close]')?.click();
        });
        updateCount();
        loadItems();
      });
    };

    form.querySelector('#presensi-role-add')?.addEventListener('click', addRole);
    roleInput?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        addRole();
      }
    });
    roleScoreInput?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        addRole();
      }
    });
    memberInput?.addEventListener('input', searchMembers);
    memberPickerOpen?.addEventListener('click', () => {
      openMemberPicker().catch(() => Admin.showToast?.('Gagal membuka daftar anggota.'));
    });
    document.addEventListener('click', (event) => {
      if (!event.target.closest('[data-presensi-member-picker]')) suggestions?.classList.add('hidden');
    });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      setStatus('');
      const payload = {
        event_name: form.querySelector('#presensi-event-name')?.value?.trim() || '',
        location: form.querySelector('#presensi-location')?.value?.trim() || '',
        status: form.querySelector('#presensi-status')?.value || 'open',
        roles,
        member_ids: members.map((member) => member.id),
      };
      if (!payload.event_name || !payload.location || !roles.length || !members.length) {
        setStatus('Lengkapi nama event, lokasi, minimal satu role, dan minimal satu anggota.', true);
        return;
      }

      const isEdit = form.dataset.edit === '1';
      const id = Number(form.dataset.itemId || 0);
      const endpoint = isEdit ? route('admin.presensiUpdate', { id }) : route('admin.presensiStore');
      try {
        const json = await requestJson(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const nextId = Number(json.data?.id || json.data?.event?.id || id);
        setStatus('Presensi tersimpan.');
        Admin.showToast?.('Presensi tersimpan.');
        if (nextId) {
          window.setTimeout(() => {
            window.location.href = route('admin.presensiDetail', { id: nextId }) || `${adminUrl('presensi-detail')}?id=${nextId}`;
          }, 500);
        }
      } catch (error) {
        setStatus(error.message || 'Gagal menyimpan presensi.', true);
      }
    });

    renderRoles();
    renderMembers();
    syncHidden();
  }

  function bindDetailActions() {
    document.querySelectorAll('[data-presensi-qr]').forEach((node) => renderQr(node, node.dataset.presensiQr || '', 220));
    document.querySelectorAll('[data-presensi-photo]').forEach((button) => {
      button.addEventListener('click', () => {
        try {
          showPhotoModal(JSON.parse(button.dataset.presensiPhoto || '{}'));
        } catch {
          showPhotoModal({ url: button.dataset.presensiPhoto || '' });
        }
      });
    });
    document.querySelectorAll('[data-presensi-detail]').forEach((button) => {
      button.addEventListener('click', () => {
        let item = {};
        try {
          item = JSON.parse(button.dataset.presensiDetail || '{}');
        } catch {
          item = {};
        }
        openHtmlModal('Detail Presensi', `
          <div class="presensi-detail-grid">
            <div>
              <p class="eyebrow">Nama</p>
              <strong>${escape(item.member_name || '')}</strong>
            </div>
            <div>
              <p class="eyebrow">Role</p>
              <strong>${escape(item.role || '')}</strong>
            </div>
            <div>
              <p class="eyebrow">Skor</p>
              <strong>${Number(item.role_score || 0)} poin</strong>
            </div>
            <div>
              <p class="eyebrow">Status</p>
              <strong>${escape(item.status || 'pending')}</strong>
            </div>
            <div>
              <p class="eyebrow">Waktu</p>
              <strong>${escape(item.created_at_label || item.created_at || '')}</strong>
            </div>
          </div>
          ${item.photo_url ? `<button type="button" class="btn btn-secondary mt-5" data-modal-presensi-photo>Lihat Foto</button>` : ''}
        `, (modal) => {
          modal.querySelector('[data-modal-presensi-photo]')?.addEventListener('click', () => {
            showPhotoModal({ url: item.photo_url || '', name: item.member_name || '' });
          });
        });
      });
    });
    document.querySelectorAll('[data-presensi-manual-approve]').forEach((button) => {
      button.addEventListener('click', () => {
        try {
          showManualApproveModal(JSON.parse(button.dataset.presensiManualApprove || '{}'));
        } catch {
          Admin.showToast?.('Data approve manual tidak valid.');
        }
      });
    });
    document.querySelectorAll('[data-approve-presensi]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.approvePresensi || 0);
        if (!id) return;
        const ok = await Admin.showConfirm?.({
          title: 'Approve presensi?',
          message: 'Status kehadiran akan diubah menjadi approved.',
          confirmText: 'Approve',
        });
        if (!ok) return;
        await requestJson(route('admin.presensiApprove', { id }), { method: 'POST' });
        Admin.showToast?.('Presensi disetujui.');
        window.setTimeout(() => window.location.reload(), 500);
      });
    });
    document.querySelectorAll('[data-cancel-presensi]').forEach((button) => {
      button.addEventListener('click', async () => {
        const id = Number(button.dataset.cancelPresensi || 0);
        if (!id) return;
        const ok = await Admin.showConfirm?.({
          title: 'Batalkan presensi?',
          message: 'Data kehadiran anggota ini akan dihapus dari event sehingga bisa presensi ulang atau di-approve lagi.',
          confirmText: 'Batalkan',
          danger: true,
        });
        if (!ok) return;
        await requestJson(route('admin.presensiCancel', { id }), { method: 'POST' });
        Admin.showToast?.('Presensi dibatalkan.');
        window.setTimeout(() => window.location.reload(), 500);
      });
    });
  }

  function init() {
    Admin.renderAdminShell?.('presensi');
    bindListActions();
    initForm();
    bindDetailActions();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
