<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Controllers\Admin\AdminPageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\Admin\NewsCommentController;
use App\Controllers\Admin\EventController as AdminEventController;
use App\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Controllers\Admin\ContactSettingController as AdminContactSettingController;
use App\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/** @var AuthController $authController */
/** @var AdminPageController $adminPageController */
/** @var AdminNewsController $adminNewsController */
/** @var NewsCommentController $adminNewsCommentController */
/** @var AdminEventController $adminEventController */
/** @var AdminPrestasiController $adminPrestasiController */
/** @var PrestasiTokenController $adminPrestasiTokenController */
/** @var AdminTeamMemberController $adminTeamMemberController */
/** @var AdminFeatureController $adminFeatureController */
/** @var AdminContactSettingController $adminContactSettingController */
/** @var AuthMiddleware $authMiddleware */
/** @var CsrfMiddleware $csrfMiddleware */

// Auth routes (no auth middleware - accessible to guests)
$router->get('/admin/login', static fn(Request $request, Response $response) => $authController->showLogin($request, $response));
$router->post('/admin/login', static fn(Request $request, Response $response) => $authController->login($request, $response));

// Logout requires CSRF validation (prevents cross-site logout attacks)
$router->group([$csrfMiddleware], static function ($router) use ($authController) {
    $router->post('/admin/logout', static fn(Request $request, Response $response) => $authController->logout($request, $response));
});

// Protected admin routes (require authentication + CSRF on POST)
$router->group([$authMiddleware, $csrfMiddleware], static function ($router) use (
    $adminPageController,
    $adminNewsController,
    $adminNewsCommentController,
    $adminEventController,
    $adminPrestasiController,
    $adminPrestasiTokenController,
    $adminTeamMemberController,
    $adminFeatureController,
    $adminContactSettingController,
) {
    // Dashboard
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

    // Agenda CMS
    $router->get('/admin/events', static fn(Request $request, Response $response) => $adminEventController->index($request, $response));
    $router->get('/admin/events/{id}', static fn(Request $request, Response $response, array $params) => $adminEventController->show($request, $response, $params));
    $router->post('/admin/events', static fn(Request $request, Response $response) => $adminEventController->store($request, $response));
    $router->post('/admin/events/{id}/update', static fn(Request $request, Response $response, array $params) => $adminEventController->update($request, $response, $params));
    $router->post('/admin/events/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminEventController->delete($request, $response, $params));

    // News Comments
    $router->get('/admin/news-comments', static fn(Request $request, Response $response) => $adminNewsCommentController->index($request, $response));
    $router->post('/admin/news-comments/{id}/approve', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'approve'));
    $router->post('/admin/news-comments/{id}/reject', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'reject'));
    $router->post('/admin/news-comments/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminNewsCommentController->action($request, $response, $params, 'delete'));

    // Prestasi CMS
    $router->get('/admin/prestasi/list', static fn(Request $request, Response $response) => $adminPrestasiController->index($request, $response));
    $router->get('/admin/prestasi/{id}', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->show($request, $response, $params));
    $router->post('/admin/prestasi', static fn(Request $request, Response $response) => $adminPrestasiController->store($request, $response));
    $router->post('/admin/prestasi/{id}/update', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->update($request, $response, $params));
    $router->post('/admin/prestasi/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminPrestasiController->delete($request, $response, $params));
    $router->post('/admin/prestasi/upload', static fn(Request $request, Response $response) => $adminPrestasiController->upload($request, $response));

    // Prestasi Tokens
    $router->get('/admin/prestasi-tokens', static fn(Request $request, Response $response) => $adminPrestasiTokenController->index($request, $response));
    $router->post('/admin/prestasi-tokens', static fn(Request $request, Response $response) => $adminPrestasiTokenController->generate($request, $response));
    $router->post('/admin/prestasi-tokens/{id}/revoke', static fn(Request $request, Response $response, array $params) => $adminPrestasiTokenController->revoke($request, $response, $params));

    // Team Members
    $router->get('/admin/team-members', static fn(Request $request, Response $response) => $adminTeamMemberController->index($request, $response));
    $router->get('/admin/team-members/options', static fn(Request $request, Response $response) => $adminTeamMemberController->options($request, $response));
    $router->get('/admin/team-members/{id}', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->show($request, $response, $params));
    $router->post('/admin/team-members', static fn(Request $request, Response $response) => $adminTeamMemberController->store($request, $response));
    $router->post('/admin/team-members/bulk', static fn(Request $request, Response $response) => $adminTeamMemberController->bulk($request, $response));
    $router->post('/admin/team-members/upload', static fn(Request $request, Response $response) => $adminTeamMemberController->upload($request, $response));
    $router->post('/admin/team-members/{id}/update', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->update($request, $response, $params));
    $router->post('/admin/team-members/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->delete($request, $response, $params));
    $router->post('/admin/team-members/{id}/home', static fn(Request $request, Response $response, array $params) => $adminTeamMemberController->setHome($request, $response, $params));

    // Program Utama
    $router->get('/admin/features', static fn(Request $request, Response $response) => $adminFeatureController->index($request, $response));
    $router->get('/admin/features/{id}', static fn(Request $request, Response $response, array $params) => $adminFeatureController->show($request, $response, $params));
    $router->post('/admin/features', static fn(Request $request, Response $response) => $adminFeatureController->store($request, $response));
    $router->post('/admin/features/upload', static fn(Request $request, Response $response) => $adminFeatureController->upload($request, $response));
    $router->post('/admin/features/{id}/update', static fn(Request $request, Response $response, array $params) => $adminFeatureController->update($request, $response, $params));
    $router->post('/admin/features/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminFeatureController->delete($request, $response, $params));
    $router->post('/admin/features/{id}/images/reorder', static fn(Request $request, Response $response, array $params) => $adminFeatureController->reorderImages($request, $response, $params));
    $router->post('/admin/features/{id}/images/{imageId}/delete', static fn(Request $request, Response $response, array $params) => $adminFeatureController->deleteImage($request, $response, $params));

    // Contact settings
    $router->get('/admin/contact-setting', static fn(Request $request, Response $response) => $adminContactSettingController->show($request, $response));
    $router->post('/admin/contact-setting', static fn(Request $request, Response $response) => $adminContactSettingController->update($request, $response));

    // Catch-all for static admin pages (must be last)
    $router->get('/admin/{page}', static fn(Request $request, Response $response, array $params) => $adminPageController->show($request, $response, $params));
});
