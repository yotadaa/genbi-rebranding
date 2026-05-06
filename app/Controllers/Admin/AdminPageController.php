<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Services\CsrfService;

final class AdminPageController
{
    public function __construct(private StaticPageRenderer $renderer)
    {
    }

    public function dashboard(Request $request, Response $response): void
    {
        $response->html($this->renderer->render('admin/dashboard.html', [
            'noindex' => true,
            'csrf_token' => CsrfService::token(),
        ]), 200, [
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /** @param array{page?: string} $params */
    public function show(Request $request, Response $response, array $params): void
    {
        $page = preg_replace('/[^a-z0-9_-]/i', '', $params['page'] ?? 'dashboard') ?: 'dashboard';
        $response->html($this->renderer->render('admin/' . $page . '.html', [
            'noindex' => true,
            'csrf_token' => CsrfService::token(),
        ]), 200, [
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
