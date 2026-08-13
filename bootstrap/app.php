<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminPageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BukuAdminController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\CommentSettingController;
use App\Controllers\Admin\EventController as AdminEventController;
use App\Controllers\Admin\GenBIPointController as AdminGenBIPointController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\Admin\NewsCommentController;
use App\Controllers\Admin\PresensiController as AdminPresensiController;
use App\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Admin\PhotoGalleryController as AdminPhotoGalleryController;
use App\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Controllers\Public\AboutController;
use App\Controllers\Public\BukuController;
use App\Controllers\Public\CommentController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\FeedController;
use App\Controllers\Public\FeatureController;
use App\Controllers\Public\NewsController;
use App\Controllers\Public\PageController;
use App\Controllers\Public\ContactController;
use App\Controllers\Public\PrestasiController;
use App\Controllers\Public\PresensiController;
use App\Controllers\Public\SitemapController;
use App\Controllers\Public\EventController;
use App\Controllers\Public\TeamController;
use App\Controllers\Keuangan\AuthController as KeuanganAuthController;
use App\Controllers\Keuangan\WilayahController as KeuanganWilayahController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Services\AuthService;
use App\Services\CommentThrottleService;
use App\Services\LoginThrottleService;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Models\Buku;
use App\Models\Category;
use App\Models\NewsComment;
use App\Models\Prestasi;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\PrestasiToken;
use App\Models\PhotoGallery;
use App\Models\Event;
use App\Models\Feature;
use App\Models\GenBIPoint;
use App\Models\NewsCommentVote;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\ContactSetting;
use App\Services\CommentPolicy;
use App\Services\SiteSettings;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = dirname(__DIR__) . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$rootPath = dirname(__DIR__);
\App\Core\Env::load($rootPath . '/.env');

// Initialize session for auth and CSRF
if (PHP_SAPI !== 'cli') {
    \App\Core\Session::start();
}

$renderer = new StaticPageRenderer($rootPath . '/fallbacks');
$viewRenderer = new ViewRenderer($rootPath . '/app/Views');
$router = new Router();
$router->addGlobalMiddleware(new SecurityHeadersMiddleware());
$newsModel = null;
$commentModel = null;
$prestasiModel = null;
$presensiEventModel = null;
$presensiSubmissionModel = null;
$genbiPointModel = null;
$tokenModel = null;
$teamModel = null;
$featureModel = null;
$contactSettingModel = null;
$commentVoteModel = null;
$categoryModel = null;
$settingModel = null;
$photoGalleryModel = null;
$commentPolicy = null;
$commentThrottle = null;
$siteSettings = null;

try {
    $db = \App\Core\Database::connection();
    $newsModel = new News($db);
    $prestasiModel = new Prestasi($db);
    $presensiEventModel = new PresensiEvent($db);
    $presensiSubmissionModel = new PresensiSubmission($db);
    $genbiPointModel = new GenBIPoint($db);
    $tokenModel = new PrestasiToken($db);
    $eventModel = new Event($db);
    $teamModel = new TeamMember($db);
    $featureModel = new Feature($db);
    $contactSettingModel = new ContactSetting($db);
    $commentVoteModel = new NewsCommentVote($db);
    $categoryModel = new Category($db);
    $settingModel = new Setting($db);
    $photoGalleryModel = new PhotoGallery($db);
    $commentPolicy = new CommentPolicy($settingModel);
    $siteSettings = new SiteSettings($settingModel);
    $commentThrottle = new CommentThrottleService();
    $commentModel = new NewsComment($db, $commentVoteModel);
    $bukuModel = new Buku($db);
} catch (\Throwable $exception) {
    error_log('[GenBI DB] ' . $exception->getMessage());
    $newsModel = null;
    $bukuModel = null;
    $commentModel = null;
    $prestasiModel = null;
    $presensiEventModel = null;
    $presensiSubmissionModel = null;
    $genbiPointModel = null;
    $tokenModel = null;
    $eventModel = null;
    $teamModel = null;
    $featureModel = null;
    $contactSettingModel = null;
    $commentVoteModel = null;
    $categoryModel = null;
    $settingModel = null;
    $photoGalleryModel = null;
    $commentPolicy = null;
    $commentThrottle = null;
    $siteSettings = new SiteSettings(null);
}

$viewRenderer->share([
    'siteSettings' => $siteSettings,
    'site' => $siteSettings->site(),
]);

$pageController = new PageController($renderer);
$featureController = new FeatureController($renderer, $featureModel, $viewRenderer);
$aboutController = new AboutController($viewRenderer);
$bukuController = new BukuController($viewRenderer, $bukuModel);
$contactController = new ContactController($viewRenderer, $contactSettingModel, $siteSettings);
$homeController = new HomeController($renderer, $featureModel, $newsModel, $eventModel, $teamModel, $viewRenderer, $siteSettings);
$newsController = new NewsController($renderer, $newsModel, $commentModel, $viewRenderer);
$commentController = new CommentController($newsModel, $commentModel, $commentVoteModel, $commentPolicy, $commentThrottle);
$prestasiController = new PrestasiController($renderer, $prestasiModel, $tokenModel, $viewRenderer);
$presensiController = new PresensiController($presensiEventModel, $presensiSubmissionModel, $viewRenderer);
$eventController = new EventController($renderer, $eventModel, $viewRenderer);
$teamController = new TeamController($renderer, $teamModel, $viewRenderer);
$sitemapController = new SitemapController($newsModel, $prestasiModel, $eventModel);
$feedController = new FeedController($newsModel);
$authService = new AuthService($db ?? null);
$loginThrottle = new LoginThrottleService($db ?? null);
$authController = new AuthController($authService, $loginThrottle);
$authMiddleware = new AuthMiddleware();
$csrfMiddleware = new CsrfMiddleware();
$roleMiddleware = new RoleMiddleware(['superadmin', 'admin']);
$adminPageController = new AdminPageController($renderer, $viewRenderer, $newsModel, $teamModel, $prestasiModel, $featureModel, $siteSettings, $presensiEventModel, $presensiSubmissionModel, $genbiPointModel, $eventModel, $categoryModel, $commentModel, $photoGalleryModel, $tokenModel, $bukuModel);
$adminEventController = new AdminEventController($eventModel);
$adminNewsController = new AdminNewsController($newsModel);
$adminBukuController = new BukuAdminController($bukuModel);
$adminCategoryController = new AdminCategoryController($categoryModel);
$adminNewsCommentController = new NewsCommentController($commentModel);
$adminPrestasiController = new AdminPrestasiController($prestasiModel);
$adminPresensiController = new AdminPresensiController($presensiEventModel, $presensiSubmissionModel, $teamModel);
$adminGenBIPointController = new AdminGenBIPointController($genbiPointModel);
$adminPrestasiTokenController = new PrestasiTokenController($tokenModel);
$adminTeamMemberController = new AdminTeamMemberController($teamModel);
$adminFeatureController = new \App\Controllers\Admin\FeatureController($featureModel);
$adminContactSettingController = new \App\Controllers\Admin\ContactSettingController($contactSettingModel);
$adminCommentSettingController = new CommentSettingController($settingModel, $viewRenderer);
$adminSettingsController = new \App\Controllers\Admin\SettingsController($settingModel, $siteSettings, $viewRenderer);
$adminPhotoGalleryController = new AdminPhotoGalleryController($photoGalleryModel);
$keuanganAuthController = new KeuanganAuthController($viewRenderer);
$keuanganWilayahController = new KeuanganWilayahController($viewRenderer);
require $rootPath . '/routes/web.php';
require $rootPath . '/routes/admin.php';
require $rootPath . '/routes/keuangan.php';

return [$router, new Request(), new Response()];
