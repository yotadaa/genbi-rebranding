# Admin Feature, FAQ, and Social Media Implementation Checklist

Checked before implementation on 2026-07-30. The legacy fallback pages remain the layout source of truth. No replacement admin layout will be introduced.

## Shared audit and layout

- [x] Inspect legacy route shells.
  - [x] `fallbacks/admin/feature.html`
  - [x] `fallbacks/admin/feature-add.html`
  - [x] `fallbacks/admin/faq.html`
  - [x] `fallbacks/admin/faq-add.html`
  - [x] `fallbacks/admin/social-media.html`
  - [x] `fallbacks/admin/social_media.html`
- [x] Inspect shared admin shell and components.
  - [x] `laravel-app/resources/views/admin/static-shell.blade.php`
  - [x] `laravel-app/public/assets/js/admin/admin.js`
  - [x] `laravel-app/public/assets/js/admin/cms.js`
  - [x] `laravel-app/public/assets/css/styles.css`
  - [x] Confirm existing `renderShell`, `admin-card`, `simple-admin-card`, `editor-workspace`, `config-card`, custom select, confirmation modal, toast, and feature media components will be reused.
- [x] Inspect data/API routing conventions.
  - [x] `laravel-app/routes/web.php`
  - [x] `laravel-app/public/assets/js/api-core.js`
  - [x] `laravel-app/app/Http/Controllers/Admin/AdminPageController.php`
- [x] Confirm legacy database tables and columns.
  - [x] `tbl_feature` and `tbl_feature_image`
  - [x] `tbl_faq`
  - [x] `tbl_social`

## `/admin/feature`, `/admin/feature-add`, `/admin/feature-edit`

- [x] Replace mock list rendering with live API data.
  - [x] Fetch `/admin/features`.
  - [x] Render loading, empty, error, search, status, and home-visibility states.
  - [x] Use existing Program Utama cards/icons and old admin actions.
  - [x] Bind live delete through the existing custom confirmation modal.
- [x] Replace simulated editor with a live editor.
  - [x] Fetch `/admin/features/{id}` on edit.
  - [x] Fill title, name, focus, description, icon, visibility, status, and sort order.
  - [x] Reuse the existing custom icon picker.
  - [x] Reuse the existing image upload/reorder/delete board.
  - [x] Submit create/update to the Laravel API.
  - [x] Preserve `/admin/feature-edit?id={id}` after create.
- [x] Related implementation files.
  - [x] `laravel-app/public/assets/js/admin/cms.js`
  - [x] `laravel-app/public/assets/js/api-core.js`
  - [x] `laravel-app/app/Http/Controllers/Admin/FeatureController.php`
  - [x] `laravel-app/app/Models/Feature.php`
  - [x] `laravel-app/app/Models/FeatureImage.php`
  - [x] `laravel-app/routes/web.php`

## `/admin/faq`, `/admin/faq-add`

- [x] Implement Laravel persistence over the legacy `tbl_faq`.
  - [x] Add Eloquent model with `faq_id` primary key.
  - [x] Add admin controller with index, show, store, update, and delete.
  - [x] Validate the legacy 60-character title limit.
  - [x] Normalize `show_on_home` to legacy `Yes`/`No`.
  - [x] Store safe plain-text FAQ answers.
- [x] Register screen and API routes.
  - [x] Keep `/admin/faq` and `/admin/faq-add` on their legacy fallback shells.
  - [x] Add `/admin/faqs` CRUD API routes.
- [x] Replace mock FAQ UI with live CRUD.
  - [x] Render loading, empty, error, and search states.
  - [x] Support edit through `/admin/faq-add?id={id}`.
  - [x] Submit create/update with CSRF.
  - [x] Delete through the existing custom confirmation modal.
- [x] Related implementation files.
  - [x] `laravel-app/app/Models/Faq.php`
  - [x] `laravel-app/app/Http/Controllers/Admin/FaqController.php`
  - [x] `laravel-app/routes/web.php`
  - [x] `laravel-app/public/assets/js/api-core.js`
  - [x] `laravel-app/public/assets/js/admin/cms.js`

## `/admin/social-media`

- [x] Implement Laravel persistence over legacy `tbl_social`.
  - [x] Add Eloquent model with `social_id` primary key.
  - [x] Add admin controller to read and update YouTube, Instagram, and WhatsApp.
  - [x] Preserve other legacy social rows.
  - [x] Validate empty or HTTP(S) URLs and legacy column length.
  - [x] Clear public site settings cache after updates.
- [x] Replace simulated social form with live data.
  - [x] Load saved URLs.
  - [x] Show loading and inline error/help states.
  - [x] Save all three channels with CSRF.
  - [x] Disable the submit button while saving.
- [x] Use saved social links on the public header.
  - [x] Expose active social links through `SiteSettings`.
  - [x] Replace hard-coded header URLs while keeping safe defaults.
  - [x] Prevent the legacy public JavaScript shell from replacing live URLs with `#`.
- [x] Related implementation files.
  - [x] `laravel-app/app/Models/Social.php`
  - [x] `laravel-app/app/Http/Controllers/Admin/SocialMediaController.php`
  - [x] `laravel-app/app/Services/SiteSettings.php`
  - [x] `laravel-app/resources/views/partials/public-header.blade.php`
  - [x] `laravel-app/public/assets/js/app.js`
  - [x] `laravel-app/routes/web.php`
  - [x] `laravel-app/public/assets/js/api-core.js`
  - [x] `laravel-app/public/assets/js/admin/cms.js`

## Validation and handoff

- [x] PHP syntax checks for every added or changed PHP file.
- [x] JavaScript syntax checks for source and generated bundles.
- [x] Rebuild distributable `cms.js`, `api-core.js`, and `app.js`.
- [x] CSS rebuild not required because no CSS source changed.
- [x] Verify all registered controller actions exist with `php artisan route:list`.
- [x] Smoke-test live reads for Feature, FAQ, and Social Media.
- [x] Smoke-test create/update/delete using database transactions that roll back.
- [x] Clear stale Laravel route cache and rebuild the Blade view cache.
- [x] Run the Laravel test suite and record unrelated failures separately.
  - [x] Existing PHPUnit smoke test still fails because the in-memory SQLite test database lacks the pre-existing `teams` table.
  - [x] Existing `npm test` command still stops because the Laravel copy has no `tools/check-theme-contrast.js`; direct Node syntax checks pass.
- [x] Inspect authenticated desktop and 390px mobile screenshots.
  - [x] No horizontal overflow on Feature list/editor, FAQ editor, or Social Media.
  - [x] Feature edit loads persisted values and gallery state.
  - [x] FAQ edit loads persisted values.
  - [x] Social Media loads all three legacy table values.
  - [x] Public shell renders the saved YouTube, Instagram, and WhatsApp URLs.
- [x] Confirm no mock/simulation handlers remain on the requested routes.
- [x] Mark completed checklist items.
- [x] Commit all related files with a detailed description.
