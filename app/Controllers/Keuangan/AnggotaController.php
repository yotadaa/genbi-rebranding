<?php

declare(strict_types=1);

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;

final class AnggotaController
{
    public function __construct(private ViewRenderer $renderer) {}

    public function wilayah(Request $request, Response $response): void
    {
        $response->html($this->renderer->renderWithLayout('keuangan/anggota/wilayah.php', 'layouts/anggota.php', [
            'activeNav' => 'wilayah'
        ]));
    }

    public function unja(Request $request, Response $response): void
    {
        $response->html($this->renderer->renderWithLayout('keuangan/anggota/unja.php', 'layouts/anggota.php', [
            'activeNav' => 'unja'
        ]));
    }

    public function uin(Request $request, Response $response): void
    {
        $response->html($this->renderer->renderWithLayout('keuangan/anggota/uin.php', 'layouts/anggota.php', [
            'activeNav' => 'uin'
        ]));
    }
}
