# Security Audit — GenBI Rebranding

Tanggal audit: 2026-06-22  
Branch/revisi: `ssr` @ `20282b7b322a61e4a190a792edaf423c5f64b8e3`  
Scope: seluruh repository PHP MVC, route publik/admin, controller/model/service, view SSR, JavaScript yang memanggil endpoint mutasi, upload handling, session/auth/CSRF, dan coverage semua route `POST`.

## Ringkasan eksekutif

Audit ini menemukan pola keamanan dasar yang sudah cukup baik: seluruh route `POST` yang ditemukan berada di dalam middleware CSRF; route mutasi admin berada di belakang `AuthMiddleware`, `CsrfMiddleware`, dan `RoleMiddleware`; mayoritas upload image divalidasi server-side dengan MIME detection (`finfo`/`mime_content_type`), `getimagesize()`, ukuran file, nama acak, dan `.htaccess` anti-eksekusi PHP.

Namun ada beberapa celah nyata yang perlu diprioritaskan:

| ID | Severity | Status | Temuan |
| --- | --- | --- | --- |
| SEC-01 | High | Confirmed | Token submit Prestasi tidak benar-benar one-time dan route publik menerima hash token sebagai bearer token. |
| SEC-02 | Medium | Confirmed | Token Presensi publik disimpan dan dikembalikan dalam plaintext, tanpa expiry field. |
| SEC-03 | Medium | Confirmed | Delete image Program Utama memakai path prefix check tanpa canonicalization; admin-controlled path dapat mengarah ke file lain di `public/`. |
| SEC-04 | Medium | Confirmed with precondition | Admin news fallback merender rich HTML dari DB secara raw; aman untuk konten baru yang lewat sanitizer, tapi berisiko untuk legacy/imported content. |
| SEC-05 | Low | Confirmed | Public Prestasi submission mengembalikan exception class dan message pada error transaksi. |
| SEC-06 | Low | Hardening | JSON-LD script tidak memakai `JSON_HEX_TAG`; saat ini input umumnya sudah disanitasi, tapi helper raw script terlalu rapuh untuk data masa depan. |
| SEC-07 | Low | Security debt | Legacy MD5 password fallback masih diterima saat login, walau direhash setelah login sukses. |

## Threat model singkat

Asset utama:

- Admin CMS, session admin, role admin/superadmin/editor/moderator.
- Konten publik: news, event, prestasi, team, settings, feature/program, gallery.
- Token publik: Prestasi submission token dan Presensi attendance token.
- Upload publik/admin di `public/uploads/**`.
- SEO/meta/JSON-LD yang dirender ke halaman publik.

Boundary yang diaudit:

- Browser publik -> route publik `POST`.
- Browser admin -> route admin `POST`.
- Admin/editor input -> database -> public/admin SSR/CSR rendering.
- Upload file -> `public/uploads/**` -> asset serving.
- Session/cookie/CSRF -> mutating routes.

Asumsi audit:

- Tidak ada DB production lokal untuk dynamic exploit replay; validasi dilakukan dengan static source-to-sink trace, route reachability, dan counterevidence dari middleware/model/service.
- `AGENTS.md` dipakai sebagai kebijakan keamanan repo: semua backend input hostile, semua mutating request wajib CSRF, upload harus divalidasi server-side, token Prestasi seharusnya one-time.

## Coverage route POST dan validasi server

### Proteksi global route POST

| Surface | Evidence | Assessment |
| --- | --- | --- |
| Public POST group | `routes/web.php:53-57` | Semua public `POST` masuk `$router->group([$csrfMiddleware], ...)`. |
| Admin login/logout | `routes/admin.php:51-53` | Login/logout memakai CSRF. Login memvalidasi email/password dan throttle. Logout rendah dampak. |
| Admin mutations | `routes/admin.php:57-189` | Semua admin mutation memakai `$authMiddleware`, `$csrfMiddleware`, `$roleMiddleware`. |
| CSRF enforcement | `app/Middleware/CsrfMiddleware.php:16-31`, `app/Services/CsrfService.php:21-30` | CSRF dicek untuk `POST`, token dari header/form/json, dibandingkan dengan `hash_equals`. |
| Future methods | `app/Middleware/CsrfMiddleware.php:16` | Middleware saat ini hanya enforce `POST`. Aman untuk route sekarang karena router hanya mendaftarkan mutasi via `POST`, tapi perlu diperluas jika nanti `PUT/PATCH/DELETE` ditambahkan. |

### Public POST routes

| Route | Controller | Server-side validation | Status |
| --- | --- | --- | --- |
| `POST /prestasi/submit/{token}` | `Public\PrestasiController::submitWithToken` | Body divalidasi `validateSubmission()`, upload dibatasi 6 image, MIME server-side, `getimagesize()`, random filename, `.htaccess`; token divalidasi via `validateTokenForUpdate()`. | Finding SEC-01 dan SEC-05. Input/form cukup divalidasi, tapi token policy dan error response bermasalah. |
| `POST /presensi/{token}` | `Public\PresensiController::submit` | Token event dicari by hash, event harus `open`, team/role dicek terhadap event, upload photo wajib dan divalidasi MIME + `getimagesize()`. | Form validation OK; token storage policy dilaporkan di SEC-02. |
| `POST /news/{slug}/comment` | `Public\CommentController::store` | News lookup by slug, policy comment, throttle, name/email/comment min/max, `strip_tags`, parent validation. | OK. |
| `POST /news/{slug}/comment/{id}/vote` | `Public\CommentController::vote` | News/comment lookup, vote value enum, throttle, voter key. | OK, dengan catatan salt default sebaiknya wajib env production. |

### Admin POST routes

Semua route di tabel ini berada di admin protected group kecuali login/logout, sehingga baseline-nya adalah auth + role + CSRF.

| Route family | Routes | Controller validation | Status |
| --- | --- | --- | --- |
| Auth | `/admin/login`, `/admin/logout` | Login validasi email/password, throttle service, session regenerate. Logout hanya destroy session. | OK; lihat SEC-07 untuk MD5 fallback. |
| News | `/admin/news`, `/admin/news/{id}/update`, `/admin/news/{id}/delete`, `/admin/news/upload` | Store memanggil `validate()` dan `sanitize()`. Update bersifat partial update dan sanitize field; upload cek size/MIME/`getimagesize()`/random filename/`.htaccess`. | Mostly OK; raw fallback view dilaporkan SEC-04. Partial update perlu dokumentasi intent. |
| Categories | `/admin/categories`, `/admin/categories/{id}/update`, `/admin/categories/{id}/delete` | `validatedName()` trim/length; delete model mencegah kategori yang masih dipakai. | OK. |
| Events | `/admin/events`, `/admin/events/{id}/update`, `/admin/events/{id}/delete` | Store/update validate title, sanitize content via `HtmlSanitizer`, map URL via `sanitizeMapEmbedUrl`. | Partial: date/location/meta lebih banyak sanitize daripada strict validate; bukan exploit langsung. |
| News comments | `/admin/news-comments/{id}/approve`, `/reject`, `/delete` | ID route cast integer; action enum ditentukan routing; model update status fixed. | OK. |
| Prestasi CMS | `/admin/prestasi`, `/admin/prestasi/{id}/update`, `/delete`, `/upload` | Store validate required fields, sanitize payload/status/slug; update partial sanitize; upload validates MIME/`getimagesize()`/random filename/`.htaccess`. | OK. |
| Presensi CMS | `/admin/presensi`, `/admin/presensi/{id}/update`, `/delete`, `/submissions/{id}/approve`, `/cancel`, `/presensi/{eventId}/members/{teamId}/approve` | `validatedPayload()` validates event fields/roles/member IDs; action IDs cast integer. | OK; see SEC-02 for token storage design. |
| GenBI Poin | `/admin/genbi-poin/activities`, `/admin/genbi-poin/activities/{id}/update` | `validatedActivity()` validates team/member/activity payload and scores. | OK. |
| Prestasi tokens | `/admin/prestasi-tokens`, `/admin/prestasi-tokens/{id}/revoke` | Generate validates label and expiry string; revoke ID cast. | Finding SEC-01: returned URL uses token hash and token lifecycle is reusable. |
| Team members | `/admin/team-members`, `/bulk`, `/upload`, `/{id}/update`, `/{id}/delete`, `/{id}/home`, `/{id}/alumni` | Store/update validate required fields; bulk IDs cast; upload checks MIME/`getimagesize()`/random filename/`.htaccess`. | OK. |
| Features / Program Utama | `/admin/features`, `/upload`, `/{id}/update`, `/{id}/delete`, `/{id}/images/reorder`, `/{id}/images/{imageId}/delete` | Store/update validate title/image presence and sanitize image payload; upload validated server-side. | Finding SEC-03 on image delete path containment. |
| Photo gallery | `/admin/photos`, `/upload`, `/{id}/update`, `/{id}/delete` | Store/update sanitize fixed fields; upload validates MIME/`getimagesize()`/random filename/`.htaccess`. | OK; update is partial sanitize, not strict required-field validation. |
| Settings | `/admin/settings/logo`, `/favicon`, `/topbar`, `/footer`, `/email`, `/banner`, `/sidebar`, `/color`, `/page-home`, `/upload`, `/theme` | `updateSettings()` rule-based validation; upload validates MIME/`getimagesize()`/random filename/`.htaccess`. | OK. |
| Contact setting | `/admin/contact-setting` | Model sanitizes payload, controller validates resulting clean fields. | OK. |
| Comment setting | `/admin/comment-setting` | Controller/model constrain comment policy fields. | OK. |

## Temuan detail

### SEC-01 — Prestasi submission token accepts hash bearer and is reusable

Severity: High  
Confidence: High  
Category: token lifecycle / authorization bypass  
CWE: CWE-287, CWE-640-like token secret handling  
Affected files:

- `app/Models/PrestasiToken.php:36-40`
- `app/Models/PrestasiToken.php:80-94`
- `app/Models/PrestasiToken.php:102-106`
- `app/Models/PrestasiToken.php:163-166`
- `app/Controllers/Admin/PrestasiTokenController.php:40-44`
- `app/Controllers/Public/PrestasiController.php:188-225`
- `database/migrations/2026_05_20_000001_alter_prestasi_tokens_unlimited_until_expiry.php:14-32`

Root cause:

- `generate()` membuat `bin2hex(random_bytes(32))`. Token ini sudah 64 hex.
- `tokenHash()` menganggap string 64 hex sebagai hash dan mengembalikannya tanpa `hash('sha256', ...)`.
- Admin response membangun `submit_url` dari `hash('sha256', $generated['token'])`.
- `mapRow()` juga mengekspos `submit_url` dari `token_hash`.
- Validator publik menerima nilai yang sama via `validateTokenForUpdate()`.
- `markUsed()` adalah no-op dan migration sengaja mengubah token menjadi reusable sampai expired/revoked.

Attack path:

1. Admin membuat Prestasi token.
2. Sistem mengembalikan plain token dan URL yang memakai hash token.
3. Route publik `/prestasi/submit/{token}` menerima token route tersebut.
4. `tokenHash()` menerima hash 64 hex sebagai nilai final dan query DB mencari `token_hash = :hash`.
5. Setelah submit sukses, token tidak ditandai used; submit bisa diulang hingga expiry/revoke.

Impact:

- Jika admin token list, DB, log, screenshot, atau URL hash bocor, nilai `token_hash` sendiri bisa dipakai sebagai bearer token.
- Token Prestasi tidak memenuhi requirement “one-time prestasi submission tokens”; attacker yang punya URL dapat submit draft Prestasi berulang sampai token dicabut/kedaluwarsa.

Counterevidence:

- Token random kuat (`random_bytes(32)`).
- Query mengecek `revoked_at` dan `expires_at`.
- Submit disimpan sebagai `draft`, bukan langsung published.

Rekomendasi:

1. Ubah format plain token supaya tidak ambigu dengan hash, misalnya prefix `pst_` + random base64url.
2. `tokenHash()` harus selalu `hash('sha256', $plainToken)`; jangan pernah menerima stored hash sebagai bearer.
3. `submit_url` harus memakai plain token hanya saat generate response pertama; jangan tampilkan plain token lagi setelah itu.
4. Implementasikan one-time semantics: `used_at`, `used_count`, atau `max_uses = 1`, dan panggil `markUsed()` di transaction yang sama setelah create sukses.
5. Tambahkan regression test:
   - plain token valid.
   - `hash('sha256', plain)` tidak valid sebagai route token.
   - submit kedua dengan token sama ditolak.

### SEC-02 — Presensi public tokens are stored and returned in plaintext

Severity: Medium  
Confidence: High  
Category: bearer token storage / secret exposure  
CWE: CWE-522  
Affected files:

- `database/migrations/2026_06_16_000001_create_tbl_presensi_event.php:10-24`
- `app/Models/PresensiEvent.php:17-30`
- `app/Models/PresensiEvent.php:211-245`
- `app/Models/PresensiEvent.php:165-180`

Root cause:

- Schema menyimpan `public_token` dan `public_token_hash`.
- `create()` menyimpan plain token ke `public_token` dan hash ke `public_token_hash`.
- `mapRow()` mengembalikan `public_token` dan `public_url` dari plain token.
- Lookup publik memang memakai hash, tetapi plaintext token tetap hidup di DB/API admin.
- Schema tidak memiliki `expires_at`, sehingga masa hidup token hanya dikendalikan status/deleted state event.

Attack path:

1. Presensi event dibuat dan token publik disimpan di DB.
2. Siapa pun yang mendapat read access ke table/API admin/log dapat melihat token langsung.
3. Token bisa dipakai ke `/presensi/{token}` selama event status `open`.

Impact:

- Database leak atau response admin leak langsung memberi bearer URL valid.
- Tidak ada expiry granular untuk token presensi; operator harus menutup/archive event untuk menghentikan token.

Counterevidence:

- Public lookup menggunakan `public_token_hash`, bukan plain token.
- Submit presensi tetap memvalidasi team/role/event open.

Rekomendasi:

1. Simpan hanya `public_token_hash`; tampilkan plain token hanya sekali saat event dibuat.
2. Jika admin perlu copy link ulang, generate/revoke token baru, bukan membaca plaintext lama.
3. Tambahkan `expires_at` atau token rotation policy.
4. Audit log token creation/revocation.

### SEC-03 — Feature image deletion lacks path containment

Severity: Medium  
Confidence: Medium-high  
Category: path traversal / unsafe file deletion  
CWE: CWE-22, CWE-73  
Affected files:

- `app/Models/Feature.php:456-479`
- `app/Models/Feature.php:535-543`
- `app/Models/Feature.php:202-221`
- `app/Controllers/Admin/FeatureController.php:138-149`
- `app/Controllers/Admin/FeatureController.php:285-292`

Root cause:

- `normalizeImagePath()` mengembalikan path yang sudah diawali `/uploads/features/` tanpa menolak `..`.
- `removeUploadedFile()` hanya mengecek `str_starts_with($path, self::UPLOAD_DIR)`.
- Path kemudian digabungkan ke `dirname(...)/public` dan dikirim ke `is_file()` + `unlink()`, tanpa `realpath()` dan containment check.

Attack path:

1. Authenticated admin/superadmin menyimpan image path seperti `/uploads/features/../../index.php` melalui payload images/feature.
2. Path tersebut lolos prefix check karena diawali `/uploads/features/`.
3. Saat image dihapus, filesystem menyelesaikan `..` dan `unlink()` dapat menargetkan file lain di `public/`.

Impact:

- Authenticated CMS user dengan akses Program Utama dapat menghapus file di luar upload feature.
- Dampak dibatasi oleh role admin dan kemampuan menulis path image, tetapi melanggar invariant “admin upload/delete hanya boleh menyentuh upload directory”.

Counterevidence:

- Route berada di admin protected group.
- Upload normal dari `storeUploadedFile()` menghasilkan nama random aman di `/uploads/features/`.
- Tidak ada unauthenticated reachability.

Rekomendasi:

1. Tolak path yang mengandung `..`, backslash, control char, atau URL eksternal untuk image lokal yang akan dihapus.
2. Di `removeUploadedFile()`, hitung `$base = realpath(public/uploads/features)` dan `$target = realpath(public . $path)`, lalu pastikan `$target` berada di dalam `$base` dengan separator boundary.
3. Simpan image path hasil upload saja untuk records yang akan dihapus fisiknya. Untuk external URL, hapus DB row tanpa `unlink`.
4. Tambahkan test untuk `/uploads/features/../../index.php`, encoded traversal, dan sibling prefix seperti `/uploads/features_evil`.

### SEC-04 — Admin news edit fallback renders stored rich content raw

Severity: Medium  
Confidence: Medium  
Category: stored XSS / sanitizer dependency  
CWE: CWE-79  
Affected files:

- `app/Views/admin/news/form.php:5-7`
- `app/Views/admin/news/form.php:45-47`
- `app/Controllers/Admin/NewsController.php:249-325`

Root cause:

- Admin news edit fallback mengambil `$item['content']` dan merender `<?= $content ?>` langsung dalam `contenteditable`.
- Konten baru/update yang lewat `Admin\NewsController` disanitasi dengan `HtmlSanitizer::sanitize()`.
- Risiko muncul jika DB legacy/import/import manual memuat `news_content` yang tidak pernah melewati sanitizer.

Attack path:

1. Konten news berbahaya masuk ke DB melalui jalur legacy/import/manual DB atau bug endpoint lain.
2. Admin membuka edit news.
3. Fallback SSR merender HTML raw ke admin page.

Impact:

- Stored XSS di admin context jika precondition legacy/imported unsanitized content terpenuhi.

Counterevidence:

- Path normal create/update sekarang memanggil sanitizer.
- Public news controller re-sanitize konten sebelum render.

Rekomendasi:

1. Sanitasi `$content` saat keluar ke fallback admin editor, bukan hanya saat masuk.
2. Simpan rich HTML yang sudah disanitasi di DB, tetapi tetap lakukan defense-in-depth output sanitization untuk rich sinks.
3. Tambahkan migration/command one-off untuk sanitize legacy `tbl_news.news_content`.
4. Tambahkan test dengan payload `<script>`/event handler untuk admin edit fallback.

### SEC-05 — Public Prestasi submission leaks exception details

Severity: Low  
Confidence: High  
Category: information disclosure  
CWE: CWE-209  
Affected file:

- `app/Controllers/Public/PrestasiController.php:232-238`
- `app/Controllers/Public/PrestasiController.php:257-268`

Root cause:

- Saat catch exception, controller memanggil `submissionFailurePayload('submission_transaction_failed', $e)`.
- Payload publik menyertakan `$error::class` dan `$error->getMessage()`.

Attack path:

1. Request publik dengan token valid memicu exception DB/storage/constraint.
2. Response JSON mengembalikan class exception dan message internal.

Impact:

- Bisa membocorkan detail schema, SQL error, path filesystem, atau kondisi internal lain.
- Dampak rendah karena perlu token valid dan tidak langsung memberi privilege.

Rekomendasi:

1. Jangan kirim `exception` atau raw `message` ke client.
2. Kirim correlation ID/log ID dan simpan detail hanya di server log.
3. Pastikan mode debug lokal saja yang dapat menampilkan detail, dan tidak aktif production.

### SEC-06 — JSON-LD script helper lacks `JSON_HEX_TAG`

Severity: Low  
Confidence: Medium  
Category: output encoding hardening  
CWE: CWE-79 hardening  
Affected files:

- `app/Services/StructuredData.php:160-162`
- `app/Views/layouts/public.php:25`

Root cause:

- `StructuredData::script()` menulis raw `<script type="application/ld+json">` dengan `json_encode(... JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)`.
- Tidak memakai `JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`, `JSON_HEX_QUOT`.

Impact:

- Saat ini banyak input SEO/title/content sudah strip/sanitize, jadi ini bukan XSS terkonfirmasi.
- Tetapi helper raw script menjadi rapuh jika field masa depan berisi `</script>` atau data import belum disanitasi.

Rekomendasi:

Gunakan flags:

```php
JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
```

### SEC-07 — Legacy MD5 password fallback remains accepted

Severity: Low  
Confidence: High  
Category: password storage migration debt  
CWE: CWE-916  
Affected file:

- `app/Services/AuthService.php:158-172`

Assessment:

- Login masih menerima stored hash MD5 32-char.
- Counterevidence kuat: saat login berhasil, password langsung direhash dengan `password_hash()`.
- Ini wajar sebagai migration bridge, tetapi sebaiknya dibatasi waktunya.

Rekomendasi:

1. Tambahkan migration/command untuk memaksa reset password legacy.
2. Tambahkan cutoff date/env flag untuk menonaktifkan MD5 fallback.
3. Monitor jumlah akun yang masih legacy.

## Suppressed / not found

| Area | Result |
| --- | --- |
| SQL injection | Tidak ditemukan direct SQL injection pada model/controller yang direview. Query dinamis memakai placeholder, cast ID array, whitelist enum/columns, dan PDO prepared statements. |
| CSRF pada route sekarang | Tidak ditemukan mutating route tanpa CSRF. Semua route `POST` publik/admin terdaftar dalam CSRF group. |
| GET destructive action | Tidak ditemukan route delete berbasis GET di route PHP. Delete action memakai `POST`. |
| Upload active content | Upload image utama memakai server-side MIME validation dan `getimagesize()`; nama file acak; `.htaccess` ditulis di upload dirs. |
| Public comments | Public rendering comments menggunakan escape, controller strip tags, model hanya menampilkan approved comments. |
| Admin indexing | Layout admin memakai `noindex, nofollow`. |

## Hardening tambahan

- `CsrfMiddleware` perlu diperluas jika nanti route `PUT`, `PATCH`, atau `DELETE` ditambahkan.
- `RoleMiddleware` saat ini default allow `superadmin` dan `admin`, sementara `AuthService::allowedRoles()` mengenal `editor` dan `moderator`; pastikan ini memang policy, bukan lupa memberi akses terbatas.
- `SecurityHeadersMiddleware` masih mengizinkan `script-src 'unsafe-inline'`; ini mungkin diperlukan karena SSR/legacy inline script, tapi sebaiknya dikurangi bertahap.
- `HtmlSanitizer::sanitizeUrl()` menerima semua URL yang diawali `/`, termasuk protocol-relative `//example.com`; ini bukan XSS langsung karena scheme berbahaya ditolak, tapi jika policy ingin first-party-only, reject `//`.
- Audit logging masih belum konsisten untuk semua admin mutation; table audit ada, tetapi tidak semua model/controller menulis audit log.

## Rekomendasi prioritas fix

1. Perbaiki token Prestasi one-time dan hash handling (SEC-01).
2. Hilangkan plaintext Presensi token di DB/API, tambahkan expiry/rotation (SEC-02).
3. Tambahkan canonical path containment sebelum `unlink()` Program Utama (SEC-03).
4. Sanitize rich HTML saat output ke admin news fallback dan buat migration sanitize legacy content (SEC-04).
5. Hilangkan detail exception dari response publik (SEC-05).
6. Tambahkan regression tests untuk semua bug di atas.

## Verifikasi audit

Commands/evidence yang digunakan:

- `git status --short --branch`
- `git rev-parse HEAD`
- `rg -n "post\\(|group\\(|CsrfMiddleware|AuthMiddleware|RoleMiddleware" routes app`
- `rg -n "function submitWithToken|validateTokenForUpdate|markUsed|tokenHash|submit_url|public_token|public_url" app database`
- `rg -n "deleteImage|removeUploadedFile|normalizeImagePath|unlink" app/Controllers app/Models`
- `rg -n "MAX_UPLOAD_SIZE|ALLOWED_IMAGE_TYPES|finfo|getimagesize|move_uploaded_file|.htaccess" app/Controllers app/Services`

