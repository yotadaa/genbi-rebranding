<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\TeamController;
use App\Http\Controllers\Public\PrestasiController as PublicPrestasiController;
use App\Http\Controllers\Public\EventController as PublicEventController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\PresensiController as PublicPresensiController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\FeedController;

// Admin Controllers
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\NewsCommentController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ContactSettingController;
use App\Http\Controllers\Admin\CommentSettingController;
use App\Http\Controllers\Admin\PhotoGalleryController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\PresensiController as AdminPresensiController;
use App\Http\Controllers\Admin\GenBIPointController;
use App\Http\Controllers\Admin\PrestasiTokenController;

// Public Routes (no auth)
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [AboutController::class, 'index']);
Route::get('/team', [TeamController::class, 'index']);
Route::get('/teams', [TeamController::class, 'index']);
Route::get('/prestasi', [PublicPrestasiController::class, 'index']);
Route::get('/prestasi/submit/{token}', [PublicPrestasiController::class, 'submissionForm']);
Route::post('/prestasi/submit/{token}', [PublicPrestasiController::class, 'submitWithToken']);
Route::get('/prestasi/{slug}', [PublicPrestasiController::class, 'show']);
Route::get('/event', [PublicEventController::class, 'index']);
Route::get('/event/{slug}', [PublicEventController::class, 'show']);
Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/id/{id}', [PublicNewsController::class, 'legacyShow']); // MUST come before /news/{slug}
Route::get('/news/{slug}/comments', [CommentController::class, 'index']);
Route::post('/news/{slug}/comment', [CommentController::class, 'store']);
Route::post('/news/{slug}/comment/{id}/vote', [CommentController::class, 'vote']);
Route::get('/news/{slug}', [PublicNewsController::class, 'show']);
Route::get('/contact', [ContactController::class, 'index']);
Route::get('/presensi/{token}', [PublicPresensiController::class, 'show']);
Route::get('/presensi/{token}/members', [PublicPresensiController::class, 'members']);
Route::post('/presensi/{token}', [PublicPresensiController::class, 'submit']);

// Sitemaps and Feeds
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages']);
Route::get('/sitemap-news.xml', [SitemapController::class, 'news']);
Route::get('/sitemap-events.xml', [SitemapController::class, 'events']);
Route::get('/sitemap-prestasi.xml', [SitemapController::class, 'prestasi']);
Route::get('/sitemap-images.xml', [SitemapController::class, 'images']);
Route::get('/feed.xml', [FeedController::class, 'news']);

// Auth Routes
Route::middleware(['guest.admin'])->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
});
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Routes
Route::middleware(['auth', 'admin.role'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminPageController::class, 'dashboard'])->name('dashboard.alt');
    Route::get('/news', [AdminPageController::class, 'newsIndex'])->name('news');
    Route::get('/news/add', [AdminPageController::class, 'newsAdd'])->name('news.add');
    Route::get('/news/edit', [AdminPageController::class, 'newsEdit'])->name('news.edit');
    Route::get('/news-add', [AdminPageController::class, 'newsAdd'])->name('newsAdd');
    Route::get('/news-edit', [AdminPageController::class, 'newsEdit'])->name('newsEdit');

    // Legacy-layout CMS screens rendered server-side, then hydrated by cms.js.
    Route::get('/prestasi', [AdminPageController::class, 'prestasiIndex'])->name('prestasi');
    Route::get('/prestasi-add', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->prestasiForm($request))->name('prestasi.add');
    Route::get('/prestasi-edit', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->prestasiForm($request, true))->name('prestasi.edit');
    Route::get('/prestasi-token', [AdminPageController::class, 'prestasiTokenIndex'])->name('prestasi.token');
    Route::get('/team-member', [AdminPageController::class, 'teamIndex'])->name('team');
    
    // News API & actions
    Route::get('/news/list', [AdminNewsController::class, 'index'])->name('news.list');
    Route::get('/news/categories', [AdminNewsController::class, 'categories']);
    Route::get('/news/{id}', [AdminNewsController::class, 'show']);
    Route::post('/news', [AdminNewsController::class, 'store']);
    Route::post('/news/{id}/update', [AdminNewsController::class, 'update']);
    Route::post('/news/{id}/delete', [AdminNewsController::class, 'destroy']);
    Route::post('/news/upload', [AdminNewsController::class, 'upload']);
    
    // Prestasi API & actions
    Route::get('/prestasi/list', [AdminPrestasiController::class, 'index']);
    Route::get('/prestasi/{id}', [AdminPrestasiController::class, 'show']);
    Route::post('/prestasi', [AdminPrestasiController::class, 'store']);
    Route::post('/prestasi/{id}/update', [AdminPrestasiController::class, 'update']);
    Route::post('/prestasi/{id}/delete', [AdminPrestasiController::class, 'destroy']);
    Route::post('/prestasi/upload', [AdminPrestasiController::class, 'upload']);
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}/update', [CategoryController::class, 'update']);
    Route::post('/categories/{id}/delete', [CategoryController::class, 'destroy']);
    
    // Events
    Route::get('/events', [AdminEventController::class, 'index']);
    Route::get('/events/{id}', [AdminEventController::class, 'show']);
    Route::post('/events', [AdminEventController::class, 'store']);
    Route::post('/events/{id}/update', [AdminEventController::class, 'update']);
    Route::post('/events/{id}/delete', [AdminEventController::class, 'destroy']);
    
    // News Comments
    Route::get('/news-comments', [NewsCommentController::class, 'index']);
    Route::post('/news-comments/{id}/approve', [NewsCommentController::class, 'approve']);
    Route::post('/news-comments/{id}/reject', [NewsCommentController::class, 'reject']);
    Route::post('/news-comments/{id}/delete', [NewsCommentController::class, 'destroy']);
    
    // Team Members
    Route::get('/team-members', [TeamMemberController::class, 'index']);
    Route::get('/team-members/options', [TeamMemberController::class, 'options']);
    Route::get('/team-members/{id}', [TeamMemberController::class, 'show']);
    Route::post('/team-members', [TeamMemberController::class, 'store']);
    Route::post('/team-members/upload', [TeamMemberController::class, 'upload']);
    Route::post('/team-members/{id}/update', [TeamMemberController::class, 'update']);
    Route::post('/team-members/{id}/delete', [TeamMemberController::class, 'destroy']);
    Route::post('/team-members/bulk', [TeamMemberController::class, 'bulk']);
    Route::post('/team-members/{id}/home', [TeamMemberController::class, 'setHome']);
    Route::post('/team-members/{id}/alumni', [TeamMemberController::class, 'alumni']);
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'edit']);
    Route::get('/settings/data', [SettingsController::class, 'data']);
    Route::post('/settings/logo', [SettingsController::class, 'updateLogo']);
    Route::post('/settings/favicon', [SettingsController::class, 'updateFavicon']);
    Route::post('/settings/topbar', [SettingsController::class, 'updateTopbar']);
    Route::post('/settings/footer', [SettingsController::class, 'updateFooter']);
    Route::post('/settings/email', [SettingsController::class, 'updateEmail']);
    Route::post('/settings/banner', [SettingsController::class, 'updateBanner']);
    Route::post('/settings/sidebar', [SettingsController::class, 'updateSidebar']);
    Route::post('/settings/color', [SettingsController::class, 'updateColor']);
    Route::post('/settings/upload', [SettingsController::class, 'upload']);
    Route::get('/settings/theme', [SettingsController::class, 'showTheme']);
    Route::post('/settings/theme', [SettingsController::class, 'updateTheme']);
    Route::get('/settings/page-home', [SettingsController::class, 'showHomePage']);
    Route::post('/settings/page-home', [SettingsController::class, 'updateHomePage']);
    Route::get('/settings/pages/{page}', [SettingsController::class, 'pageContent']);
    Route::post('/settings/pages/{page}', [SettingsController::class, 'updatePageContent']);
    
    // Contact Setting
    Route::get('/contact-setting', [ContactSettingController::class, 'show']);
    Route::post('/contact-setting', [ContactSettingController::class, 'update']);
    
    // Comment Setting
    Route::get('/comment-setting', [CommentSettingController::class, 'show']);
    Route::post('/comment-setting', [CommentSettingController::class, 'update']);
    
    // Photos
    Route::get('/photos', [PhotoGalleryController::class, 'index']);
    Route::get('/photos/{id}', [PhotoGalleryController::class, 'show']);
    Route::post('/photos', [PhotoGalleryController::class, 'store']);
    Route::post('/photos/upload', [PhotoGalleryController::class, 'upload']);
    Route::post('/photos/{id}/update', [PhotoGalleryController::class, 'update']);
    Route::post('/photos/{id}/delete', [PhotoGalleryController::class, 'destroy']);
    
    // Features
    Route::get('/features', [FeatureController::class, 'index']);
    Route::get('/features/{id}', [FeatureController::class, 'show']);
    Route::post('/features', [FeatureController::class, 'store']);
    Route::post('/features/upload', [FeatureController::class, 'upload']);
    Route::post('/features/{id}/update', [FeatureController::class, 'update']);
    Route::post('/features/{id}/delete', [FeatureController::class, 'destroy']);
    
    // Presensi
    Route::get('/presensi', [AdminPageController::class, 'presensiIndex'])->name('presensi');
    Route::get('/presensi-add', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->presensiForm($request))->name('presensi.add');
    Route::get('/presensi-edit', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->presensiForm($request, true))->name('presensi.edit');
    Route::get('/presensi-detail', [AdminPageController::class, 'presensiDetail'])->name('presensi.show');
    Route::get('/presensi/list', [AdminPresensiController::class, 'index']);
    Route::get('/presensi/{id}', [AdminPresensiController::class, 'show']);
    Route::get('/presensi/{id}/submissions', [AdminPresensiController::class, 'submissions']);
    Route::post('/presensi', [AdminPresensiController::class, 'store']);
    Route::post('/presensi/{id}/update', [AdminPresensiController::class, 'update']);
    Route::post('/presensi/{id}/delete', [AdminPresensiController::class, 'destroy']);
    Route::post('/presensi/submissions/{id}/approve', [AdminPresensiController::class, 'approve']);
    Route::post('/presensi/submissions/{id}/cancel', [AdminPresensiController::class, 'cancel']);
    Route::post('/presensi/{eventId}/members/{teamId}/approve', [AdminPresensiController::class, 'approveMember']);
    
    // GenBI Poin
    Route::get('/genbi-poin', [AdminPageController::class, 'genbiPoinIndex'])->name('genbiPoin');
    Route::get('/genbi-poin-detail', [AdminPageController::class, 'genbiPoinDetail'])->name('genbiPoin.show');
    Route::get('/genbi-poin-add', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->genbiPoinForm($request))->name('genbiPoin.add');
    Route::get('/genbi-poin-edit', fn (\Illuminate\Http\Request $request) => app(AdminPageController::class)->genbiPoinForm($request, true))->name('genbiPoin.edit');
    Route::get('/genbi-poin/members', [GenBIPointController::class, 'members']);
    Route::get('/genbi-poin/activities', [GenBIPointController::class, 'activities']);
    Route::get('/genbi-poin/activities/{id}', [GenBIPointController::class, 'showActivity']);
    Route::post('/genbi-poin/activities', [GenBIPointController::class, 'storeActivity']);
    Route::post('/genbi-poin/activities/{id}/update', [GenBIPointController::class, 'updateActivity']);
    
    // Prestasi Tokens
    Route::get('/prestasi-tokens', [PrestasiTokenController::class, 'index']);
    Route::post('/prestasi-tokens', [PrestasiTokenController::class, 'generate']);
    Route::post('/prestasi-tokens/{id}/revoke', [PrestasiTokenController::class, 'revoke']);
    
    // Catch-all static pages
    Route::get('/{page}', [AdminPageController::class, 'show']);
    Route::get('/{page}/{sub}', [AdminPageController::class, 'show']);
});
