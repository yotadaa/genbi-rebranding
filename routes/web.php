<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

$router->get('/', static fn (Request $request, Response $response) => $pageController->show($request, $response, 'index.html'));
$router->get('/about', static fn (Request $request, Response $response) => $pageController->show($request, $response, 'about.html'));
$router->get('/team', static fn (Request $request, Response $response) => $pageController->show($request, $response, 'team.html'));
$router->get('/prestasi', static fn (Request $request, Response $response) => $prestasiController->index($request, $response));
$router->get('/prestasi/submit/{token}', static fn (Request $request, Response $response, array $params) => $prestasiController->submissionForm($request, $response, $params));
$router->post('/prestasi/submit/{token}', static fn (Request $request, Response $response, array $params) => $prestasiController->submitWithToken($request, $response, $params));
$router->get('/prestasi/{slug}', static fn (Request $request, Response $response, array $params) => $prestasiController->show($request, $response, $params));
$router->get('/contact', static fn (Request $request, Response $response) => $pageController->show($request, $response, 'contact.html'));
$router->get('/news', static fn (Request $request, Response $response) => $newsController->index($request, $response));
$router->get('/news/id/{id}', static fn (Request $request, Response $response, array $params) => $newsController->legacyShow($request, $response, $params));
$router->get('/news/{slug}/comments', static fn (Request $request, Response $response, array $params) => $commentController->index($request, $response, $params));
$router->post('/news/{slug}/comment', static fn (Request $request, Response $response, array $params) => $commentController->store($request, $response, $params));
$router->get('/news/{slug}', static fn (Request $request, Response $response, array $params) => $newsController->show($request, $response, $params));

// Sitemap and Feed
$router->get('/sitemap.xml', static fn (Request $request, Response $response) => $sitemapController->index($request, $response));
$router->get('/sitemap-pages.xml', static fn (Request $request, Response $response) => $sitemapController->pages($request, $response));
$router->get('/sitemap-news.xml', static fn (Request $request, Response $response) => $sitemapController->news($request, $response));
$router->get('/sitemap-prestasi.xml', static fn (Request $request, Response $response) => $sitemapController->prestasi($request, $response));
$router->get('/feed.xml', static fn (Request $request, Response $response) => $feedController->news($request, $response));
