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

    private function checkProfileComplete(int $userId): bool
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'uin'");
        $stmt->execute([$userId]);
        $profil = $stmt->fetch();
        if (!$profil) return false;

        $requiredFields = ['nama_bendahara', 'tahun_periode_awal', 'tahun_periode_akhir', 'jenis_kelamin', 'universitas', 'program_studi', 'semester_studi'];
        foreach ($requiredFields as $field) {
            if (empty($profil[$field])) return false;
        }
        return true;
    }

    public function dashboard(Request $request, Response $response): void
    {
        $db = \App\Core\Database::connection();

        $sql = "
            SELECT 
                t.id, 
                t.tanggal_transaksi, 
                t.keterangan_transaksi, 
                t.alokasi_dana, 
                t.tipe_transaksi, 
                t.nominal 
            FROM tbl_transaksi_uin t
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
                'amount' => (float) $row['nominal']
            ];
        }, $data);

        $response->html($this->view->renderWithLayout('keuangan/bendahara/uin/dashboard.php', 'layouts/bendaharaKomsatUIN.php', [
            'activeMenu' => 'dashboard',
            'dummyData' => $mappedData,
            'title' => 'Dashboard Keuangan Komsat UIN'
        ]));
    }

    public function profil(Request $request, Response $response): void
    {
        // TODO: Later if we migrate profil, we will update this
        $response->html($this->view->renderWithLayout('keuangan/bendahara/uin/profil.php', 'layouts/bendaharaKomsatUIN.php', [
            'activeMenu' => 'profil'
        ]));
    }
}