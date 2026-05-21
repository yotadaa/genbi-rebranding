# Complete Security Audit — Custom PHP MVC CMS — 2026-05-21

## Research Scope

- **Mode:** mixed codebase security audit
- **Application type:** custom PHP 8.2+ MVC CMS, **not Laravel**
- **Scope covered:** routes, middleware, request/session/response flow, auth, CSRF, role checks, public forms, admin JSON endpoints, upload handlers, SQL construction, HTML/URL sanitization, settings storage, static/upload serving, dependency manifests, and selected frontend DOM-injection surfaces.
- **Out of scope:** live production server configuration, actual database contents, authenticated browser testing of admin pages, penetration testing with real requests, and secret value inspection. `.env` exists but was not opened to avoid exposing secrets.

## Short Answer

The application has a reasonable custom-MVC security baseline: admin routes are authenticated and CSRF-protected, public POST routes are CSRF-protected, DB access generally uses prepared statements, uploaded files are MIME/image-validated with randomized names, and rich HTML is passed through a custom sanitizer.

The highest-priority gaps are:

1. **Request body size is not capped before reading JSON**.
2. **Several JSON admin POSTs omit `_csrf_token` in the body and rely only on the header**, which currently works but is brittle and inconsistent.
3. **CSP permits `'unsafe-inline'`**, weakening XSS containment.
4. **Legacy MD5 password hashes are still accepted**.
5. **Upload hardening is inconsistent for team uploads and server-level upload execution policy is not verifiable from the repo alone**.
6. **Some frontend `innerHTML` sinks interpolate URL values without escaping in preview-only contexts**, increasing XSS risk if stored/admin-controlled URLs become malicious.

Overall readiness: **Needs verification** before production hardening sign-off, because some risks depend on deployment configuration and actual DB/migration state.

## Audit Inventory

### Key files reviewed

```txt
public/index.php
bootstrap/app.php
routes/web.php
routes/admin.php
app/Core/Request.php
app/Core/Response.php
app/Core/Database.php
app/Core/Session.php
app/Core/ViewRenderer.php
app/Core/ErrorHandler.php
app/Core/Env.php
app/Middleware/CsrfMiddleware.php
app/Middleware/AuthMiddleware.php
app/Middleware/RoleMiddleware.php
app/Middleware/SecurityHeadersMiddleware.php
app/Services/AuthService.php
app/Services/CsrfService.php
app/Services/LoginThrottleService.php
app/Services/CommentThrottleService.php
app/Services/HtmlSanitizer.php
app/Services/SiteSettings.php
app/Controllers/Admin/*.php
app/Controllers/Public/*.php
app/Models/*.php
app/Config/*.php
package.json
.gitignore
```

### Commands run

```bash
npm audit --omit=dev
npm audit
```

Both reported:

```txt
found 0 vulnerabilities
```

No `composer.json` or `composer.lock` was found, so there was no Composer dependency audit to run.

## Findings

### Critical

No critical vulnerability was confirmed from static code review alone.

### High

No high-severity vulnerability was confirmed from static code review alone. The medium findings below should still be addressed before production sign-off because they are defense-in-depth and availability/security boundary weaknesses.

### Medium Findings

#### M1 — JSON request bodies are read without a size cap

- **Evidence:** `app/Core/Request.php:34-50`
- **What happens:** `Request::json()` calls `file_get_contents('php://input')` without checking `CONTENT_LENGTH` first.
- **Impact:** Any JSON endpoint can be forced to read a large request body into memory. This can cause memory pressure or denial of service, especially on shared hosting.
- **Affected surfaces:** Admin JSON POST endpoints, public comments/votes, public Prestasi submit JSON fallback, CSRF JSON fallback.
- **Recommendation:** Add a central request body limit before reading raw input. Example policy: 1 MiB for JSON by default, larger only for multipart upload handled by PHP upload limits. Return HTTP `413 Payload Too Large` when exceeded.

#### M2 — Some admin JSON POSTs rely on CSRF header only and omit body token

- **Evidence:**
  - CSRF middleware accepts header first: `app/Middleware/CsrfMiddleware.php:21-31`
  - Team home update body omits `_csrf_token`: `public/assets/js/admin/cms.js:2155-2160`
- **What happens:** This works because the middleware prefers `X-CSRF-TOKEN`, but it is inconsistent with most other endpoints that also include `_csrf_token` in JSON/form body.
- **Impact:** Not an immediate bypass, but brittle. If a proxy strips custom headers, if a future client posts without the header, or if middleware behavior changes, these actions fail unexpectedly. Consistency also helps auditing.
- **Recommendation:** Include `_csrf_token` in every JSON POST body as well as `X-CSRF-TOKEN`, or formalize header-only CSRF as the standard and update all clients/tests accordingly.

#### M3 — Content Security Policy allows inline scripts/styles

- **Evidence:** `app/Middleware/SecurityHeadersMiddleware.php:23`
- **What happens:** CSP includes `script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net` and `style-src 'self' 'unsafe-inline' ...`.
- **Impact:** If an HTML injection or escaping defect exists, inline script execution is not blocked by CSP. This weakens XSS containment.
- **Recommendation:** Inventory inline bootstraps/styles, move them to static files where possible, then migrate to nonce/hash-based CSP and remove `'unsafe-inline'` from `script-src`. Style hardening can be phased separately.

#### M4 — Legacy MD5 password fallback remains enabled

- **Evidence:** `app/Services/AuthService.php:151-164`
- **What happens:** Authentication first uses `password_verify()`, then accepts `md5($input)` for 32-character stored hashes.
- **Impact:** Any account still using an MD5 hash is materially weaker if the user table is leaked.
- **Recommendation:** Run a one-time migration/password reset for MD5 users, report remaining MD5-hash count, then remove the MD5 fallback. If transitional support is required, log MD5 logins and force immediate rehash/reset.

#### M5 — Login throttling fails open when its table is unavailable

- **Evidence:** `app/Services/LoginThrottleService.php:119-122`, `app/Services/LoginThrottleService.php:182-187`
- **What happens:** If `tbl_login_attempt` is missing or errors, login continues and logs one warning: `rate limiting disabled`.
- **Impact:** Brute-force protection silently weakens when migrations are incomplete or DB permissions fail.
- **Recommendation:** For production, fail closed after repeated attempts or add a file/session fallback throttle. Also include `tbl_login_attempt` migration status in deployment checks.

#### M6 — Upload execution hardening is inconsistent

- **Evidence:**
  - Team upload stores into `/public/uploads/team` without writing `.htaccess`: `app/Controllers/Admin/TeamMemberController.php:171-180`
  - News/Prestasi/Feature/Settings/PhotoGallery upload handlers write `.htaccess` disabling PHP execution.
  - Root `.gitignore` allows committed upload `.htaccess`: `.gitignore:24-30`
- **What happens:** Most upload directories attempt Apache `.htaccess` hardening, but team uploads do not. This also only helps Apache-compatible servers; Nginx/LiteSpeed config must be verified separately.
- **Impact:** Extension control and MIME checks reduce risk, but upload directories should never execute server-side code.
- **Recommendation:** Add the same `.htaccess` creation to team uploads and ensure a committed `/public/uploads/.htaccess` denies PHP execution. Document Nginx/LiteSpeed equivalent config for production.

#### M7 — URL validation for settings and gallery permits broad external URLs

- **Evidence:**
  - Settings URL validator only checks prefix `http(s)://` or `/`: `app/Controllers/Admin/SettingsController.php:322-330`
  - `HtmlSanitizer::sanitizeUrl()` permits `http` and `https` generally: `app/Services/HtmlSanitizer.php:70-96`
  - Photo gallery image field sanitizes as generic URL: `app/Controllers/Admin/PhotoGalleryController.php:95-106`
- **What happens:** Admin-controlled logo/favicon/banner/gallery image URLs can point to any HTTP(S) URL or local path.
- **Impact:** This is acceptable for trusted admins, but if an admin account is compromised it enables tracking pixels, broken-branding, mixed-content mistakes, or phishing-like asset swaps.
- **Recommendation:** For branding/gallery images, prefer local upload-backed paths (`/uploads/...`) or add an explicit trusted-host allowlist. Keep broad URL support only where intentionally needed.

#### M8 — Frontend preview `innerHTML` sinks interpolate returned URL values without escaping

- **Evidence:**
  - Photo preview: `public/assets/js/admin/cms.js:2077`
  - Team photo preview: `public/assets/js/admin/cms.js:2306`
  - Public Prestasi invalid message inserts raw `message`: `public/assets/js/pages/prestasi-submit.js:49-61`
- **What happens:** Several frontend templates assign `innerHTML` with interpolated values. Many call sites use `escape(...)`, but these preview URL/message examples do not.
- **Impact:** Current backend upload URLs are generated server-side and safe, so exploitability is limited for those preview cases. The pattern is risky if a future endpoint returns user-controlled URLs/messages or if an admin manually supplies a crafted URL.
- **Recommendation:** Use DOM APIs (`createElement`, `setAttribute`, `textContent`) for previews/messages or escape all interpolated values. Specifically escape the Prestasi invalid message and upload preview URLs.

#### M9 — Comment vote salt has a production-unsafe fallback

- **Evidence:** `app/Controllers/Public/CommentController.php:169`
- **What happens:** Vote hashing uses `COMMENT_VOTE_SALT`, but falls back to `genbi-comment-vote-local-fallback`.
- **Impact:** If production forgets the env var, voter hashes are predictable and less privacy-preserving.
- **Recommendation:** Require `COMMENT_VOTE_SALT` outside local development, or disable voting until configured.

### Low Findings / Hardening Items

#### L1 — Upload handlers do not explicitly reject zero-byte files everywhere

- **Evidence:**
  - PhotoGallery rejects `size <= 0`: `app/Controllers/Admin/PhotoGalleryController.php:72`
  - Public Prestasi rejects `size <= 0`: `app/Controllers/Public/PrestasiController.php:411`
  - News/Prestasi/Settings/Feature/Team primarily check upper size and rely on `getimagesize()` for invalid content.
- **Impact:** `getimagesize()` should reject zero-byte non-images, so this is low risk, but consistency is better.
- **Recommendation:** Add `size <= 0` checks to every upload handler.

#### L2 — Security headers are set globally but static upload serving has a separate narrower header set

- **Evidence:**
  - Global middleware headers: `app/Middleware/SecurityHeadersMiddleware.php:18-27`
  - Direct upload serving headers: `public/index.php:91-108`
- **Impact:** Upload responses include `nosniff` and `X-Frame-Options`, but not the full CSP/referrer/permissions set because direct serving returns before the router/middleware.
- **Recommendation:** Add a small shared header helper for direct static/upload responses, or at minimum add CSP to upload responses.

#### L3 — Public upload fallback SVG is emitted inline for missing images

- **Evidence:** `public/index.php:112-119`
- **Impact:** The SVG is static and not user-controlled, so this is low risk. It is still a special direct-response code path outside templating/middleware.
- **Recommendation:** Keep it static; do not interpolate request path into SVG output.

#### L4 — Admin static page catch-all requires strict page resolution discipline

- **Evidence:** `routes/admin.php:166-167`; `app/Core/ViewRenderer.php:82-87` strips `..` and resolves under view root.
- **Impact:** Current view resolution blocks simple traversal. Risk would increase if future code maps arbitrary page names to filesystem paths outside this resolver.
- **Recommendation:** Keep a page allowlist for admin catch-all pages where practical.

## Positive Findings

| Area | Evidence | Assessment |
|---|---|---|
| Admin auth boundary | `routes/admin.php:52-168`, `app/Middleware/AuthMiddleware.php:14-25`, `app/Middleware/RoleMiddleware.php:17-25` | Protected admin route group requires session and role. |
| CSRF | `routes/web.php:48-53`, `routes/admin.php:47-53`, `app/Middleware/CsrfMiddleware.php:14-43` | Public and admin POST routes are CSRF-protected. |
| Session security | `app/Core/Session.php:20-39`, `app/Core/Session.php:61-65` | Uses configured secure/samesite, httponly, idle timeout, login regeneration. |
| Database safety | `app/Core/Database.php:34-38`; model review | PDO exceptions and emulated prepares disabled; SQL mostly uses prepared statements. Dynamic column fragments are generally from allowlists. |
| Rich HTML sanitizer | `app/Services/HtmlSanitizer.php` | Allows limited tags/attrs, strips dangerous tags, sanitizes URLs, restricts iframes to Google Maps. |
| Upload validation | Upload controllers | Most handlers validate error, size, MIME via `finfo`, image via `getimagesize()`, and randomize filenames. |
| Dependency audit | `npm audit --omit=dev`, `npm audit` | NPM reported `found 0 vulnerabilities`. No Composer manifest found. |
| Secret hygiene | `.gitignore:1-4` | `.env` and `.env.*` ignored; `.env.example` allowed. |
| Production DB config | `app/Config/Database.php:15-33` | Production requires DB env vars; local defaults only used outside production. |
| Error handling | `app/Core/ErrorHandler.php:47-64` | Throwable responses are generic; details go to `error_log`, not user output. |

## SQL Injection Review

### Result

No confirmed SQL injection was found in the reviewed code.

### Evidence and reasoning

- Prepared statements are used for user inputs across reviewed models.
- `IN (...)` placeholder lists are built from `array_fill()` after IDs are cast with `intval`, e.g. `app/Models/News.php:169-178`, `app/Models/TeamMember.php:331-367`.
- Dynamic `UPDATE ... SET` fragments are built from allowlisted keys in models such as `News`, `Prestasi`, `PhotoGallery`, and `Feature`.
- Static `query()` calls are used for fixed SQL such as category lists or schema inspection, not direct user input.

### Residual risk

Future additions must keep dynamic SQL column/table names behind allowlists. Do not concatenate request-provided sort keys or column names without mapping them to known constants.

## XSS / Output Encoding Review

### Server-side

- Views use an `$e` escaping helper from `ViewRenderer`.
- Rich content is sanitized via `HtmlSanitizer` before storage/display for news, events, and Prestasi.
- Error pages escape titles/messages.

### Client-side

- Admin JS uses many `innerHTML` templates, often with `escape(...)`.
- Several unescaped preview/message sinks were found and should be fixed even if current backend values are safe.

## Upload Security Review

### Strong controls observed

- Randomized server-side filenames.
- MIME validation with `finfo`.
- Image validation with `getimagesize()`.
- Size cap around 5 MiB.
- `.htaccess` execution denial in most upload directories.

### Gaps

- Team upload directory misses `.htaccess` creation.
- Server-level no-execution policy for `/public/uploads` is not verifiable from repo alone.
- Upload endpoints generally accept GIF; if animated GIFs or image parser risk is a concern, consider converting to JPEG/WebP server-side or restricting GIF.

## Authentication & Authorization Review

- Login uses email validation, throttling service, `password_verify()`, role whitelist, session regeneration, and CSRF token regeneration.
- Roles are whitelisted in `AuthService`.
- Admin route group applies `RoleMiddleware` with default `superadmin`, `admin`.
- **Important behavior:** `editor` and `moderator` are accepted as valid roles by `AuthService`, but `RoleMiddleware` default only allows `superadmin` and `admin` for the protected admin group. This may be intentional, but if editors/moderators should access subsets of admin, routes need more granular role groups.

## CSRF Review

- CSRF enforcement is good for POST.
- No destructive GET routes were found in the route files reviewed.
- Recommended consistency improvement: include `_csrf_token` in all JSON bodies, not only the header.

## Dependency Review

- `package.json` has only dev dependencies: Tailwind, PostCSS, Autoprefixer, esbuild.
- `npm audit --omit=dev`: `found 0 vulnerabilities`.
- `npm audit`: `found 0 vulnerabilities`.
- No Composer manifest was found, so PHP library supply-chain exposure appears low from repository evidence.

## Deployment-Sensitive Checks Still Required

These cannot be proven from static repo review alone:

- Domain web root points to `/public`, not project root.
- PHP execution is disabled under `/public/uploads` at the web server level.
- `.env` on production has `APP_ENV=production`.
- Production has `SESSION_SECURE=true` or uses the production default from `app/Config/Security.php`.
- HTTPS termination correctly sets `HTTPS` or `HTTP_X_FORWARDED_PROTO=https` so HSTS is emitted.
- `tbl_login_attempt` migration exists and is writable.
- `COMMENT_VOTE_SALT` is set in production.
- `pdo_mysql` and PHP 8.2+ are enabled; `public/index.php` checks these at runtime.

## Evidence Ledger

| Claim | Source | Strength | Confidence |
|---|---|---:|---:|
| This is custom PHP MVC, not Laravel. | Project files/routes/controllers; no Laravel structure/composer package found | Local authoritative | High |
| Admin routes use auth, CSRF, role middleware. | `routes/admin.php:52-168` | Local authoritative | High |
| Public POST routes use CSRF. | `routes/web.php:48-53` | Local authoritative | High |
| CSRF checks header, POST body, then JSON body. | `app/Middleware/CsrfMiddleware.php:21-31` | Local authoritative | High |
| JSON body has no size cap. | `app/Core/Request.php:34-50` | Local authoritative | High |
| Sessions have httponly/samesite/secure config and idle timeout. | `app/Core/Session.php:20-39` | Local authoritative | High |
| Login accepts MD5 fallback. | `app/Services/AuthService.php:151-164` | Local authoritative | High |
| Login throttle fails open if table unavailable. | `app/Services/LoginThrottleService.php:119-122`, `182-187` | Local authoritative | High |
| CSP allows inline script/style. | `app/Middleware/SecurityHeadersMiddleware.php:23` | Local authoritative | High |
| Upload direct serving is constrained to upload root and adds `nosniff`. | `public/index.php:91-108` | Local authoritative | High |
| Team uploads lack `.htaccess` hardening. | `app/Controllers/Admin/TeamMemberController.php:171-180` | Local authoritative | Medium-high |
| NPM audit found no vulnerabilities. | `npm audit --omit=dev`, `npm audit` output | Command evidence | High |
| No Composer manifest found. | `glob composer.*` returned no files | Local tooling evidence | High |

## Recommended Remediation Plan

### Priority 1 — Small, high-value hardening

1. Add max JSON body size enforcement in `Request::json()`.
2. Add `_csrf_token` to all admin JSON POST bodies that currently rely only on `X-CSRF-TOKEN`.
3. Escape or DOM-build the unescaped frontend preview/message sinks.
4. Add `.htaccess` creation to `TeamMemberController::upload()` and commit a root `/public/uploads/.htaccess` if not already present.

### Priority 2 — Auth and deployment hardening

1. Remove MD5 fallback after confirming all admin passwords are `password_hash()` hashes.
2. Make login throttling fail closed or use a fallback throttle when DB table is unavailable.
3. Require `COMMENT_VOTE_SALT` in production.
4. Add a deployment checklist command/page that verifies required migrations/tables.

### Priority 3 — Defense-in-depth

1. Replace inline scripts/styles and migrate CSP toward nonce/hash-based policy.
2. Add URL allowlists for branding/gallery settings if external images are not required.
3. Normalize upload validation across all upload handlers, including explicit zero-byte rejection.
4. Add static/upload response security headers through a shared helper.

## Verification Commands

After fixes, run:

```bash
php -l app/Core/Request.php
php -l app/Controllers/Admin/TeamMemberController.php
php -l app/Middleware/CsrfMiddleware.php
npm run build:js
npm run build:css
npm test
php tests/php/PrestasiModelTest.php
npm audit --omit=dev
npm audit
```

For deployment verification, also run or manually check:

```bash
php database/migration_status.php
```

and confirm the production web root is `/public`.

## Risks & Unknowns

- Static review cannot prove server-level upload execution denial.
- Static review cannot prove production session/cookie behavior without production env values.
- `.env` exists but was intentionally not read.
- Admin browser verification remains blocked without an authenticated admin session.
- Actual database data may contain legacy MD5 hashes, unsafe historical HTML, or missing migrations; this audit only reviewed code behavior.

## Implementation Readiness

**Needs verification** — the code has a solid baseline and the remediation path is clear, but deployment-sensitive checks and a few code hardening fixes are needed before production security sign-off.

## Recommended Next Step

Implement Priority 1 hardening first: JSON body limit, CSRF body consistency, escaped/DOM-safe preview sinks, and team upload `.htaccess` hardening. Then run the verification commands above and update this audit with remediation status.
