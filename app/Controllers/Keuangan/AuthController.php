<?php

declare(strict_types=1);

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;

final class AuthController
{
    public function __construct(private ViewRenderer $renderer) {}

    public function showLogin(Request $request, Response $response): void
    {
        $response->html($this->renderer->render('keuangan/login.php'));
    }

    public function showRegister(Request $request, Response $response): void
    {
        $response->html($this->renderer->render('keuangan/register.php'));
    }
}
