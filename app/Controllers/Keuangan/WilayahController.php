<?php

declare(strict_types=1);

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;

final class WilayahController
{
    public function __construct(private ViewRenderer $renderer) {}

    public function dashboard(Request $request, Response $response): void
    {
        $dummyData = $this->getDummyData();
        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/dashboard.php', 'layouts/bendahara.php', [
            'activeMenu' => 'dashboard',
            'dummyData' => $dummyData,
            'title' => 'Dashboard Bendahara Wilayah'
        ]));
    }

    public function transaksi(Request $request, Response $response): void
    {
        $dummyData = $this->getDummyData();
        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/transaksi.php', 'layouts/bendahara.php', [
            'activeMenu' => 'transaksi',
            'dummyData' => $dummyData,
            'title' => 'Transaksi Keuangan Wilayah'
        ]));
    }

    public function profil(Request $request, Response $response): void
    {
        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/profil.php', 'layouts/bendahara.php', [
            'activeMenu' => 'profil',
            'title' => 'Profil Bendahara Wilayah'
        ]));
    }

    private function getDummyData(): array
    {
        return [
            ['id' => 1, 'date' => '2026-08-01', 'desc' => 'Pencairan Dana Kas GenBI', 'category' => 'BPI', 'type' => 'in', 'amount' => 5000000],
            ['id' => 2, 'date' => '2026-08-03', 'desc' => 'Langganan Domain & Hosting', 'category' => 'Tim IT', 'type' => 'out', 'amount' => 1200000],
            ['id' => 3, 'date' => '2026-08-05', 'desc' => 'Biaya Iklan Instagram', 'category' => 'Tim Media Wilayah', 'type' => 'out', 'amount' => 300000],
            ['id' => 4, 'date' => '2026-08-10', 'desc' => 'Sponsorship Bank Indonesia', 'category' => 'BPI', 'type' => 'in', 'amount' => 10000000],
            ['id' => 5, 'date' => '2026-08-12', 'desc' => 'Pembuatan Video Profil', 'category' => 'Tim Media Wilayah', 'type' => 'out', 'amount' => 1500000],
            ['id' => 6, 'date' => '2026-08-14', 'desc' => 'Maintenance Server', 'category' => 'Tim IT', 'type' => 'out', 'amount' => 500000],
        ];
    }
}
