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
        $db = \App\Core\Database::connection();
        $sql = "
            SELECT t.*, k.nama_kegiatan, k.divisi 
            FROM tbl_transaksi_wilayah t
            LEFT JOIN tbl_kegiatan_keuangan k ON t.kegiatan_id = k.id
            ORDER BY t.tanggal_transaksi DESC, t.id DESC
        ";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll() ?: [];

        $mappedData = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'date' => $row['tanggal_transaksi'],
                'desc' => $row['keterangan_transaksi'],
                'category' => $row['alokasi_dana'] ?: 'Umum',
                'type' => $row['tipe_transaksi'] === 'pemasukan' ? 'in' : 'out',
                'amount' => (float) $row['nominal'],
                'proof' => $row['bukti_transaksi'],
                'source' => $row['sumber_dana'],
                'event' => $row['nama_kegiatan'],
                'period' => $row['periode_kepengurusan']
            ];
        }, $data);

        $response->html($this->renderer->renderWithLayout('keuangan/anggota/wilayah.php', 'layouts/anggota.php', [
            'activeNav' => 'wilayah',
            'title' => 'Keuangan Wilayah',
            'transactions' => $mappedData
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
