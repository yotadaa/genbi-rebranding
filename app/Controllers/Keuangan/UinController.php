<?php

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;

class UinController
{
    private ViewRenderer $view;

    public function __construct(ViewRenderer $view)
    {
        $this->view = $view;
    }

    public function dashboard(Request $request, Response $response): void
    {
        $response->html($this->view->render('keuangan/uin/dashboard.php'));
    }
}
