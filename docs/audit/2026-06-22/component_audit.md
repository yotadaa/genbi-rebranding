# Component Reusability Audit — GenBI Rebranding

Tanggal audit: 2026-06-22  
Branch/revisi: `ssr` @ `20282b7b322a61e4a190a792edaf423c5f64b8e3`  
Scope: `public/assets/js/**/*.js`, `assets/js/**/*.js`, `app/Views/**/*.php`, admin/public shell, CMS JS, modal/dropdown/pagination/list patterns, upload/validation helper candidates.

## Ringkasan

Codebase sudah punya beberapa fondasi reusable yang bagus:

- `window.GenBIAPI` dan `public/assets/js/api-core.js` untuk route/API normalization.
- `window.GenBIUI` di `public/assets/js/lib/ui.js` untuk modal, custom select/dropdown, mobile menu, observer, image helper.
- `window.GenBIAdmin` di `public/assets/js/admin/admin.js` untuk shell admin, toast, confirm, icon, escape helper.
- PHP MVC sudah memisahkan controller/model/view dan beberapa service (`HtmlSanitizer`, `SeoService`, `CsrfService`, etc).

Tetapi maintainability mulai berat karena ada source tree ganda, file admin CMS monolitik, dan banyak pola UI/CRUD/upload yang berulang. Ini bisa membuat bug fix tidak konsisten dan scalable-nya turun saat fitur CMS bertambah.

## Prioritas temuan

| ID | Priority | Temuan | Rekomendasi utama |
| --- | --- | --- | --- |
| CMP-01 | P1 | Dua source tree JS (`public/assets/js` dan `assets/js`) drift. | Jadikan satu canonical source atau buat sync/build rule eksplisit. |
| CMP-02 | P1 | Rendering public/admin masih banyak `innerHTML` dari API/backend tanpa escape/sanitize helper konsisten. | Buat `GenBIUI.escapeHtml()` dan `GenBIUI.sanitizeContentHtml()`, lalu pakai default escape. |
| CMP-03 | P1 | `public/assets/js/admin/cms.js` terlalu besar dan memuat banyak fitur CMS sekaligus. | Pecah menjadi module per resource + shared admin list/form controller. |
| CMP-04 | P2 | Modal/confirm behavior duplicated walau `createModalController()` sudah ada. | Jadikan satu modal/confirm wrapper shared. |
| CMP-05 | P2 | Pagination duplicated di public JS, admin JS, dan PHP views. | Extract shared JS pagination dan PHP partial/helper. |
| CMP-06 | P2 | Admin CRUD list/table workflow diulang per feature. | Buat `AdminListController` / resource config. |
| CMP-07 | P2 | Upload validation PHP duplicated di banyak controller. | Buat `ImageUploadService` + policy per context. |
| CMP-08 | P2 | Request/CSRF wrapper duplicated di beberapa admin/public modules. | Standarkan semua mutating fetch ke `GenBIAPI.requestJson()` atau helper admin wrapper. |
| CMP-09 | P2 | SSR/CSR hydration contract belum terdokumentasi dan bisa drift. | Document data attributes atau fetch detail by ID. |
| CMP-10 | P3 | Build output/source convention membingungkan; ada `assets/js/dist/` untracked. | Ignore/remove wrong dist path dan dokumentasikan build output resmi. |

## Temuan detail

### CMP-01 — Dua source tree JS drift

Priority: P1  
Evidence:

- Build script memakai `public/assets/js` sebagai source dan output ke `public/assets/js/dist`: `tools/build-js.js:5`, `tools/build-js.js:36`.
- PHP layouts memuat `/assets/js/dist/...`: `app/Views/layouts/public.php:37-41`, `app/Views/layouts/admin.php:41-46`.
- Static fallback masih memuat `assets/js` langsung, misalnya `fallbacks/index.html:148`.
- `public/assets/js/lib/ui.js` sudah punya helper baru seperti `enhanceProjectSelects` dan `setupPublicMobileMenu`, sedangkan `assets/js/lib/ui.js` export list berbeda.
- Worktree menunjukkan untracked `assets/js/dist/`, bukan output resmi `public/assets/js/dist/`.

Risiko:

- Developer bisa edit tree yang salah.
- Static fallback dan PHP runtime bisa beda behavior.
- Bug fix di UI helper bisa hanya masuk salah satu tree.

Rekomendasi:

1. Pilih canonical source: disarankan `public/assets/js` karena build saat ini memakai tree itu.
2. Jika `assets/js` tetap diperlukan untuk fallback static, buat script sync eksplisit dan CI check diff.
3. Tambahkan `.gitignore` untuk `assets/js/dist/` atau hapus output salah lokasi.
4. Dokumentasikan: “edit source JS di X, build output di Y”.

### CMP-02 — `innerHTML` tanpa shared escape/sanitize

Priority: P1  
Evidence:

- News detail injects API fields into `root.innerHTML`: `public/assets/js/pages/news-detail.js:49`.
- `cleanNewsContent()` hanya remove style attr lalu memakai `wrapper.innerHTML`: `public/assets/js/pages/news-detail.js:231-233`.
- News list renders cards via `innerHTML`: `public/assets/js/pages/news.js:66`.
- Team modal/card renders via `innerHTML`: `public/assets/js/pages/team.js:177`.
- Prestasi modal/card renders via `innerHTML`: `public/assets/js/pages/prestasi.js:137`.
- Event modal body renders via `innerHTML`: `public/assets/js/pages/event.js:165`.
- `Admin.showConfirm({ html: true })` menulis raw `innerHTML`: `public/assets/js/admin/admin.js:366`.
- Prestasi admin detail injects `item.content` raw: `public/assets/js/admin/cms.js:3193`.

Risiko:

- Sanitization tersebar per file dan tidak selalu jelas mana plain text vs rich HTML.
- Jika backend/API field baru tidak disanitasi server-side, public/admin CSR menjadi raw HTML sink.

Rekomendasi:

1. Tambahkan `GenBIUI.escapeHtml(value)`.
2. Tambahkan `GenBIUI.sanitizeContentHtml(html)` untuk rich content yang memang boleh HTML.
3. Default rule: plain fields harus escaped; rich fields harus lewat sanitizer allowlist.
4. Hindari `html: true` di confirm untuk data dinamis, atau wajib pass through sanitizer.
5. Tambahkan lint/search checklist untuk `innerHTML =` baru.

### CMP-03 — `admin/cms.js` monolitik

Priority: P1  
Evidence:

- `public/assets/js/admin/cms.js` berisi ribuan baris dan banyak domain: news, category, event, slider, team, feature, why, FAQ, social, gallery, prestasi, token, etc.
- Pola list state dan render berulang:
  - News list flow: `public/assets/js/admin/cms.js:316`.
  - Event rows: `public/assets/js/admin/cms.js:1087`.
  - Team state/list: `public/assets/js/admin/cms.js:1383`.
  - Prestasi list state: `public/assets/js/admin/cms.js:2436`.
  - Token rows: `public/assets/js/admin/cms.js:3327`.

Risiko:

- Sulit test per fitur.
- Refactor kecil berisiko menyentuh banyak fitur.
- Bundle admin memuat logic untuk semua page, bukan hanya page aktif.

Rekomendasi struktur:

```text
public/assets/js/admin/
  cms/
    index.js
    resources/news.js
    resources/event.js
    resources/team.js
    resources/feature.js
    resources/prestasi.js
    resources/prestasi-token.js
    shared/list-controller.js
    shared/form-controller.js
    shared/upload-field.js
    shared/resource-actions.js
```

Mulai dari extraction non-breaking: pindahkan helper shared dulu, baru pecah resource satu per satu.

### CMP-04 — Modal/confirm duplicated

Priority: P2  
Evidence:

- Reusable modal controller sudah ada: `public/assets/js/lib/ui.js:58`.
- Video modal custom: `public/assets/js/pages/home.js:307`.
- Prestasi image modal custom: `public/assets/js/pages/prestasi-detail.js:19`.
- Admin confirm custom: `public/assets/js/admin/admin.js:348`.
- Feature icon picker modal/focus trap sendiri: `public/assets/js/admin/cms.js:1655`.

Risiko:

- Focus trap, Escape close, body lock, restore focus, dan backdrop behavior bisa beda-beda.
- Accessibility bug bisa fix di satu modal tapi tertinggal di modal lain.

Rekomendasi:

1. Buat `GenBIUI.createAdHocModal({ content, panelSelector, initialFocusSelector })`.
2. Buat `GenBIUI.confirm()` yang memakai controller yang sama.
3. Migrasi admin confirm, video modal, image preview, icon picker, dan presensi modal fallback ke wrapper itu.

### CMP-05 — Pagination duplicated

Priority: P2  
Evidence:

- `renderPagination()` public news: `public/assets/js/pages/news.js:86`.
- `renderPagination()` event: `public/assets/js/pages/event.js:117`.
- Team pagination: `public/assets/js/pages/team.js:215`.
- Admin pagination: `public/assets/js/admin/cms.js:2623`.
- PHP SSR pagination berulang: `app/Views/public/team/index.php:97`, `app/Views/admin/news/index.php:227`.

Risiko:

- Label, disabled state, active state, keyboard/focus behavior tidak konsisten.
- Styling pagination rawan drift saat UI berubah.

Rekomendasi:

- JS: `GenBIUI.renderPagination({ root, currentPage, totalPages, onPageChange, hrefForPage })`.
- PHP: partial/helper `partials/pagination.php` dengan kontrak class yang sama.
- Buat snapshot/manual checklist untuk pagination public/admin.

### CMP-06 — Admin CRUD workflow berulang

Priority: P2  
Pattern yang berulang:

- Load list.
- Simpan `state.items/currentPage/perPage/search/filter`.
- Render table/card.
- Bind edit/delete/approve/revoke.
- Render pagination.
- Show confirm.
- Fetch `POST` with CSRF.

Contoh area:

- Category editor/delete.
- News list/delete.
- Event list/delete.
- Team list/bulk action.
- Feature image reorder/delete.
- Prestasi list/detail/delete.
- Prestasi token generate/revoke.

Rekomendasi:

```js
GenBIAdmin.createListController({
  root,
  endpoint,
  state,
  filters,
  renderItem,
  renderEmpty,
  actions: {
    delete: { endpoint, confirm, onSuccess },
    approve: { endpoint, confirm, onSuccess }
  },
  pagination: true
})
```

Ini tidak harus framework besar; cukup helper kecil berbasis config agar tiap resource hanya menyumbang render dan endpoint.

### CMP-07 — Upload validation duplicated di PHP controllers

Priority: P2  
Evidence:

- News upload: `app/Controllers/Admin/NewsController.php:167-226`.
- Prestasi admin upload: `app/Controllers/Admin/PrestasiController.php:125-202`.
- Public Prestasi submission upload: `app/Controllers/Public/PrestasiController.php:405-444`.
- Public Presensi photo upload: `app/Controllers/Public/PresensiController.php:140-174`.
- Feature upload: `app/Controllers/Admin/FeatureController.php:238-279`.
- Photo gallery upload: `app/Controllers/Admin/PhotoGalleryController.php:65-88`.
- Team member upload: `app/Controllers/Admin/TeamMemberController.php:167-207`.
- Settings upload: `app/Controllers/Admin/SettingsController.php:94-145`.

Hal yang sudah baik:

- Hampir semua menggunakan size limit, MIME server-side, `getimagesize()`, random filename, dan `.htaccess`.

Masalah maintainability:

- Policy MIME/size/path/error message tersebar.
- Perbaikan security upload harus diulang banyak tempat.

Rekomendasi:

Buat service kecil:

```php
final class ImageUploadService
{
    public function store(array $file, ImageUploadPolicy $policy): UploadResult;
}
```

Policy berisi:

- upload dir;
- max size;
- allowed MIME;
- filename prefix;
- allowed extensions mapping;
- whether `is_uploaded_file()` required;
- whether external URL is allowed.

### CMP-08 — Request/CSRF wrapper duplicated

Priority: P2  
Evidence:

- Core request wrapper: `public/assets/js/api.js:18`.
- Presensi page fallback wrapper: `public/assets/js/pages/presensi.js:20`.
- Admin GenBI point local escape/request patterns: `public/assets/js/admin/genbi-point.js:7`, `public/assets/js/admin/genbi-point.js:21`.
- Admin settings has custom upload/post logic.
- `admin/cms.js` memiliki banyak raw `fetch(... method: "POST" ...)`.

Rekomendasi:

1. Expose satu helper:

```js
GenBIAPI.requestJson(path, {
  method: 'POST',
  body,
  csrf: true,
  formData: false
})
```

2. Untuk admin:

```js
GenBIAdmin.post(routeName, params, body)
GenBIAdmin.upload(routeName, params, formData)
```

3. Semua mutating request harus lewat helper agar header `Accept`, `credentials`, `X-CSRF-TOKEN`, JSON parsing, dan error toast konsisten.

### CMP-09 — SSR/CSR hydration contract drift

Priority: P2  
Evidence:

- Prestasi SSR list link hanya membawa `data-id`/`data-index`: `app/Views/public/prestasi/index.php:39`.
- JS hydration Prestasi mengharapkan data lebih kaya seperti title/name/campus/description di `public/assets/js/pages/prestasi.js:30`.

Risiko:

- Modal/detail CSR bisa kehilangan field ketika SSR markup berubah.
- Fallback data dan backend JSON bisa beda shape.

Rekomendasi:

1. Untuk item detail, fetch JSON by ID/slug saat modal dibuka.
2. Jika tetap embed `data-*`, dokumentasikan kontrak per component.
3. Tambahkan normalization function di `api-core.js` untuk semua shape SSR/CSR.

### CMP-10 — Build output convention membingungkan

Priority: P3  
Evidence:

- Resmi output build: `public/assets/js/dist/`.
- Worktree saat audit memiliki untracked `assets/js/dist/`.
- `.gitignore` tampaknya belum mencegah `assets/js/dist/` muncul sebagai untracked noise.

Rekomendasi:

1. Hapus local generated `assets/js/dist/` jika tidak dipakai.
2. Tambahkan ignore rule untuk `assets/js/dist/`.
3. Pastikan `npm run build:js`/build script hanya menulis output resmi.

## Kandidat komponen reusable

### Frontend JS

| Candidate | Tujuan | Prioritas |
| --- | --- | --- |
| `GenBIUI.escapeHtml(value)` | Escape plain text di semua render template. | P1 |
| `GenBIUI.sanitizeContentHtml(html)` | Sanitizer rich content CSR. | P1 |
| `GenBIUI.createAdHocModal()` | Modal runtime dengan focus trap/body lock/Escape/backdrop. | P2 |
| `GenBIUI.confirm()` | Confirm modal public/admin konsisten. | P2 |
| `GenBIUI.renderPagination()` | Pagination CSR reusable. | P2 |
| `GenBIAdmin.createListController()` | Search/filter/page/action list admin. | P2 |
| `GenBIAdmin.renderToolbar()` | Search, filter, per-page toolbar. | P3 |
| `GenBIAdmin.renderTable()` | Table shell, empty state, loading state. | P3 |
| `GenBIAdmin.memberPicker()` | Typeahead member untuk Presensi dan GenBI Poin. | P3 |

### PHP backend

| Candidate | Tujuan | Prioritas |
| --- | --- | --- |
| `ImageUploadService` + `ImageUploadPolicy` | Satu tempat untuk MIME, size, random filename, `.htaccess`, `getimagesize`. | P1 |
| `Validator` kecil | Required/string length/enum/date/int array/email/url validation. | P2 |
| `AuditLogger` service | Konsisten untuk admin mutation penting. | P2 |
| `ResponseFactory` / `JsonResponder` | Error shape, validation response, correlation ID. | P2 |
| `PathGuard` | `realpath()` containment untuk upload delete/serve. | P1 |
| PHP view partials: `pagination`, `status-badge`, `form-field`, `image-upload-field` | Kurangi duplikasi SSR views. | P3 |

## Refactor roadmap

### Phase 1 — Safety helpers dulu

1. Tambahkan `GenBIUI.escapeHtml()` dan migrasi local escape helper.
2. Tambahkan `GenBIUI.sanitizeContentHtml()` untuk content HTML.
3. Tambahkan `PathGuard` untuk containment file path.
4. Tambahkan `ImageUploadService`.

### Phase 2 — Stabilkan source/build

1. Tentukan canonical JS source tree.
2. Retire/sync tree satunya.
3. Ignore/hapus `assets/js/dist/`.
4. Dokumentasikan build command dan output.

### Phase 3 — Pecah admin CMS

1. Extract helper non-UI dari `admin/cms.js`.
2. Extract `AdminListController`.
3. Pindahkan resource paling kecil dulu: Prestasi token atau category.
4. Lanjut ke News/Event/Team/Feature.

### Phase 4 — UI component consistency

1. Modal/confirm shared.
2. Pagination shared.
3. Toolbar/table shared.
4. SSR partial parity.

## Acceptance criteria untuk refactor berikutnya

- Tidak ada source JS ganda yang bisa drift tanpa check.
- Semua raw `innerHTML` baru harus memakai helper escape/sanitize.
- Semua upload image baru lewat `ImageUploadService`.
- Semua path delete/upload memakai canonical containment check.
- Semua admin list baru memakai shared list/action helper atau punya alasan tertulis.
- `npm test` tetap pass setelah perubahan API normalization/integration.
- `npm run build:css` dijalankan jika class Tailwind berubah.

