# GenBI Frontend-Backend Integration Progress

Last updated: 2026-07-30 after SSR delivery/buildless migration planning

## Source Markdown Reviewed

- [x] `README.md` - current static HTML prototype pages and run command.
- [x] `FEATURE.md` - public and admin feature inventory, visual standards, static page map.
- [x] `PROBLEMS.md` - prioritized UI fixes, comment moderation, Prestasi CMS, token form, testing checklist.
- [x] `another-PROBLEM.md` - P0 news filter dropdown stacking bug and updated execution order.
- [x] `BLUEPRINTS.md` - pure PHP MVC backend architecture, routes, model mappings, admin/public integration.
- [x] `CONFIGS.md` - SEO crawlability, robots, sitemap, canonical, Open Graph, structured data.
- [x] `NEWS-SHARE.md` - news share preview, Open Graph/Twitter Card, canonical slug share behavior.

## Current Project State

- Static prototype still provides the rendered page bodies: `*.html`, `admin/*.html`, `assets/js/**/*.js`, `assets/css/**/*.css`.
- PHP MVC skeleton now exists with `public/index.php`, `bootstrap/app.php`, `app/Core`, `app/Controllers`, and `routes`.
- Environment/config foundation now exists with `.env.example`, `app/Config`, `app/Core/Env.php`, and PDO connection factory.
- Migration runner foundation now exists with `database/migrate.php`, `app/Core/MigrationRunner.php`, and blueprint-aligned migration files.
- News model foundation now exists with `app/Models/News.php`, JSON negotiation for `/news`, and legacy id redirect support.
- PHP public asset bridge now serves `/assets/*` through `public/index.php` during local PHP preview.
- Git repository is initialized with `.gitignore`; Markdown files and docs are intentionally ignored per request.
- News comment backend foundation now exists with model and public/admin JSON routes.
- Prestasi backend foundation now exists with `app/Models/Prestasi.php`, `app/Models/PrestasiToken.php`, public/admin controllers, and full CRUD + token routes.
- Asset paths fixed: `assets/` relocated to `public/assets/`; test/tool/config paths updated accordingly.
- SEO metadata system now exists with `app/Services/SeoService.php`, server-side meta injection in `StaticPageRenderer`, dynamic sitemap/feed XML routes, and `robots.txt`.
- All public pages now render with dynamic `<title>`, `<meta description>`, `<link canonical>`, Open Graph, Twitter Card, and RSS feed link tags.
- Admin authentication system now exists with session management, AuthService (login/logout/rate limit/account lock), CSRF protection, middleware pipeline in Router, and login page.
- All admin routes are now protected by AuthMiddleware; unauthenticated requests redirect to `/admin/login` or return 401 JSON.
- Blank page on back navigation (bfcache) bug is fixed with `pageshow` event listener.
- Login fix: AuthService now handles case-insensitive role/status, `id` column (not `user_id`), and graceful fallback when migration 008 columns don't exist yet.
- Phase B CSRF hardening complete: CsrfMiddleware applied to all protected admin POST routes; CSRF token injected into admin pages via `<meta name="csrf-token">`; frontend JS auto-includes `X-CSRF-TOKEN` header on mutating requests.
- Security headers added to all dynamic responses: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`.
- Admin topbar now includes logout button with CSRF-protected form.
- CLI password reset tool: `php tools/reset-password.php <email> <password>`.
- Existing frontend reads dummy data from `public/assets/js/data.js` through `window.GenBIData`.
- Existing package has build, serve, and test scripts.
- Backend target from plan is pure PHP MVC with routes such as `/news`, `/news/{slug}`, `/news/{slug}/comment`, `/prestasi`, `/team`, and admin routes.
- News share image metadata now resolves legacy relative upload filenames against existing files in `public/uploads`, including same-basename extension fallback such as `news-98.jpeg` -> `news-98.jpg`.
- Clean public routes now support `HEAD` requests by dispatching them through matching `GET` routes without a response body, so social/link validators do not receive false 404 results during preview checks.
- **SSR Phase 1 complete**: Public news list and detail pages now render initial HTML server-side via `ViewRenderer` and PHP templates in `app/Views/public/news/`. Static HTML files remain as fallback. Client-side JS detects SSR markup and skips duplicate rendering (news list) or progressively binds behavior (news detail share/comment).
- **SSR Phase 2 complete**: Admin news list, add, and edit pages now render initial HTML server-side via `ViewRenderer` and PHP templates in `app/Views/admin/news/`. Admin news list renders real rows from `tbl_news` with delete buttons. Admin news add/edit renders the full editor form shell with prefilled data for edit mode. JS hydrates Editor.js, image uploads, and form submission on SSR pages instead of rebuilding the DOM.
- **Backend Pagination complete**: All item-listing pages now paginate from the backend via `?page=N` URL params. Shared `Paginator` helper parses page/per_page, computes offset, and builds query strings preserving filters. No page dumps all rows into a single response.
- **SSR Phase 3 complete**: Public `/prestasi`, `/prestasi/{slug}`, `/team` and admin `/admin/team-member`, `/admin/prestasi` now render initial HTML server-side with paginated data. Pagination links work without JS. Filters preserved across page navigation.
- **SSR Phase 4 complete**: Public `/event` and `/event/{id}` now render initial HTML server-side with backend pagination (9/page, max 24). Event list includes search form, paginated `<a>` links, and progressive modal enhancement. Event detail page renders full content with breadcrumb, date/location info, banner image, and map embed. "Event" added to top navigation bar. "Lihat semua event" button added to homepage Agenda Utama section. JS detects SSR and binds modal behavior without rebuilding DOM.
- **SSR Phase 5 complete**: Admin Prestasi CMS now fully server-rendered. `/admin/prestasi` list has backend-driven search/filter (q, category, year, status) with paginated `<a>` links preserving filters. `/admin/prestasi-add` and `/admin/prestasi-edit?id=...` render SSR forms with prefilled data. Detail preview modal fetches JSON and shows item info with Edit action. JS detects SSR forms and hydrates (upload, submit, custom selects, member datalist) without rebuilding DOM. `Prestasi::allForAdmin()` and `countForAdmin()` support combined filter queries.

## Integration Strategy

- Preserve the static prototype behavior while adding a small frontend integration layer.
- Read backend route intent from `BLUEPRINTS.md` and map it to client API methods.
- Use backend data when available, then fall back to existing dummy data when running statically.
- Keep URL compatibility with current `*.html?id=...` pages until the PHP router exists.
- Add unit tests for integration utilities before marking implementation tasks complete.
- Do not mark a checkbox as complete until the related unit test command has passed.

## Priority Roadmap

- [x] P0: Add test harness and frontend API integration adapter.
- [x] P0: Fix modal root behavior for Team and Prestasi pages.
- [x] P0: Fix admin mobile sidebar scroll lock and Escape behavior.
- [x] P0: Fix news filter dropdown stacking and outside/Escape close behavior.
- [x] P1: Integrate news list/detail with backend-shaped API and static fallback.
- [x] P1: Integrate approved-only public comments and comment submission endpoint.
- [x] P1: Update admin comment page as moderation dashboard wired to backend-shaped API.
- [x] P1: Prepare clean public/admin URLs without `.html` or `.php` for backend handoff.
- [x] P1: Add local static route fallback so clean URLs do not resolve to the homepage during preview.
- [x] P1: Add PHP MVC entry point skeleton for cPanel `/public` web root and clean route rendering.
- [x] P1: Add PHP environment, config, and PDO database foundation without hard-coded secrets.
- [x] P1: Add pure PHP migration runner and initial blueprint-aligned migration files.
- [x] P1: Add PHP News model foundation and JSON route negotiation for frontend adapter.
- [x] P1: Fix PHP public asset loading and initialize Git ignore policy.
- [x] P1: Add PHP NewsComment model foundation and public/admin JSON endpoints.
- [x] P2: Fix broken asset/test/tool paths after `assets/` -> `public/assets/` relocation.
- [x] P2: Add PHP Prestasi model, PrestasiToken model, public/admin controllers, and full CRUD + token routes.
- [x] P2: Add SEO/canonical/share metadata path for PHP rendering handoff.
- [x] P3: Fix blank page on back navigation (bfcache issue).
- [x] P3: Add admin authentication with session, CSRF, middleware pipeline, and login page.
- [x] P3: Fix login (case-insensitive role/status, correct column names, graceful pre-migration fallback).
- [x] P3: Phase B CSRF hardening — CsrfMiddleware on all admin POST routes, meta tag injection, JS auto-header.
- [x] P3: Security headers on all dynamic responses.
- [x] P3: Admin logout button in topbar.
- [x] P3: CLI password reset tool.
- [x] P3: Resolve legacy news share image upload filename mismatches for Open Graph/Twitter Card URLs.
- [x] P3: Support `HEAD` requests for clean public routes used by link preview crawlers and validators.
- [x] P3: SSR Phase 1 — Add ViewRenderer foundation, shared layouts/partials, and migrate public `/news` and `/news/{slug}` to server-side rendering.
- [x] P3: SSR Phase 2 — Migrate admin `/admin/news`, `/admin/news-add`, and `/admin/news-edit` to server-side rendering with JS hydration.
- [x] P3: Backend Pagination — All listing pages paginate from backend via `?page=N`. Shared `Paginator` helper. Public news, prestasi, team; admin news, team, prestasi.
- [x] P3: SSR Phase 3 — Migrate public `/prestasi`, `/prestasi/{slug}`, `/team` and admin `/admin/team-member`, `/admin/prestasi` to server-side rendering with pagination.
- [x] P3: SSR Phase 4 — Migrate public `/event` and `/event/{id}` to server-side rendering with pagination. Add Event to top nav. Add event CTA to homepage.
- [x] P3: SSR Phase 5 — Complete admin Prestasi CMS SSR: list with filters/search/pagination, add/edit forms with hydration, detail preview modal.

## Current SSR Delivery Status

- [x] P0: PHP front controller is the default local server; the Node static server is explicitly isolated as `serve:static-fallback`.
- [x] P0: Runtime layouts use editable source JS/CSS, with no `dist`, minified stylesheet, or generated theme stylesheet dependency.
- [x] P1: Public and authenticated admin display routes render initial HTML on the server; JavaScript is progressive enhancement only.
- [ ] P1: Confirm production Apache/Nginx document root is `/public` and deploy the PHP runtime to the live domain.

## Working Checklist

### Task 1: Test Harness and API Adapter

- [x] Write unit tests for endpoint construction, dummy fallback, slug generation, and response normalization.
- [x] Run tests and confirm they fail before implementation where applicable.
- [x] Implement `assets/js/api.js` with backend-first/static-fallback data access.
- [x] Add `npm test` script using Node's built-in test runner.
- [x] Run `npm test` and confirm pass.
- [x] Update this checklist only after tests pass.

### Task 2: News Public Integration

- [x] Write unit tests for news normalization and lookup by `id`/`slug`.
- [x] Update `news.html` to load `assets/js/api.js` before page script.
- [x] Update `news-detail.html` to load `assets/js/api.js` before page script.
- [x] Update `assets/js/pages/news.js` to render API-backed news with fallback.
- [x] Update `assets/js/pages/news-detail.js` to render API-backed detail, related articles, and approved comments.
- [x] Run `npm test` before marking done.

### Task 3: Comments Integration

- [x] Write unit tests for approved-only comment filtering and submit payload construction.
- [x] Wire public comment form to `POST /news/{slug}/comment` with fallback toast behavior.
- [x] Ensure pending comments are not displayed publicly.
- [x] Run `npm test` before marking done.

### Task 4: P0 UI Stability

- [x] Write unit tests for reusable modal/sidebar state helpers where practical.
- [x] Move reusable modal behavior into `assets/js/lib/ui.js`.
- [x] Update Team and Prestasi modals to use fixed modal root, focus return, Escape, backdrop close, and body lock.
- [x] Update admin sidebar to lock body scroll, close on overlay/menu/Escape, and keep fixed mobile position.
- [x] Fix news dropdown z-index, outside click, Escape close, and focus state.
- [x] Run `npm test` before marking done.

### Task 5: Prestasi Integration

- [x] Write unit tests for Prestasi normalization and token status rules.
- [x] Update public Prestasi list/detail to use backend-shaped API with fallback.
- [x] Add admin Prestasi list/add/edit pages or route placeholders aligned with `PROBLEMS.md` and `BLUEPRINTS.md`.
- [x] Add token generation/history UI with static fallback.
- [x] Run `npm test` before marking done.

### Task 5: Admin Comment Moderation Integration

- [x] Write unit tests for admin comment normalization, stats, filtering, and action endpoint construction.
- [x] Add backend-shaped admin comment API methods with static fallback.
- [x] Update `admin/comment.html` to load the API adapter.
- [x] Update admin comment dashboard to use API-backed comments with search and status filters.
- [x] Wire approve, reject, and delete actions to backend-shaped endpoints with static fallback behavior.
- [x] Keep delete/reject behind custom confirmation modal.
- [x] Run `npm test` before marking done.

### Task 6: Clean URL Preparation

- [x] Add public, news detail, and admin URL helper tests.
- [x] Add clean URL helpers that keep direct `file://` static preview fallback.
- [x] Update public shell and page scripts to generate clean URLs over HTTP/HTTPS.
- [x] Update admin shell and generated CMS links to use clean admin paths.
- [x] Add report in `docs/report/` after implementation.
- [x] Run `npm test` before marking done.

### Task 7: Local Clean Route Preview Support

- [x] Cross-check clean route issue against route requirements in `BLUEPRINTS.md`, `CONFIGS.md`, and this progress tracker.
- [x] Identify local preview root fallback as the reason clean routes appeared to redirect/render as `/` before PHP routing exists.
- [x] Add static route resolver for clean public, news detail, and admin paths.
- [x] Replace Python static server script with a Node preview server that maps clean routes to existing HTML prototype files.
- [x] Keep production URL helpers aligned to extensionless backend routes while using static file URLs on local preview hosts.
- [x] Add route resolver tests and run smoke checks against the preview server.
- [x] Add report in `docs/report/` after implementation.
- [x] Run `npm test` before marking done.

### Task 8: PHP MVC Entry Point Skeleton

- [x] Cross-check `BLUEPRINTS.md`, `CONFIGS.md`, and this progress tracker for PHP entry point and route requirements.
- [x] Add `public/index.php` as the cPanel web-root entry point.
- [x] Add minimal `bootstrap/app.php`, `Router`, `Request`, `Response`, and static page renderer.
- [x] Add public route placeholders for `/`, `/about`, `/team`, `/prestasi`, `/contact`, `/news`, `/news/{slug}`, and `/news/id/{id}`.
- [x] Add admin route placeholders for `/admin`, `/admin/dashboard`, and `/admin/{page}` with `noindex` headers/meta.
- [x] Add `public/.htaccess` rewrite rules for Apache/cPanel.
- [x] Run PHP lint and clean route smoke tests.
- [x] Run `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 9: PHP Config and Database Foundation

- [x] Cross-check `BLUEPRINTS.md` database/config requirements and `docs/PROJECT_PROGRESS.md` before implementation.
- [x] Add `.env.example` matching blueprint configuration without real secrets.
- [x] Add environment loader and config classes for app, database, and security settings.
- [x] Add PDO connection factory with `PDO::ATTR_EMULATE_PREPARES => false`.
- [x] Add initial SQL migration file for the pure PHP `migrations` table.
- [x] Add PHP foundation verification for env/config behavior.
- [x] Run PHP lint, PHP foundation test, PHP route smoke test, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 10: PHP Migration Runner Foundation

- [x] Cross-check migration requirements in `BLUEPRINTS.md` and `docs/PROJECT_PROGRESS.md` before implementation.
- [x] Add `app/Core/MigrationRunner.php` for sorted pending migration detection, batch tracking, and transactional execution.
- [x] Add `database/migrate.php` CLI command.
- [x] Convert migration-table SQL placeholder into migration runner responsibility.
- [x] Add blueprint-aligned PHP migration files for news columns, news comments, prestasi, prestasi tokens, submissions, team org fields, audit log, and user security.
- [x] Add PHP migration runner verification test.
- [x] Run PHP lint, migration runner test, PHP foundation test, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 11: PHP News Model Foundation

- [x] Cross-check News model mapping and public route requirements in `BLUEPRINTS.md`, `NEWS-SHARE.md`, and integration plan.
- [x] Add request JSON negotiation and JSON response support.
- [x] Add `Slugger` utility aligned with frontend slug fallback behavior.
- [x] Add `app/Models/News.php` for `tbl_news` list, detail by slug/id, related query, view increment, and row mapping.
- [x] Wire `/news` and `/news/{slug}` to return JSON when requested by the frontend adapter while keeping HTML rendering for normal browser visits.
- [x] Improve `/news/id/{id}` legacy redirect to use database slug when available and static fallback when unavailable.
- [x] Add PHP News model mapping test.
- [x] Run PHP lint, News model test, PHP route smoke test, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 12: PHP Asset Bridge, Git Init, and News Comment Backend

- [x] Diagnose missing CSS/JS when serving with PHP public root.
- [x] Add `/assets/*` bridge in `public/index.php` for local PHP server fallback.
- [x] Add `serve:php` script that uses `public/index.php` as the router script.
- [x] Initialize Git repository.
- [x] Add `.gitignore` with Markdown/docs ignored per request, plus secrets, dependencies, logs, and editor noise.
- [x] Cross-check NewsComment routes/model requirements in `BLUEPRINTS.md` and frontend API adapter.
- [x] Add `app/Models/NewsComment.php` for approved public comments, admin list, create, approve, reject, and delete flows.
- [x] Add public comment controller for `/news/{slug}/comments` and `POST /news/{slug}/comment`.
- [x] Add admin comment controller for `/admin/news-comments` and moderation actions.
- [x] Add PHP NewsComment mapping test.
- [x] Run PHP lint, PHP model tests, asset/comment route smoke tests, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 13: Prestasi Backend Foundation and Path Fixes

- [x] Diagnose `npm test` failure caused by `assets/` -> `public/assets/` relocation.
- [x] Fix `tests/api-core.test.js` require paths to `../public/assets/js/api-core.js` and `../public/assets/js/lib/ui.js`.
- [x] Fix `tools/static-server.js` require path to `../public/assets/js/api-core.js`.
- [x] Fix `tailwind.config.js` content scan to `./public/assets/js/**/*.js`.
- [x] Fix `public/index.php` asset bridge to use `__DIR__` (assets now in `public/assets/`).
- [x] Add `app/Models/Prestasi.php` with mapRow, published, findBySlug, findById, all, create, update, softDelete.
- [x] Add `app/Models/PrestasiToken.php` with mapRow, generate, validateToken, markUsed, revoke, all.
- [x] Add `app/Controllers/Public/PrestasiController.php` with index, show, submissionForm, submitWithToken.
- [x] Add `app/Controllers/Admin/PrestasiController.php` with index, show, store, update, delete.
- [x] Add `app/Controllers/Admin/PrestasiTokenController.php` with index, generate, revoke.
- [x] Wire Prestasi models and controllers in `bootstrap/app.php`.
- [x] Add public routes: `/prestasi`, `/prestasi/{slug}`, `/prestasi/submit/{token}` (GET+POST).
- [x] Add admin routes: `/admin/prestasi` (GET+POST), `/admin/prestasi/{id}`, `/admin/prestasi/{id}/update`, `/admin/prestasi/{id}/delete`, `/admin/prestasi-tokens` (GET+POST), `/admin/prestasi-tokens/{id}/revoke`.
- [x] Add `tests/php/PrestasiModelTest.php` for Prestasi and PrestasiToken mapRow.
- [x] Run PHP lint, all PHP model tests, route smoke tests, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 14: SEO/Canonical/Share Metadata and Sitemap/Feed

- [x] Add `xml()` method to `app/Core/Response.php`.
- [x] Create `app/Services/SeoConfig.php` with static page meta configuration.
- [x] Create `app/Services/SeoService.php` with `forPage()`, `forNews()`, `forPrestasi()`, `absoluteUrl()`, `imageUrl()`, `cleanDescription()`, `isoDate()`, and `renderMetaBlock()`.
- [x] Extend `app/Core/StaticPageRenderer.php` with `meta` option for full meta block injection (removes duplicate title/description).
- [x] Update `PageController` to inject page-level SEO meta for all static pages.
- [x] Update `NewsController` to inject news-specific SEO meta (OG article, Twitter Card) for HTML detail rendering.
- [x] Update `PrestasiController` to inject prestasi-specific SEO meta for detail pages and `noindex` for token forms.
- [x] Fix `PrestasiController` bug: was calling `$this->renderer->render($response, ...)` with wrong argument order.
- [x] Create `app/Controllers/Public/SitemapController.php` with `index()`, `pages()`, `news()`, `prestasi()` XML generators.
- [x] Create `app/Controllers/Public/FeedController.php` with RSS 2.0 news feed.
- [x] Create `public/robots.txt` per CONFIGS.md specification.
- [x] Add static file serving in `public/index.php` for `robots.txt`, `favicon.ico`, `manifest.webmanifest`.
- [x] Wire SitemapController and FeedController in `bootstrap/app.php`.
- [x] Add routes: `/sitemap.xml`, `/sitemap-pages.xml`, `/sitemap-news.xml`, `/sitemap-prestasi.xml`, `/feed.xml`.
- [x] Create `tests/php/SeoServiceTest.php` with comprehensive assertions.
- [x] Run PHP lint (12 files), all PHP tests (6 suites), route smoke tests, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 15: Blank Page Fix and Admin Authentication

- [x] Diagnose blank page on browser back navigation (bfcache + CSS opacity + no pageshow listener).
- [x] Add `pageshow` event listener in `public/assets/js/app.js` to restore visibility on bfcache restore.
- [x] Add `pageshow` event listener in `public/assets/js/admin/admin.js` for admin pages.
- [x] Create `app/Core/Session.php` with start, get, set, remove, has, regenerate, destroy, flash, getFlash.
- [x] Create `app/Core/Middleware.php` interface.
- [x] Add middleware pipeline support to `app/Core/Router.php` with `group()` method.
- [x] Add `session()` helper to `app/Core/Request.php`.
- [x] Create `app/Services/CsrfService.php` with token generation, validation, regeneration, and hidden input.
- [x] Create `app/Services/AuthService.php` with attempt, check, user, logout, rate limiting, account locking, legacy MD5 fallback, and password rehash.
- [x] Create `app/Middleware/AuthMiddleware.php` — redirects to login or returns 401 JSON.
- [x] Create `app/Middleware/CsrfMiddleware.php` — validates CSRF token from POST body or header.
- [x] Create `app/Controllers/Admin/AuthController.php` with showLogin, login, logout.
- [x] Initialize session in `bootstrap/app.php` (skipped in CLI mode for tests).
- [x] Add auth routes: `GET /admin/login`, `POST /admin/login`, `POST /admin/logout`.
- [x] Wrap all protected admin routes in `$router->group([$authMiddleware], ...)`.
- [x] Create `tests/php/AuthServiceTest.php` for role validation, CSRF, and no-DB fallback.
- [x] Run PHP lint (12 files), all PHP tests (7 suites), auth route smoke tests, and `npm test` before marking done.
- [x] Add report in `docs/report/` after implementation.

### Task 17: SSR Phase 1 — Public News List and Detail

- [x] Audit `tbl_news` schema against SSR requirements (slug, contributors, status, timestamps, indexes).
- [x] Create `app/Core/ViewRenderer.php` with render(), renderWithLayout(), safe escaping helpers.
- [x] Create `tests/php/ViewRendererTest.php` with escaping, layout, missing view, CSRF token tests.
- [x] Wire ViewRenderer into `bootstrap/app.php` and pass to NewsController.
- [x] Create `app/Views/layouts/public.php` with shell placeholders for app.js rendering.
- [x] Create `app/Views/layouts/admin.php` with CSRF meta tag injection.
- [x] Create minimal partials: public-header.php, public-footer.php, admin-sidebar.php, admin-topbar.php.
- [x] Create `app/Views/public/news/index.php` with server-rendered article cards marked `data-ssr="true"`.
- [x] Update `NewsController::index()` to render SSR view when ViewRenderer exists, fallback to static otherwise.
- [x] Update `public/assets/js/pages/news.js` to detect SSR and skip duplicate rendering.
- [x] Create `app/Views/public/news/show.php` with hero, article body, contributors, share/comment sections.
- [x] Update `NewsController::show()` to render SSR view when ViewRenderer exists, return 404 status when item missing.
- [x] Update `public/assets/js/pages/news-detail.js` to progressively bind share/comment behavior on SSR pages.
- [x] Run PHP lint, all PHP tests (9 suites), JS tests (20 tests), and smoke tests before marking done.
- [x] Verify `/news` returns 200 with SSR marker and article cards.
- [x] Verify `/news/{slug}` returns 200 with SSR marker, NewsArticle JSON-LD, hero section.
- [x] Verify HEAD requests still work correctly.
- [x] Update `docs/PROJECT_PROGRESS.md` with SSR Phase 1 completion.

### Task 18: SSR Phase 2 — Admin News List, Add, and Edit

- [x] Wire `AdminPageController` with `ViewRenderer` and `News` model via constructor injection.
- [x] Update `bootstrap/app.php` to pass `$viewRenderer` and `$newsModel` to `AdminPageController`.
- [x] Create `app/Views/admin/news/index.php` with server-rendered news table and delete buttons.
- [x] Add SSR branch in `AdminPageController::show()` for `news` page.
- [x] Refactor `cms.js` `renderNewsList()` to detect SSR and only bind delete behavior.
- [x] Extend `bindNewsDeleteButtons()` to match both CSR and SSR delete button selectors.
- [x] Create `app/Views/admin/news/form.php` with full editor form shell matching existing JS selectors.
- [x] Add SSR branches in `AdminPageController` for `news-add` and `news-edit` pages.
- [x] Load categories from `News::categories()` for SSR form category select.
- [x] Prefill edit form with item data from `News::findById()` when editing.
- [x] Include Editor.js CDN scripts in admin layout for add/edit pages.
- [x] Add `bindNewsFormSubmit()` function to `cms.js` for reusable form submission.
- [x] Refactor `renderNewsEditor()` to detect SSR form and hydrate instead of rebuilding.
- [x] Run PHP lint, all PHP tests (9 suites), JS syntax check, and JS tests (20 tests).
- [x] Verify SSR rendering for `/admin/news`, `/admin/news-add`, `/admin/news-edit?id=100`.
- [x] Verify static fallback still works for non-news admin pages.
- [x] Update `docs/PROJECT_PROGRESS.md` with SSR Phase 2 completion.

### Task 19: SSR Delivery Cutover and Buildless Asset Serving (Complete in code; production cutover pending)

- [x] Make the PHP server (`php -S ... -t public public/index.php`) the default local SSR preview; isolate the Node server as `serve:static-fallback`.
- [ ] Verify the production document root is `/public`, rewrite rules are active, and PHP 8.2+ with `pdo_mysql` is enabled (requires hosting access).
- [x] Add `/news/view/{id}` alongside `/news/id/{id}` redirect handling to canonical `/news/{slug}`.
- [x] Add SSR markers and local HTTP/browser smoke checks for public SSR pages; article-detail redirect/content smoke needs a seeded published record.
- [x] Replace PHP runtime `/assets/js/dist/...` references with direct source files and preserve hydration guards.
- [x] Replace `styles.min.css` and generated `theme.css` runtime dependencies with `styles.css` and PHP inline theme tokens.
- [x] Serve editable CSS from the root source asset first and mark JS/CSS `no-cache, must-revalidate` so File Manager changes become visible without a bundle upload.
- [x] Keep the Tailwind utility stylesheet as a baseline; runtime code no longer generates or loads a build artifact, and custom visual changes belong in editable `assets/css/styles.css`.
- [x] Migrate SSR priority routes: Event CMS, Team add/edit, comments, category, photo, Prestasi token form/list, and dashboard.
- [x] Render FAQ, social media, slider, language, page, and Why Choose prototype screens server-side; their persistence model remains a separate backend feature.
- [x] Run `npm test`, PHP lint, JS syntax checks, HTTP response checks, and browser snapshots without executing `npm run build`.

### Task 20: SSR Regression Follow-up

- [x] Restore dashboard navigation initialization for `/admin` and `/admin/dashboard`.
- [x] Preserve a valid SSR Prestasi submission form at `/prestasi/submit/{token}` instead of replacing it with a client-side token error.
- [x] Port the existing Laravel public Feature layout to a PHP SSR page and register `/feature`.
- [x] Audit and remove only files proven unused after the SSR cutover; preserve active Laravel work and unrelated local changes.
- [x] Review the final SSR diff and commit all verified implementation changes in scope.

## Test Log

- 2026-07-30: SSR regression follow-up passed: Playwright confirmed dashboard navigation/topbar rendering with zero console errors; `/feature` rendered six published programs with zero console errors; the Prestasi browser smoke test kept the SSR form intact after loading its source script. PHP lint and `npm test` (26 tests) passed without running a build.
- 2026-07-30: SSR/buildless cutover verification passed: `npm test` (26 tests), PHP lint for changed controllers/views/routes, JS syntax checks, PHP HTTP smoke for public `/news`, `/about`, and source assets, plus Playwright snapshots for `/news` and `/about` with zero console errors. No `npm run build` was used.
- 2026-05-06: No test harness existed at initial audit.
- 2026-05-06: `npm test` passed after API adapter, News integration, comment integration, and Prestasi normalization tests. Result: 6 passing tests.
- 2026-05-06: `npm test` passed after P0 UI stability changes. Result: 8 passing tests.
- 2026-05-06: `npm run build:css` could not complete because `tailwindcss` was missing from `node_modules/.bin`; `npm install` later failed with `ENOTEMPTY` while renaming `node_modules/autoprefixer`.
- 2026-05-06: `npm test` failed as expected after adding admin comment moderation tests before implementation. Result: 8 passing, 4 failing.
- 2026-05-06: `npm test` passed after admin comment moderation integration. Result: 12 passing tests.
- 2026-05-06: `npm run build:css` still could not complete because `tailwindcss` is missing from `node_modules/.bin`.
- 2026-05-06: `npm test` passed after clean URL preparation. Result: 16 passing tests.
- 2026-05-06: `npm test` passed after local clean route preview support. Result: 17 passing tests.
- 2026-05-06: Local preview smoke test passed for `/`, `/news`, `/about`, `/team`, `/prestasi`, `/contact`, `/news/example-slug-1`, and `/admin/comment`.
- 2026-05-06: PHP lint passed for 11 new PHP MVC skeleton files.
- 2026-05-06: PHP route smoke test passed for `/`, `/news`, `/about`, `/team`, `/prestasi`, `/contact`, `/news/example-slug-1`, `/admin`, `/admin/dashboard`, and `/admin/comment`; `/news/id/100` returns 301.
- 2026-05-06: `npm test` passed after PHP MVC entry point skeleton. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for config/database foundation files and `tests/php/FoundationTest.php`.
- 2026-05-06: PHP foundation test passed for env loading and config normalization.
- 2026-05-06: PHP route smoke test still passed after config/database foundation.
- 2026-05-06: `npm test` passed after PHP config and database foundation. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for migration runner, CLI migration command, migration runner test, and all migration files.
- 2026-05-06: PHP migration runner test passed for pending detection, ordered execution, batch recording, and idempotent no-pending state.
- 2026-05-06: PHP foundation test passed after migration runner foundation.
- 2026-05-06: `npm test` passed after PHP migration runner foundation. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for News model foundation files.
- 2026-05-06: PHP News model mapping test passed.
- 2026-05-06: PHP News route smoke test passed for `/news`, `/news/{slug}`, `/news/id/{id}`, and `/news` with `Accept: application/json`.
- 2026-05-06: `npm test` passed after PHP News model foundation. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for asset bridge and NewsComment backend foundation files.
- 2026-05-06: PHP NewsComment model mapping test passed.
- 2026-05-06: PHP asset smoke test passed for `/assets/css/tailwind.css`, `/assets/css/styles.css`, `/assets/js/app.js`, and `/assets/js/api-core.js` when using the PHP router script.
- 2026-05-06: PHP comment route smoke test passed for `/news/{slug}/comments`, `POST /news/{slug}/comment`, and `/admin/news-comments` fallback behavior.
- 2026-05-06: `npm test` passed after asset bridge, Git init, and News comments foundation. Result: 17 passing tests.
- 2026-05-06: Fixed broken `tests/api-core.test.js` and `tools/static-server.js` require paths after `assets/` -> `public/assets/` relocation.
- 2026-05-06: Fixed `tailwind.config.js` content scan path to `./public/assets/js/**/*.js`.
- 2026-05-06: Fixed PHP asset bridge in `public/index.php` to use `__DIR__` instead of `dirname(__DIR__)`.
- 2026-05-06: `npm test` passed after path fixes. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for Prestasi model, PrestasiToken model, public/admin controllers, bootstrap, and routes.
- 2026-05-06: PHP Prestasi model mapping test passed (Prestasi + PrestasiToken mapRow).
- 2026-05-06: PHP route smoke test passed for `/prestasi` (200), `/prestasi/{slug}` (404 no DB), `/prestasi/submit/{token}` (403 no DB), `/admin/prestasi` (200), `/admin/prestasi-tokens` (200), `/admin/prestasi/{id}` (404 no DB).
- 2026-05-06: All existing PHP tests still pass (Foundation, MigrationRunner, NewsModel, NewsComment).
- 2026-05-06: `npm test` passed after Prestasi backend foundation. Result: 17 passing tests.
- 2026-05-06: PHP lint passed for SEO service, config, controllers, sitemap, feed, and renderer files (12 files).
- 2026-05-06: PHP SeoService test passed (absoluteUrl, imageUrl, cleanDescription, isoDate, forPage, forNews, forPrestasi, renderMetaBlock).
- 2026-05-06: All existing PHP tests still pass (Foundation, MigrationRunner, NewsModel, NewsComment, Prestasi).
- 2026-05-06: `npm test` passed after SEO implementation. Result: 17 passing tests.
- 2026-05-06: Route smoke test passed for `/sitemap.xml`, `/sitemap-pages.xml`, `/sitemap-news.xml`, `/sitemap-prestasi.xml`, `/feed.xml`, `/robots.txt` (all 200).
- 2026-05-06: SEO meta injection verified: homepage has dynamic `<title>`, `<link canonical>`, `og:title`, `twitter:card`, RSS link. About page has correct title. Token form has `noindex`.
- 2026-05-06: PHP lint passed for auth system files (12 files).
- 2026-05-06: PHP AuthService test passed (role validation, CSRF token generation/validation/regeneration, no-DB fallback).
- 2026-05-06: All existing PHP tests still pass (Foundation, MigrationRunner, NewsModel, NewsComment, Prestasi, SeoService).
- 2026-05-06: `npm test` passed after auth implementation. Result: 17 passing tests.
- 2026-05-06: Auth route smoke test passed: `/admin/login` (200 with CSRF), `/admin/dashboard` (302 redirect), `/admin/prestasi` JSON (401), public routes unaffected.
- 2026-05-06: Login fix verified: `genbi@gmail.com` with role `Admin` (capitalized) and status `Active` now authenticates correctly.
- 2026-05-06: Phase B integration test passed: login (CSRF 64-char), dashboard (200 + CSRF meta), POST without CSRF (403), POST with CSRF (passes), security headers present, logout works, public routes unaffected.
- 2026-05-07: PHP News model mapping test passed after adding same-basename upload extension fallback for legacy news images.
- 2026-05-07: All PHP regression tests passed after news image filename resolution.
- 2026-05-07: `npm test` passed after news image filename resolution. Result: 20 passing tests.
- 2026-05-07: PHP router HEAD request test passed; `/news/{slug}` HEAD requests now match GET routes and return an empty 200 response instead of route-level 404.
- 2026-05-07: All PHP regression tests and `npm test` passed after HEAD request handling. JS result: 20 passing tests.
- 2026-05-07: SSR Phase 1 complete: Added ViewRenderer foundation with tests, shared public/admin layouts and partials, migrated public `/news` and `/news/{slug}` to server-side rendering. All 9 PHP tests and 20 JS tests pass. Smoke tests confirm SSR marker, article cards, NewsArticle JSON-LD, and HEAD request support.
- 2026-05-07: SSR Phase 2 complete: Migrated admin `/admin/news`, `/admin/news-add`, `/admin/news-edit` to server-side rendering. All 9 PHP tests and 20 JS tests pass. SSR verification confirms: news list with delete buttons, add form with empty fields, edit form with prefilled data, CSRF meta, noindex, Editor.js CDN scripts, and static fallback for non-news admin pages.
- 2026-05-07: Backend Pagination complete: Added shared `Paginator` helper (10 PHP tests pass). Public `/news` paginated (12/page, max 24). Admin `/admin/news` paginated (25/page, max 100). Public `/prestasi` SSR with pagination (12/page). Public `/team` SSR with pagination (12/page, max 48). Admin team/prestasi URL pagination synced. Admin `/admin/team-member` and `/admin/prestasi` SSR with pagination. All 10 PHP tests and 20 JS tests pass.

## Progress Log

- 2026-05-06: Read all markdown files and created this tracker from the documented plan priorities.
- 2026-05-06: Added `assets/js/api-core.js`, `assets/js/api.js`, `tests/api-core.test.js`, and `npm test`.
- 2026-05-06: Updated News, News Detail, and Prestasi public pages to load the integration adapter and use backend-first/static-fallback data.
- 2026-05-06: Implemented P0 UI stability phase: reusable modal controller, Team/Prestasi focus-safe fixed modals, admin mobile sidebar body lock/Escape close, and news dropdown stacking/outside/Escape behavior.
- 2026-05-06: Added `docs/ADMIN_COMMENT_MODERATION_PLAN.md` and implemented admin comment moderation integration with backend-shaped API fallback, dashboard stats, search/status filtering, approve/reject/delete actions, and tests.
- 2026-05-06: Prepared clean public/admin URL generation without `.html` or `.php`, while preserving static direct-file fallback for `file://` previews.
- 2026-05-06: Added local clean route preview support through `tools/static-server.js` and updated `npm run serve` so clean routes map to the correct static prototype pages before PHP routing exists.
- 2026-05-06: Added PHP MVC entry point skeleton with `public/index.php`, custom router, public/admin route placeholders, Apache rewrite rules, and static prototype rendering through clean routes.
- 2026-05-06: Added PHP environment/config/database foundation with `.env.example`, config classes, PDO factory, and initial migrations table SQL.
- 2026-05-06: Added pure PHP migration runner, CLI command, migration runner test, and blueprint-aligned schema migration files.
- 2026-05-06: Added PHP News model foundation with database-backed JSON negotiation for frontend API calls and improved legacy id redirect behavior.
- 2026-05-06: Fixed PHP-served assets, initialized Git with Markdown/docs ignored, and added NewsComment backend foundation routes for public comments and admin moderation.
- 2026-05-06: Fixed broken JS test/tool/config paths after `assets/` -> `public/assets/` relocation; added Prestasi + PrestasiToken PHP models, public/admin controllers, full CRUD routes, and token system foundation.
- 2026-05-06: Added SEO/canonical/share metadata system: SeoService, SeoConfig, meta injection in StaticPageRenderer, SitemapController, FeedController, robots.txt, and updated all public controllers to inject dynamic meta tags.
- 2026-05-06: Fixed blank page on back navigation by adding `pageshow` event listener for bfcache restoration in `app.js` and `admin.js`.
- 2026-05-06: Added admin authentication system: Session management, AuthService (login/logout/rate limit/account lock/legacy MD5 migration), CsrfService, AuthMiddleware, CsrfMiddleware, middleware pipeline in Router, AuthController with login page, and protected all admin routes.
- 2026-05-06: Fixed login: case-insensitive role/status matching, `id` column support, graceful `hasColumn()` check for pre-migration DB, password reset to bcrypt.
- 2026-05-06: Phase B CSRF hardening: CsrfMiddleware added to admin route group, CSRF meta tag injected into admin pages, frontend JS auto-includes X-CSRF-TOKEN header, security headers on all responses, logout button in admin topbar, CLI password reset tool.
- 2026-05-07: Fixed news share image URL generation for legacy DB rows that reference an upload filename with the wrong extension by resolving the actual file in `public/uploads` before emitting `/uploads/...` URLs.
- 2026-05-07: Added HEAD request support to the router/response layer so clean news URLs return successful headers for validators and crawlers without emitting HTML bodies.
- 2026-05-07: SSR Phase 1 implementation: Added `app/Core/ViewRenderer.php` for safe PHP template rendering with escaping helpers. Created shared layouts in `app/Views/layouts/` (public.php keeps shell placeholders for app.js, admin.php includes CSRF meta). Created news views in `app/Views/public/news/` for list and detail pages. Updated `NewsController` to render SSR when ViewRenderer exists, falling back to StaticPageRenderer otherwise. Updated `news.js` to detect SSR and skip duplicate rendering. Updated `news-detail.js` to progressively bind share/comment behavior on SSR pages. Static HTML files remain as fallback.
- 2026-05-07: SSR Phase 2 implementation: Wired `AdminPageController` with `ViewRenderer` and `News` model. Created `app/Views/admin/news/index.php` for server-rendered news list with delete buttons. Created `app/Views/admin/news/form.php` for server-rendered editor form shell matching existing `cms.js` selectors. Added SSR branches in `AdminPageController::show()` for `news`, `news-add`, and `news-edit` pages. Refactored `cms.js` to detect SSR markup and hydrate (Editor.js, uploads, form submit) instead of rebuilding. Added `bindNewsFormSubmit()` for reusable form submission. Extended `bindNewsDeleteButtons()` to match both CSR and SSR selectors. Static HTML files remain as fallback for non-news admin pages.
- 2026-05-07: Backend Pagination implementation: Created `app/Core/Paginator.php` with resolve/totalPages/meta/buildQuery helpers and `tests/php/PaginatorTest.php`. Updated `NewsController` SSR to paginate (12/page). Updated `AdminPageController` news SSR to paginate (25/page). Added `Prestasi::countPublished()` and `Prestasi::countAll()`. Created `app/Views/public/prestasi/index.php` and `show.php` for SSR. Created `app/Views/public/team/index.php` for SSR with filters. Injected `ViewRenderer` into `PrestasiController` and `TeamController`. Updated `prestasi.js` and `team.js` for SSR detection. Updated admin `cms.js` team list to hydrate/sync URL state. Updated admin prestasi list to fetch by page from backend. Created `app/Views/admin/team/index.php` and `app/Views/admin/prestasi/index.php` for SSR. Injected `TeamMember` and `Prestasi` into `AdminPageController`. Added `bindTeamDeleteButtons()` and extended `bindPrestasiDeleteButtons()` for SSR selectors.
- 2026-05-07: SSR Phase 4 implementation: Added "Event" to `navItems` in `data.js` for top navigation bar. Added "Lihat semua event" CTA button to homepage Agenda Utama section in `fallbacks/index.html`. Updated `EventController` to accept `ViewRenderer` and use `Paginator` for SSR (9/page, max 24). Created `app/Views/public/event/index.php` for server-rendered event list with search form, paginated `<a>` links, and event cards with detail links. Created `app/Views/public/event/show.php` for server-rendered event detail with breadcrumb, date/location, banner, content, map embed, and back link. Updated `event.js` to detect SSR markup and progressively bind modal behavior (fetch JSON + open modal) without rebuilding DOM; CSR fallback preserved for `fallbacks/event.html`. Updated `bootstrap/app.php` to pass `$viewRenderer` to `EventController`. All 10 PHP tests and 20 JS tests pass.
- 2026-05-07: SSR Phase 5 implementation: Added `Prestasi::allForAdmin()` and `countForAdmin()` with combined filter support (q, category, year, status) using `applyAdminFilters()` helper. Updated `Admin\PrestasiController::index()` to read filter query params and use new methods. Updated `AdminPageController::renderAdminPrestasiSsr()` to accept page param and handle `prestasi`, `prestasi-add`, and `prestasi-edit` SSR branches. Enhanced `app/Views/admin/prestasi/index.php` with search input, category/status filter selects, per-page selector, image thumbnails, and Detail button per row. Created `app/Views/admin/prestasi/form.php` for SSR add/edit form matching `cms.js` selectors with `data-ssr="true"`, `data-edit`, and `data-item-id` attributes. Updated `cms.js`: added `prestasi-add` route, SSR detection in `renderPrestasiEditor()` for form hydration (member datalist, upload, custom selects, submit), extracted `bindPrestasiFormSubmit()` for reuse, added `bindPrestasiDetailButtons()` for JSON-fetched detail preview modal with Edit action, and bound search Enter key on SSR list. All 10 PHP tests and 20 JS tests pass.
