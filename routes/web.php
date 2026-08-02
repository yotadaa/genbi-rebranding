<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Controllers\Public\AboutController;
use App\Controllers\Public\BukuController;
use App\Controllers\Public\CommentController;
use App\Controllers\Public\EventController;
use App\Controllers\Public\FeedController;
use App\Controllers\Public\FeatureController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\NewsController;
use App\Controllers\Public\PageController;
use App\Controllers\Public\ContactController;
use App\Controllers\Public\PrestasiController;
use App\Controllers\Public\PresensiController;
use App\Controllers\Public\SitemapController;
use App\Controllers\Public\TeamController;
use App\Middleware\CsrfMiddleware;

/** @var AboutController $aboutController */
/** @var BukuController $bukuController */
/** @var PageController $pageController */
/** @var FeatureController $featureController */
/** @var ContactController $contactController */
/** @var HomeController $homeController */
/** @var TeamController $teamController */
/** @var PrestasiController $prestasiController */
/** @var PresensiController $presensiController */
/** @var EventController $eventController */
/** @var NewsController $newsController */
/** @var CommentController $commentController */
/** @var SitemapController $sitemapController */
/** @var FeedController $feedController */
/** @var CsrfMiddleware $csrfMiddleware */

$router->get('/', static fn(Request $request, Response $response) => $homeController->index($request, $response));
$router->get('/about', static fn(Request $request, Response $response) => $aboutController->index($request, $response));
$router->get('/team', static fn(Request $request, Response $response) => $teamController->index($request, $response));
$router->get('/teams', static fn(Request $request, Response $response) => $teamController->index($request, $response));
$router->get('/feature', static fn(Request $request, Response $response) => $featureController->index($request, $response));
$router->get('/prestasi', static fn(Request $request, Response $response) => $prestasiController->index($request, $response));
$router->get('/prestasi/submit/{token}', static fn(Request $request, Response $response, array $params) => $prestasiController->submissionForm($request, $response, $params));
$router->get('/prestasi/{slug}', static fn(Request $request, Response $response, array $params) => $prestasiController->show($request, $response, $params));
$router->get('/buku', static fn(Request $request, Response $response) => $bukuController->index($request, $response));
$router->get('/presensi/{token}/members', static fn(Request $request, Response $response, array $params) => $presensiController->members($request, $response, $params));
$router->get('/presensi/{token}', static fn(Request $request, Response $response, array $params) => $presensiController->show($request, $response, $params));
$router->get('/event', static fn(Request $request, Response $response) => $eventController->index($request, $response));
$router->get('/event/{slug}', static fn(Request $request, Response $response, array $params) => $eventController->show($request, $response, $params));
$router->get('/contact', static fn(Request $request, Response $response) => $contactController->index($request, $response));
$router->get('/news', static fn(Request $request, Response $response) => $newsController->index($request, $response));
$router->get('/news/view/{id}', static fn(Request $request, Response $response, array $params) => $newsController->legacyShow($request, $response, $params));
$router->get('/news/id/{id}', static fn(Request $request, Response $response, array $params) => $newsController->legacyShow($request, $response, $params));
$router->get('/news/{slug}/comments', static fn(Request $request, Response $response, array $params) => $commentController->index($request, $response, $params));
$router->get('/news/{slug}', static fn(Request $request, Response $response, array $params) => $newsController->show($request, $response, $params));

// Public POST routes with CSRF protection
$router->group([$csrfMiddleware], static function ($router) use ($prestasiController, $presensiController, $commentController) {
    $router->post('/prestasi/submit/{token}', static fn(Request $request, Response $response, array $params) => $prestasiController->submitWithToken($request, $response, $params));
    $router->post('/presensi/{token}', static fn(Request $request, Response $response, array $params) => $presensiController->submit($request, $response, $params));
    $router->post('/news/{slug}/comment', static fn(Request $request, Response $response, array $params) => $commentController->store($request, $response, $params));
    $router->post('/news/{slug}/comment/{id}/vote', static fn(Request $request, Response $response, array $params) => $commentController->vote($request, $response, $params));
});

// Sitemap and Feed
$router->get('/sitemap.xml', static fn(Request $request, Response $response) => $sitemapController->index($request, $response));
$router->get('/sitemap-pages.xml', static fn(Request $request, Response $response) => $sitemapController->pages($request, $response));
$router->get('/sitemap-news.xml', static fn(Request $request, Response $response) => $sitemapController->news($request, $response));
$router->get('/sitemap-events.xml', static fn(Request $request, Response $response) => $sitemapController->events($request, $response));
$router->get('/sitemap-prestasi.xml', static fn(Request $request, Response $response) => $sitemapController->prestasi($request, $response));
$router->get('/sitemap-images.xml', static fn(Request $request, Response $response) => $sitemapController->images($request, $response));
$router->get('/feed.xml', static fn(Request $request, Response $response) => $feedController->news($request, $response));
