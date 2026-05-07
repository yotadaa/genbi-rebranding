# AGENTS.md

## Project Direction

- Current state: PHP MVC with SSR views in `app/Views/`, static HTML fallbacks in `fallbacks/*.html` and `fallbacks/admin/*.html`, JS in `public/assets/js/**/*.js`, and CSS in `public/assets/css/**/*.css`.
- PHP MVC skeleton has started with `public/index.php`, `bootstrap/app.php`, `app/Core`, `app/Controllers`, and `routes`; it currently renders the existing static prototype through clean routes.
- Target state from `BLUEPRINTS.md`: pure PHP 8.2+ MVC CMS, no Laravel, no CodeIgniter, no heavy framework; Composer is allowed only for small libraries.
- Keep the planned backend aligned to `app/`, `bootstrap/`, `database/`, `public/`, `routes/`, and `storage/` structure from `BLUEPRINTS.md` when PHP work starts.
- Domain web root must eventually point to `/public`, not the project root.
- Database target is existing `genc1357_genbijambi`; never hard-code database credentials or commit `.env` secrets.

## Commands

- Install dependencies: `npm install`.
- Run unit tests: `npm test`.
- Build Tailwind output after class/style changes: `npm run build:css`.
- Watch Tailwind during manual UI work: `npm run watch:css`.
- Serve static prototype: `npm run serve`, then open `http://127.0.0.1:5173`.
- Notify the user when requested or when work is complete: `wp --contact 62895703051945 --message "your-message-content"`.

## Source Of Truth

- `BLUEPRINTS.md`: backend architecture, database migrations, routes, models, security, deployment, and SEO blueprint.
- `PROBLEMS.md`: main issue plan and acceptance criteria.
- `another-PROBLEM.md`: news filter dropdown P0 bug and acceptance criteria.
- `FEATURE.md`: public/admin feature inventory, static page map, visual standards, and dummy-data expectations.
- `CONFIGS.md`: crawlability, sitemap, canonical, noindex, structured data, RSS, and URL policy.
- `NEWS-SHARE.md`: news detail Open Graph, Twitter Card, canonical share URL, and share-button requirements.
- `docs/PROJECT_PROGRESS.md`: integration progress tracker; update checkboxes only after related tests pass.
- `docs/FRONTEND_BACKEND_INTEGRATION_PLAN.md`: compact frontend/backend integration plan.

## Current Frontend Conventions

- `assets/js/data.js` exposes static fallback data as `window.GenBIData`.
- New integration code should use `window.GenBIAPI` from `assets/js/api.js`, not read dummy data directly.
- Keep normalization and testable mapping in `assets/js/api-core.js`; it is intentionally CommonJS-compatible for Node tests and browser-compatible for pages.
- Public shell rendering lives in `assets/js/app.js`; admin shell rendering lives in `assets/js/admin/admin.js`; admin CMS page bodies mostly live in `assets/js/admin/cms.js`.
- Preserve static URLs such as `news-detail.html?slug=...&id=...` while the PHP router does not exist yet.

## Backend Alignment Rules

- Use custom routing through `public/index.php -> Router -> Middleware -> Controller -> Model -> Database -> View -> Response`.
- Use PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false`.
- Keep controllers thin; long queries belong in models and business logic belongs in services.
- Use PHP sessions with strict security settings for admin auth.
- Require CSRF protection for every POST, PUT, PATCH, and DELETE form/action.
- Validate and sanitize all backend input; do not trust hidden inputs, select values, uploaded filenames, browser MIME values, editor HTML, slugs, or roles from forms.
- Treat every backend input as hostile by default, including route params, query strings, form fields, JSON bodies, cookies, session-derived identifiers, uploaded files, and headers; validate allowed shape/range/enum first, normalize values, sanitize only for the target sink, and reject invalid requests with safe errors.
- Do not use GET for destructive actions; all deletes must use POST with CSRF and custom confirmation modal.
- Roles should be whitelisted: `superadmin`, `admin`, `editor`, `moderator`.

## Database And Model Rules

- For this integration phase, use legacy `tbl_*` tables as the CMS source of truth; do not delete `news`, `teams`, or `users` modern tables.
- Extend `tbl_news` with `slug`, contributor fields, `content_json`, status, published timestamps, and soft-delete fields per `BLUEPRINTS.md`.
- News comments must use new `tbl_news_comment`; do not reuse `tbl_comment` because it is for Facebook comment configuration.
- Prestasi data must use new `tbl_prestasi` with slug, category, year, member/institution, image, status, SEO fields, and soft-delete fields.
- One-time prestasi submission tokens must use `tbl_prestasi_submission_token`; store only `hash('sha256', $plainToken)`, show plain token only once, and mark token used after submit.
- Add audit logging for important admin actions.

## Public Routes And URLs

- Final public URL policy: `/news`, `/news/{slug}`, `/prestasi`, `/prestasi/{slug}`, `/event`, `/event/{slug}` or `/event/{id}`, and `/team`.
- Legacy news ID URLs must redirect permanently to slug URLs, e.g. `/news/id/{id}` -> `/news/{slug}`.
- New public news URLs must use slug, not ID only.
- Draft, archived, and deleted content must not appear publicly.
- Public comments must show approved comments only; pending comments are admin-only.

## Admin Feature Priorities

- Current safest execution order from `PROBLEMS.md` and `another-PROBLEM.md`: fix modal root, fix admin mobile sidebar, fix news dropdown stacking, improve news detail share/comment, build admin comment moderation, build Prestasi CMS, then build one-time token form.
- Modal root must live before `</body>` as `<div id="modal-root"></div>` and fixed modals must be outside scroll containers.
- Modals must move focus into the dialog, trap focus while open, close on Escape/backdrop/close button, and restore focus.
- Admin mobile sidebar must be fixed, scrollable, above overlay, close on Escape, and lock body scroll while open.
- News filter dropdown must not be clipped or hidden by cards; it must close on outside click and Escape and keep clear focus state.
- All admin delete flows must use custom confirmation modals.

## SEO And Share Requirements

- Admin, API, search, and private token pages must be `noindex` or protected; do not rely on `robots.txt` to hide sensitive pages.
- Public important pages should be indexable: home, about, team, prestasi, news list/detail, event list/detail, and contact.
- Generate dynamic sitemap/feed routes from the PHP backend: `/sitemap.xml`, `/sitemap-pages.xml`, `/sitemap-news.xml`, `/sitemap-events.xml`, `/sitemap-prestasi.xml`, `/sitemap-images.xml`, and `/feed.xml`.
- News detail canonical URL must be `/news/{slug}`.
- News share preview must use Open Graph and Twitter Card tags with absolute URLs.
- News preview fallback order: title = `meta_title` then `news_title`; caption = `meta_description` then `news_content_short`; image = `photo` then `banner` then default OG image.
- Share buttons should use canonical slug URLs for WhatsApp, Facebook, X, and Copy Link; preview is controlled by meta tags, not by share-button text alone.
- Preferred OG image size is 1200x630, accessible without login, and not blocked from crawling.

## Styling And Assets

- Tailwind scans `./fallbacks/**/*.html`, `./app/Views/**/*.php`, and `./public/assets/js/**/*.js`; if adding dynamic classes, ensure they appear in scanned files or generated CSS will miss them.
- Source CSS is `assets/css/input.css`; compiled output is `assets/css/tailwind.css`; custom site/admin CSS is `assets/css/styles.css`.
- Preserve the GenBI visual language from `FEATURE.md`: softened GenBI blue, warm neutral/cream backgrounds, editorial serif headings, readable sans body text, large rounded cards, subtle borders, and clean admin UI.
- For uploads in the PHP backend, validate with `finfo_file()` and `getimagesize()`; do not trust `$_FILES['type']`.
- Uploaded images should be limited to safe image MIME types and randomized filenames; disable PHP execution in uploads.

## Testing And Progress

- Run `npm test` after changes to API normalization, frontend/backend integration logic, or progress-tracked work.
- Run `npm run build:css` after Tailwind class/style changes.
- Do not mark `docs/PROJECT_PROGRESS.md` items complete until relevant tests pass.
- If adding PHP backend code later, add the smallest practical verification path for routes, validation, CSRF, auth, uploads, slug redirects, and SEO meta output.

## Commit Discipline

- Commit every completed implementation phase after the relevant verification passes; do not leave a finished phase uncommitted unless the user explicitly asks not to commit.
- Treat a phase as a coherent, reviewable unit of work, such as one bug fix, one UI refinement, one route/API integration slice, one CMS feature slice, or one documentation/planning update.
- Before committing, inspect `git status` and `git diff` to make sure the commit includes only files related to the completed phase and never includes secrets, local environment files, credentials, generated junk, or unrelated user changes.
- Use detailed commit descriptions that explain the intent, scope, verification, and user-visible behavior, not just a vague summary. Prefer a concise subject plus a body when the change spans multiple files or behavior areas.
- Mention the verification command(s) in the commit body when applicable, for example `npm test` or `npm run build:css`.
- If verification cannot be run, commit only after documenting the reason in the commit body and reporting the limitation to the user.
- Do not amend, squash, reset, force-push, or rewrite history unless the user explicitly requests it.
