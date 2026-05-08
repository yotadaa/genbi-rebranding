<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminPageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\Admin\NewsCommentController;
use App\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Controllers\Public\CommentController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\FeedController;
use App\Controllers\Public\NewsController;
use App\Controllers\Public\PageController;
use App\Controllers\Public\ContactController;
use App\Controllers\Public\PrestasiController;
use App\Controllers\Public\SitemapController;
use App\Controllers\Public\EventController;
use App\Controllers\Public\TeamController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Services\AuthService;
use App\Services\LoginThrottleService;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Models\Event;
use App\Models\Feature;
use App\Models\TeamMember;
use App\Models\ContactSetting;

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
$tokenModel = null;
$teamModel = null;
$featureModel = null;
$contactSettingModel = null;

try {
    $db = \App\Core\Database::connection();
    $newsModel = new News($db);
    $commentModel = new NewsComment($db);
    $prestasiModel = new Prestasi($db);
    $tokenModel = new PrestasiToken($db);
    $eventModel = new Event($db);
    $teamModel = new TeamMember($db);
    $featureModel = new Feature($db);
    $contactSettingModel = new ContactSetting($db);
} catch (\Throwable $exception) {
    error_log('[GenBI DB] ' . $exception->getMessage());
    $newsModel = null;
    $commentModel = null;
    $prestasiModel = null;
    $tokenModel = null;
    $eventModel = null;
    $teamModel = null;
    $featureModel = null;
    $contactSettingModel = null;
}

$pageController = new PageController($renderer);
$contactController = new ContactController($viewRenderer, $contactSettingModel);
$homeController = new HomeController($renderer, $featureModel, $viewRenderer);
$newsController = new NewsController($renderer, $newsModel, $viewRenderer);
$commentController = new CommentController($newsModel, $commentModel);
$prestasiController = new PrestasiController($renderer, $prestasiModel, $tokenModel, $viewRenderer);
$eventController = new EventController($renderer, $eventModel, $viewRenderer);
$teamController = new TeamController($renderer, $teamModel, $viewRenderer);
$sitemapController = new SitemapController($newsModel, $prestasiModel);
$feedController = new FeedController($newsModel);
$authService = new AuthService($db ?? null);
$loginThrottle = new LoginThrottleService($db ?? null);
$authController = new AuthController($authService, $loginThrottle);
$authMiddleware = new AuthMiddleware();
$csrfMiddleware = new CsrfMiddleware();
$adminPageController = new AdminPageController($renderer, $viewRenderer, $newsModel, $teamModel, $prestasiModel, $featureModel);
$adminNewsController = new AdminNewsController($newsModel);
$adminNewsCommentController = new NewsCommentController($commentModel);
$adminPrestasiController = new AdminPrestasiController($prestasiModel);
$adminPrestasiTokenController = new PrestasiTokenController($tokenModel);
$adminTeamMemberController = new AdminTeamMemberController($teamModel);
$adminFeatureController = new \App\Controllers\Admin\FeatureController($featureModel);
$adminContactSettingController = new \App\Controllers\Admin\ContactSettingController($contactSettingModel);

require $rootPath . '/routes/web.php';
require $rootPath . '/routes/admin.php';

return [$router, new Request(), new Response()];
