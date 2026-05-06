(function () {
  'use strict';

  const { adminStats, adminActivity, news } = window.GenBIData;
  const { renderAdminShell, icon } = window.GenBIAdmin;

  renderAdminShell('dashboard');
  renderStats();
  renderActivity();
  renderHealth();

  function renderStats() {
    const root = document.querySelector('#admin-stats');
    if (!root) return;
    root.innerHTML = adminStats.map((item, index) => `
      <article class="admin-card slide-in p-5" style="animation-delay:${index * 45}ms">
        <div class="flex items-start justify-between gap-4">
          <div class="admin-stat-icon">${icon(item.icon)}</div>
          <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-neutral-500 ring-1 ring-neutral-900/10">CMS</span>
        </div>
        <p class="mt-6 text-sm font-bold uppercase tracking-[0.12em] text-neutral-500">${item.label}</p>
        <div class="mt-2 flex items-end justify-between gap-4">
          <p class="serif text-5xl font-semibold tracking-tight text-neutral-950">${item.value}</p>
          <p class="max-w-[8rem] text-right text-xs leading-5 text-neutral-500">${item.note}</p>
        </div>
      </article>
    `).join('');
  }

  function renderActivity() {
    const root = document.querySelector('#admin-activity');
    if (!root) return;
    root.innerHTML = adminActivity.map((item) => `
      <div class="grid gap-3 border-t border-neutral-900/10 p-4 first:border-t-0 md:grid-cols-[1fr_150px_110px] md:items-center">
        <div>
          <p class="font-bold text-neutral-950">${item.title}</p>
          <p class="mt-1 text-sm text-neutral-500">${item.area}</p>
        </div>
        <p class="text-sm text-neutral-500">${item.time}</p>
        <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">${item.status}</span>
      </div>
    `).join('');
  }

  function renderHealth() {
    const root = document.querySelector('#content-health');
    if (!root) return;
    const checks = [
      { label: 'Hero background', value: 'Slider 1 dan 4 aktif', percent: 100 },
      { label: 'Berita terbaru', value: `${news.length} dummy posts tersedia`, percent: 86 },
      { label: 'Settings awal', value: '8 tab siap simulasi', percent: 75 },
      { label: 'CRUD admin', value: 'Belum dibuat', percent: 18 }
    ];
    root.innerHTML = checks.map((item) => `
      <div class="rounded-2xl border border-neutral-900/10 bg-white p-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="font-bold text-neutral-950">${item.label}</p>
            <p class="mt-1 text-sm text-neutral-500">${item.value}</p>
          </div>
          <span class="text-sm font-bold text-blue-800">${item.percent}%</span>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-neutral-100">
          <div class="h-full rounded-full bg-blue-800" style="width:${item.percent}%"></div>
        </div>
      </div>
    `).join('');
  }
})();
