# Partial Security Audit — 2026-05-21

## Research Scope

- **Mode:** mixed codebase security audit
- **Application type:** custom PHP MVC CMS (not Laravel)
- **Context:** PHP 8.2+ custom routing, SSR views, admin CMS routes, public Prestasi/news/comment routes
- **Sources checked:** local repository files only; no dependency audit commands were run for this partial pass.

## Short Answer

This was a **partial security audit**. It found actionable issues in request body handling, CSP posture, legacy password compatibility, fail-open login throttling, and upload hardening consistency. It did **not** complete a full pass across every controller/model and should not be treated as the final audit.

Confidence: **medium-high** for the listed findings, because each is based on local file evidence. Completeness: **partial**.

## Findings

### Positive controls observed

- Admin routes are grouped behind `AuthMiddleware`, `CsrfMiddleware`, and `RoleMiddleware`.
- Public POST routes for Prestasi submit and news comments/votes are CSRF-protected.
- PDO is configured with prepared statements and `PDO::ATTR_EMULATE_PREPARES => false`.
- Most upload handlers validate upload errors, size, MIME using `finfo`, image validity using `getimagesize()`, and randomized filenames.
- CSRF middleware protects POST requests and checks `X-CSRF-TOKEN`, POST `_csrf_token`, and JSON `_csrf_token`.
- Sessions use `httponly`, configured `secure`, configured `samesite`, idle timeout, and session ID regeneration on login.
- `HtmlSanitizer` strips disallowed tags, sanitizes URL schemes, restricts iframe/map URLs to Google Maps hosts, and adds `noopener noreferrer` for `_blank` links.

### Issues found so far

| Severity | Finding | Evidence | Risk | Recommended fix |
|---|---|---|---|---|
| Medium | `Request::json()` reads the entire raw request body without a size limit. | `app/Core/Request.php:34-50` | Large JSON POST can cause memory pressure / DoS. | Check `CONTENT_LENGTH` before reading, cap JSON body size, return HTTP 413. |
| Medium | CSP allows `'unsafe-inline'` for scripts/styles. | `app/Middleware/SecurityHeadersMiddleware.php:23` | Inline XSS has higher impact if any output escaping bug exists. | Move inline scripts/styles to files, use nonce/hash-based CSP, gradually remove `'unsafe-inline'`. |
| Medium | Legacy MD5 password fallback is still accepted. | `app/Services/AuthService.php:151-164` | If old hashes exist, compromise cost is low. | Force password reset/rehash migration and remove MD5 fallback after migration window. |
| Medium | Login throttling disables itself if the throttle table is unavailable. | `app/Services/LoginThrottleService.php:119-122`, `app/Services/LoginThrottleService.php:182-187` | Brute-force protection silently weakens if migration/table is missing. | Fail closed for repeated login failures or add file/session fallback throttle. |
| Low-Medium | Upload hardening is inconsistent: team uploads do not create `.htaccess` disabling PHP execution, while several other upload handlers do. | `app/Controllers/Admin/TeamMemberController.php:171-180`; compare `Admin/PrestasiController.php`, `Admin/PhotoGalleryController.php`, `Admin/FeatureController.php`, `Admin/SettingsController.php` | Lower risk because extension is controlled, but directory execution hardening should be consistent. | Add `.htaccess` or server-level execution denial to `/public/uploads/team`. |
| Low-Medium | Settings URL validation accepts `http(s)://` or any `/...` without centralized URL sanitizer and without host/path policy. | `app/Controllers/Admin/SettingsController.php:322-330` | Admin can store unexpected external URLs; if account is compromised, branding/hero assets can point to tracking/phishing assets. | Use `HtmlSanitizer::sanitizeUrl()` and optionally restrict upload-backed settings to `/uploads/...`. |
| Low | Comment vote salt has an insecure fallback. | `app/Controllers/Public/CommentController.php:169` | Vote identity hashes are predictable when `COMMENT_VOTE_SALT` is missing. | Require env salt in production or disable voting when missing. |

## Evidence

| Claim | Source | Strength | Confidence |
|---|---|---:|---:|
| Admin routes require auth + CSRF + role middleware. | `routes/admin.php:52-168` | Local authoritative | High |
| Public POST routes require CSRF. | `routes/web.php:48-53` | Local authoritative | High |
| JSON body read has no size guard. | `app/Core/Request.php:34-50` | Local authoritative | High |
| CSP contains `'unsafe-inline'`. | `app/Middleware/SecurityHeadersMiddleware.php:23` | Local authoritative | High |
| MD5 password fallback exists. | `app/Services/AuthService.php:151-164` | Local authoritative | High |
| Login throttling fails open when table unavailable. | `app/Services/LoginThrottleService.php:119-122`, `app/Services/LoginThrottleService.php:182-187` | Local authoritative | High |
| Uploads generally validate MIME and dimensions. | Upload methods in admin/public controllers | Local authoritative | High |
| Team upload lacks `.htaccess` hardening while others add it. | `app/Controllers/Admin/TeamMemberController.php:171-180`; other upload controllers | Local authoritative | Medium-high |
| `HtmlSanitizer` restricts iframe to Google Maps. | `app/Services/HtmlSanitizer.php:98-117`, `app/Services/HtmlSanitizer.php:180-183` | Local authoritative | High |

## Files inspected in partial pass

```txt
app/Services/HtmlSanitizer.php
app/Services/AuthService.php
app/Services/LoginThrottleService.php
app/Middleware/AuthMiddleware.php
app/Middleware/RoleMiddleware.php
app/Middleware/SecurityHeadersMiddleware.php
app/Core/Request.php
app/Core/Session.php
routes/admin.php
routes/web.php
app/Controllers/Admin/AuthController.php
app/Controllers/Admin/PrestasiController.php
app/Controllers/Admin/PhotoGalleryController.php
app/Controllers/Admin/SettingsController.php
app/Controllers/Admin/TeamMemberController.php
app/Controllers/Admin/FeatureController.php
app/Controllers/Public/CommentController.php
app/Models/Feature.php
app/Models/TeamMember.php
package.json
```

## Risks & Unknowns

- Full controller/model pass was not yet completed.
- Dependency audit commands had not yet been run.
- No `composer.json` was found during the partial pass, so PHP dependency risk appears minimal, but runtime PHP extensions/server config still need environment verification.
- Admin browser verification was blocked by login; authenticated session or credentials are required for admin visual/admin-route verification.
- Some findings depend on deployment environment:
  - whether PHP execution is disabled globally in uploads
  - whether `session_secure` is true in production
  - whether HTTPS termination sets `HTTP_X_FORWARDED_PROTO`
  - whether `tbl_login_attempt` exists

## Implementation Readiness

**Needs verification** — the findings are actionable, but the audit was explicitly partial.

## Recommended Next Step

Run a complete custom PHP MVC security audit across controllers, models, routes, config, upload surfaces, dependencies, and deployment-sensitive assumptions; then write a final report under `docs/audits/`.
