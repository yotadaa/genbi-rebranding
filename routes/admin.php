<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

// Auth routes (no auth middleware - accessible to guests)
$router->get('/admin/login', static fn(Request $request, Response $response) => $authController->showLogin($request, $response));
$router->post('/admin/login', static fn(Request $request, Response $response) => $authController->login($request, $response));

// Logout requires CSRF validation (prevents cross-site logout attacks)
$router->group([$csrfMiddleware], function ($router) use ($authController) {
    $router->post('/admin/logout', static fn(Request $request, Response $response) => $authController->logout($request, $response));
});

// Protected admin routes (require authentication + CSRF on POST)
$router->group([$authMiddleware, $csrfMiddleware], function ($router) use ($adminPageController, $adminNewsController, $adminNewsCommentController, $adminPrestasiController, $adminPrestasiTokenController, $adminTeamMemberController) {
    $router->get('/admin', static fn(Request $request, Response $response) => $adminPageController->dashboard($request, $response));
    $router->get('/admin/dashboard', static fn(Request $request, Response $response) => $adminPageController->dashboard($request, $response));
    // News CMS
    $router->get('/admin/news/list', static fn(Request $request, Response $response) => $adminNewsController->index($request, $response));
    $router->get('/admin/news/categories', static fn(Request $request, Response $response) => $adminNewsController->categories($request, $response));
    $router->get('/admin/news/{id}', static fn(Request $request, Response $response, array $params) => $adminNewsController->show($request, $response, $params));
    $router->post('/admin/news', static fn(Request $request, Response $response) => $adminNewsController->store($request, $response));
    $router->post('/admin/news/{id}/update', static fn(Request $request, Response $response, array $params) => $adminNewsController->update($request, $response, $params));
    $router->post('/admin/news/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminNewsController->delete($request, $response, $params));
    $router->post('/admin/news/upload', static fn(Request $request, Response $response) => $adminNewsController->upload($request, $response));

    // News Comments
    $router->get('/admin/news-comments', static fn(Request $request, Response $response) => $adminNewsCommentController->index($request, $response));
    $router->post('/admin/news-comments/{id}/approve', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'approve'));
    $router->post('/admin/news-comments/{id}/reject', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'reject'));
    $router->post('/admin/news-comments/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'delete'));
    $router->get('/admin/prestasi/list', static fn(Request $request, Response $response) => $adminPrestasiController->index($request, $response));
    $router->post('/admin/prestasi', static fn(Request $request, Response $response) => $adminPrestasiController->store($request, $response));
    $router->post('/admin/prestasi/upload', static fn(Request $request, Response $response) => $adminPrestasiController->upload($request, $response));
    $router->get('/admin/prestasi/{id}', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->show($request, $response, $params));
    $router->post('/admin/prestasi/{id}/update', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->update($request, $response, $params));
    $router->post('/admin/prestasi/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->delete($request, $response, $params));
    $router->get('/admin/prestasi-tokens', static fn(Request $request, Response $response) => $adminPrestasiTokenController->index($request, $response));
    $router->post('/admin/prestasi-tokens', static fn(Request $request, Response $response) => $adminPrestasiTokenController->generate($request, $response));
    $router->post('/admin/prestasi-tokens/{id}/revoke', static fn(Request $request, Response $response, array $params) => $adminPrestasiTokenController->revoke($request, $response, $params));
    $router->get('/admin/team-members', static fn(Request $request, Response $response) => $adminTeamMemberController->index($request, $response));
    $router->get('/admin/team-members/options', static fn(Request $request, Response $response) => $adminTeamMemberController->options($request, $response));
    $router->post('/admin/team-members', static fn(Request $request, Response $response) => $adminTeamMemberController->store($request, $response));
    $router->post('/admin/team-members/bulk', static fn(Request $request, Response $response) => $adminTeamMemberController->bulk($request, $response));
    $router->post('/admin/team-members/upload', static fn(Request $request, Response $response) => $adminTeamMemberController->upload($request, $response));
    $router->get('/admin/team-members/{id}', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->show($request, $response, $params));
    $router->post('/admin/team-members/{id}/update', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->update($request, $response, $params));
    $router->post('/admin/team-members/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->delete($request, $response, $params));
    $router->post('/admin/team-members/{id}/home', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->setHome($request, $response, $params));
    $router->get('/admin/{page}', static fn(Request $request, Response $response, array $params) => $adminPageController->show($request, $response, $params));
});
