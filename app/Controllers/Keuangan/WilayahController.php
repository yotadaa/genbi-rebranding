<?php

declare(strict_types=1);

namespace App\Controllers\Keuangan;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Core\Database;
use App\Core\Session;

final class WilayahController
{
    public function __construct(private ViewRenderer $renderer) {}

    public function dashboard(Request $request, Response $response): void
    {
        $db = \App\Core\Database::connection();

        $sql = "
            SELECT 
                t.id, 
                t.tanggal_transaksi as date, 
                t.keterangan_transaksi as `desc`, 
                COALESCE(k.divisi, 'Umum') as category, 
                t.tipe_transaksi, 
                t.nominal as amount 
            FROM tbl_transaksi_wilayah t
            LEFT JOIN tbl_kegiatan_keuangan k ON t.kegiatan_id = k.id
            ORDER BY t.tanggal_transaksi DESC, t.id DESC
        ";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll();

        $mappedData = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'date' => $row['date'],
                'desc' => $row['desc'],
                'category' => $row['category'] ?: 'Umum',
                'type' => $row['tipe_transaksi'] === 'pemasukan' ? 'in' : 'out',
                'amount' => (float) $row['amount']
            ];
        }, $data);

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/dashboard.php', 'layouts/bendahara.php', [
            'activeMenu' => 'dashboard',
            'dummyData' => $mappedData,
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

    public function kegiatan(Request $request, Response $response): void
    {
        $db = Database::connection();
        // Hanya tampilkan tingkat wilayah
        $stmt = $db->prepare("SELECT * FROM tbl_kegiatan_keuangan WHERE tingkat = 'wilayah' ORDER BY created_at DESC");
        $stmt->execute();
        $kegiatan = $stmt->fetchAll();

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/kegiatan/index.php', 'layouts/bendahara.php', [
            'activeMenu' => 'kegiatan',
            'kegiatan' => $kegiatan,
            'title' => 'Kegiatan Wilayah'
        ]));
    }

    public function tambahKegiatan(Request $request, Response $response): void
    {
        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/kegiatan/form.php', 'layouts/bendahara.php', [
            'activeMenu' => 'kegiatan',
            'isEdit' => false,
            'kegiatan' => [],
            'title' => 'Tambah Kegiatan Wilayah'
        ]));
    }

    public function storeKegiatan(Request $request, Response $response): void
    {
        $nama_kegiatan = trim((string) ($_POST['nama_kegiatan'] ?? ''));
        $divisi = trim((string) ($_POST['divisi'] ?? ''));
        $tanggal_mulai = trim((string) ($_POST['tanggal_mulai'] ?? ''));
        $tanggal_selesai = trim((string) ($_POST['tanggal_selesai'] ?? ''));
        $keterangan_kegiatan = trim((string) ($_POST['keterangan_kegiatan'] ?? ''));
        $tingkat = trim((string) ($_POST['tingkat'] ?? 'wilayah'));

        $errors = [];
        if ($nama_kegiatan === '') $errors['nama_kegiatan'] = 'Nama kegiatan harus diisi.';
        if ($tanggal_mulai === '') $errors['tanggal_mulai'] = 'Tanggal mulai harus diisi.';
        
        if ($tanggal_selesai !== '' && $tanggal_mulai !== '' && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
            $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        // Strict validation for Role bendahara_wilayah -> tingkat MUST be wilayah
        if ($tingkat !== 'wilayah') {
            $errors['tingkat'] = 'Akses ditolak: Anda hanya dapat menambahkan kegiatan untuk tingkat Wilayah.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/wilayah/kegiatan/tambah');
            return;
        }

        $userId = Session::get('keuangan_user_id');

        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO tbl_kegiatan_keuangan 
            (user_id, nama_kegiatan, tingkat, divisi, tanggal_mulai, tanggal_selesai, keterangan_kegiatan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $nama_kegiatan,
            'wilayah', // Force as 'wilayah' regardless of input
            $divisi ?: null,
            $tanggal_mulai ?: null,
            $tanggal_selesai ?: null,
            $keterangan_kegiatan ?: null
        ]);

        Session::flash('success', 'Kegiatan berhasil ditambahkan!');
        $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
    }

    public function editKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'wilayah'");
        $stmt->execute([$id]);
        $kegiatan = $stmt->fetch();

        if (!$kegiatan) {
            Session::flash('error', 'Kegiatan tidak ditemukan atau bukan tingkat wilayah.');
            $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
            return;
        }

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/kegiatan/form.php', 'layouts/bendahara.php', [
            'activeMenu' => 'kegiatan',
            'isEdit' => true,
            'kegiatan' => $kegiatan,
            'title' => 'Edit Kegiatan Wilayah'
        ]));
    }

    public function updateKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;

        $db = Database::connection();
        // Pastikan data exist dan milik wilayah
        $stmt = $db->prepare("SELECT id FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'wilayah'");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            Session::flash('error', 'Kegiatan tidak valid.');
            $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
            return;
        }

        $nama_kegiatan = trim((string) ($_POST['nama_kegiatan'] ?? ''));
        $divisi = trim((string) ($_POST['divisi'] ?? ''));
        $tanggal_mulai = trim((string) ($_POST['tanggal_mulai'] ?? ''));
        $tanggal_selesai = trim((string) ($_POST['tanggal_selesai'] ?? ''));
        $keterangan_kegiatan = trim((string) ($_POST['keterangan_kegiatan'] ?? ''));
        $tingkat = trim((string) ($_POST['tingkat'] ?? 'wilayah'));

        $errors = [];
        if ($nama_kegiatan === '') $errors['nama_kegiatan'] = 'Nama kegiatan harus diisi.';
        if ($tanggal_mulai === '') $errors['tanggal_mulai'] = 'Tanggal mulai harus diisi.';

        if ($tanggal_selesai !== '' && $tanggal_mulai !== '' && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
            $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        if ($tingkat !== 'wilayah') {
            $errors['tingkat'] = 'Akses ditolak: Anda hanya dapat mengubah kegiatan untuk tingkat Wilayah.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/wilayah/kegiatan/edit/' . $id);
            return;
        }

        $updateStmt = $db->prepare("
            UPDATE tbl_kegiatan_keuangan 
            SET nama_kegiatan = ?, divisi = ?, tanggal_mulai = ?, tanggal_selesai = ?, keterangan_kegiatan = ?
            WHERE id = ? AND tingkat = 'wilayah'
        ");
        $updateStmt->execute([
            $nama_kegiatan,
            $divisi ?: null,
            $tanggal_mulai ?: null,
            $tanggal_selesai ?: null,
            $keterangan_kegiatan ?: null,
            $id
        ]);

        Session::flash('success', 'Kegiatan berhasil diperbarui!');
        $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
    }

    public function hapusKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;

        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'wilayah'");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            Session::flash('success', 'Kegiatan berhasil dihapus.');
        } else {
            Session::flash('error', 'Gagal menghapus kegiatan.');
        }

        $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
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
