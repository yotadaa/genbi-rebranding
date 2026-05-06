<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminPageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\NewsCommentController;
use App\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Public\CommentController;
use App\Controllers\Public\FeedController;
use App\Controllers\Public\NewsController;
use App\Controllers\Public\PageController;
use App\Controllers\Public\PrestasiController;
use App\Controllers\Public\SitemapController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuthService;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\StaticPageRenderer;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\Prestasi;
use App\Models\PrestasiToken;

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

$renderer = new StaticPageRenderer($rootPath);
$router = new Router();
$newsModel = null;
$commentModel = null;
$prestasiModel = null;
$tokenModel = null;

try {
    $db = \App\Core\Database::connection();
    $newsModel = new News($db);
    $commentModel = new NewsComment($db);
    $prestasiModel = new Prestasi($db);
    $tokenModel = new PrestasiToken($db);
} catch (\Throwable) {
    $newsModel = null;
    $commentModel = null;
    $prestasiModel = null;
    $tokenModel = null;
}

$pageController = new PageController($renderer);
$newsController = new NewsController($renderer, $newsModel);
$commentController = new CommentController($newsModel, $commentModel);
$prestasiController = new PrestasiController($renderer, $prestasiModel, $tokenModel);
$sitemapController = new SitemapController($newsModel, $prestasiModel);
$feedController = new FeedController($newsModel);
$authService = new AuthService($db ?? null);
$authController = new AuthController($authService);
$authMiddleware = new AuthMiddleware();
$csrfMiddleware = new CsrfMiddleware();
$adminPageController = new AdminPageController($renderer);
$adminNewsCommentController = new NewsCommentController($commentModel);
$adminPrestasiController = new AdminPrestasiController($prestasiModel);
$adminPrestasiTokenController = new PrestasiTokenController($tokenModel);

require $rootPath . '/routes/web.php';
require $rootPath . '/routes/admin.php';

return [$router, new Request(), new Response()];
