<?php

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;

class UnjaController
{
    private ViewRenderer $view;

    public function __construct(ViewRenderer $view)
    {
        $this->view = $view;
    }

    public function dashboard(Request $request, Response $response): void
    {
        $response->html($this->view->render('keuangan/unja/dashboard.php', [
            'activeMenu' => 'dashboard'
        ]));
    }

    public function profil(Request $request, Response $response): void
    {
        $response->html($this->view->render('keuangan/unja/profil.php', [
            'activeMenu' => 'profil'
        ]));
    }
}
