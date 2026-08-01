(function(){"use strict";const{adminStats:r,adminActivity:n,news:s}=window.GenBIData,{renderAdminShell:i,icon:d}=window.GenBIAdmin;i("dashboard"),l(),c(),o();function l(){const e=document.querySelector("#admin-stats");e&&(e.innerHTML=r.map((t,a)=>`
      <article class="admin-card slide-in p-5" style="animation-delay:${a*45}ms">
        <div class="flex items-start justify-between gap-4">
          <div class="admin-stat-icon">${d(t.icon)}</div>
          <span class="rounded-full bg-[rgb(var(--surface-default))] px-2.5 py-1 text-xs font-bold text-[rgb(var(--text-secondary))] ring-1 ring-[rgb(var(--border-subtle)_/_0.18)]">CMS</span>
        </div>
        <p class="mt-6 text-sm font-bold uppercase tracking-[0.12em] text-[rgb(var(--text-secondary))]">${t.label}</p>
        <div class="mt-2 flex items-end justify-between gap-4">
          <p class="serif text-5xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">${t.value}</p>
          <p class="max-w-[8rem] text-right text-xs leading-5 text-[rgb(var(--text-secondary))]">${t.note}</p>
        </div>
      </article>
    `).join(""))}function c(){const e=document.querySelector("#admin-activity");e&&(e.innerHTML=n.map(t=>`
      <div class="admin-activity-item grid gap-3 border-t border-neutral-900/10 p-4 first:border-t-0 md:grid-cols-[1fr_150px_110px] md:items-center">
        <div>
          <p class="font-bold text-[rgb(var(--text-primary))]">${t.title}</p>
          <p class="mt-1 text-sm text-[rgb(var(--text-secondary))]">${t.area}</p>
        </div>
        <p class="text-sm text-[rgb(var(--text-secondary))]">${t.time}</p>
        <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">${t.status}</span>
      </div>
    `).join(""))}function o(){const e=document.querySelector("#content-health");if(!e)return;const t=[{label:"Hero background",value:"Slider 1 dan 4 aktif",percent:100},{label:"Berita terbaru",value:`${s.length} dummy posts tersedia`,percent:86},{label:"Settings awal",value:"8 tab siap simulasi",percent:75},{label:"CRUD admin",value:"Belum dibuat",percent:18}];e.innerHTML=t.map(a=>`
      <div class="admin-health-card rounded-2xl border border-neutral-900/10 bg-white p-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="font-bold text-[rgb(var(--text-primary))]">${a.label}</p>
            <p class="mt-1 text-sm text-[rgb(var(--text-secondary))]">${a.value}</p>
          </div>
          <span class="text-sm font-bold text-blue-800">${a.percent}%</span>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-[rgb(var(--surface-muted))]">
          <div class="h-full rounded-full bg-blue-800" style="width:${a.percent}%"></div>
        </div>
      </div>
    `).join("")}})();
