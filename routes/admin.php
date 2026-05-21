<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Controllers\Admin\AdminPageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\CommentSettingController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\Admin\NewsCommentController;
use App\Controllers\Admin\EventController as AdminEventController;
use App\Controllers\Admin\PrestasiController as AdminPrestasiController;
use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Admin\PhotoGalleryController as AdminPhotoGalleryController;
use App\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Controllers\Admin\ContactSettingController as AdminContactSettingController;
use App\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;

/** @var AuthController $authController */
/** @var AdminPageController $adminPageController */
/** @var AdminNewsController $adminNewsController */
/** @var AdminCategoryController $adminCategoryController */
/** @var NewsCommentController $adminNewsCommentController */
/** @var AdminEventController $adminEventController */
/** @var AdminPrestasiController $adminPrestasiController */
/** @var PrestasiTokenController $adminPrestasiTokenController */
/** @var AdminPhotoGalleryController $adminPhotoGalleryController */
/** @var AdminTeamMemberController $adminTeamMemberController */
/** @var AdminFeatureController $adminFeatureController */
/** @var AdminContactSettingController $adminContactSettingController */
/** @var CommentSettingController $adminCommentSettingController */
/** @var AdminSettingsController $adminSettingsController */
/** @var AuthMiddleware $authMiddleware */
/** @var CsrfMiddleware $csrfMiddleware */
/** @var RoleMiddleware $roleMiddleware */

// Auth routes (no auth middleware - accessible to guests)
$router->get('/admin/login', static fn(Request $request, Response $response) => $authController->showLogin($request, $response));

// Login POST and Logout require CSRF validation
$router->group([$csrfMiddleware], static function ($router) use ($authController) {
    $router->post('/admin/login', static fn(Request $request, Response $response) => $authController->login($request, $response));
    $router->post('/admin/logout', static fn(Request $request, Response $response) => $authController->logout($request, $response));
});

// Protected admin routes (require authentication + CSRF on POST + role check)
$router->group([$authMiddleware, $csrfMiddleware, $roleMiddleware], static function ($router) use (
    $adminPageController,
    $adminNewsController,
    $adminCategoryController,
    $adminNewsCommentController,
    $adminEventController,
    $adminPrestasiController,
    $adminPrestasiTokenController,
    $adminTeamMemberController,
    $adminFeatureController,
    $adminContactSettingController,
    $adminCommentSettingController,
    $adminSettingsController,
    $adminPhotoGalleryController,
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

    // Categories
    $router->get('/admin/categories', static fn(Request $request, Response $response) => $adminCategoryController->index($request, $response));
    $router->post('/admin/categories', static fn(Request $request, Response $response) => $adminCategoryController->store($request, $response));
    $router->post('/admin/categories/{id}/update', static fn(Request $request, Response $response, array $params) => $adminCategoryController->update($request, $response, $params));
    $router->post('/admin/categories/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminCategoryController->delete($request, $response, $params));

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

    // Photo Gallery CMS
    $router->get('/admin/photos', static fn(Request $request, Response $response) => $adminPhotoGalleryController->index($request, $response));
    $router->get('/admin/photos/{id}', static fn(Request $request, Response $response, array $params) => $adminPhotoGalleryController->show($request, $response, $params));
    $router->post('/admin/photos', static fn(Request $request, Response $response) => $adminPhotoGalleryController->store($request, $response));
    $router->post('/admin/photos/upload', static fn(Request $request, Response $response) => $adminPhotoGalleryController->upload($request, $response));
    $router->post('/admin/photos/{id}/update', static fn(Request $request, Response $response, array $params) => $adminPhotoGalleryController->update($request, $response, $params));
    $router->post('/admin/photos/{id}/delete', static fn(Request $request, Response $response, array $params) => $adminPhotoGalleryController->delete($request, $response, $params));

    // Live settings
    $router->get('/admin/settings', static fn(Request $request, Response $response) => $adminSettingsController->edit($request, $response));
    $router->get('/admin/settings/data', static fn(Request $request, Response $response) => $adminSettingsController->data($request, $response));
    $router->post('/admin/settings/logo', static fn(Request $request, Response $response) => $adminSettingsController->updateLogo($request, $response));
    $router->post('/admin/settings/favicon', static fn(Request $request, Response $response) => $adminSettingsController->updateFavicon($request, $response));
    $router->post('/admin/settings/topbar', static fn(Request $request, Response $response) => $adminSettingsController->updateTopbar($request, $response));
    $router->post('/admin/settings/footer', static fn(Request $request, Response $response) => $adminSettingsController->updateFooter($request, $response));
    $router->post('/admin/settings/email', static fn(Request $request, Response $response) => $adminSettingsController->updateEmail($request, $response));
    $router->post('/admin/settings/banner', static fn(Request $request, Response $response) => $adminSettingsController->updateBanner($request, $response));
    $router->post('/admin/settings/sidebar', static fn(Request $request, Response $response) => $adminSettingsController->updateSidebar($request, $response));
    $router->post('/admin/settings/color', static fn(Request $request, Response $response) => $adminSettingsController->updateColor($request, $response));
    $router->post('/admin/settings/page-home', static fn(Request $request, Response $response) => $adminSettingsController->updateHomePage($request, $response));
    $router->post('/admin/settings/upload', static fn(Request $request, Response $response) => $adminSettingsController->upload($request, $response));
    $router->get('/admin/settings/theme', static fn(Request $request, Response $response) => $adminSettingsController->showTheme($request, $response));
    $router->post('/admin/settings/theme', static fn(Request $request, Response $response) => $adminSettingsController->updateTheme($request, $response));

    // Contact settings
    $router->get('/admin/contact-setting', static fn(Request $request, Response $response) => $adminContactSettingController->show($request, $response));
    $router->post('/admin/contact-setting', static fn(Request $request, Response $response) => $adminContactSettingController->update($request, $response));

    // Comment settings
    $router->get('/admin/comment-setting', static fn(Request $request, Response $response) => $adminCommentSettingController->show($request, $response));
    $router->post('/admin/comment-setting', static fn(Request $request, Response $response) => $adminCommentSettingController->update($request, $response));

    // Catch-all for static admin pages (must be last)
    $router->get('/admin/{page}', static fn(Request $request, Response $response, array $params) => $adminPageController->show($request, $response, $params));
});
