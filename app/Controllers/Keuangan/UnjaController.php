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
        $db = \App\Core\Database::connection();

        $sql = "
            SELECT 
                t.id, 
                t.tanggal_transaksi, 
                t.keterangan_transaksi, 
                t.alokasi_dana, 
                t.tipe_transaksi, 
                t.nominal 
            FROM tbl_transaksi_unja t
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

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/dashboard.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'dashboard',
            'dummyData' => $mappedData,
            'title' => 'Dashboard Bendahara Komsat UNJA'
        ]));
    }

    public function profil(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');
        $db = \App\Core\Database::connection();

        // Ambil data user untuk email
        $stmtUser = $db->prepare("SELECT email FROM tbl_user WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch();

        // Ambil profil bendahara unja
        $stmtProfil = $db->prepare("SELECT * FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'unja'");
        $stmtProfil->execute([$userId]);
        $profil = $stmtProfil->fetch() ?: [];

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/profil.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'profil',
            'title' => 'Profil Bendahara Komsat UNJA',
            'user' => $user,
            'profil' => $profil
        ]));
    }

    public function updateProfil(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');

        $nama_bendahara = trim((string) ($_POST['nama_bendahara'] ?? ''));
        $tahun_periode_awal = trim((string) ($_POST['tahun_periode_awal'] ?? ''));
        $tahun_periode_akhir = trim((string) ($_POST['tahun_periode_akhir'] ?? ''));
        $jenis_kelamin = trim((string) ($_POST['jenis_kelamin'] ?? ''));
        $universitas = trim((string) ($_POST['universitas'] ?? ''));
        $program_studi = trim((string) ($_POST['program_studi'] ?? ''));
        $semester_studi = trim((string) ($_POST['semester_studi'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        $errors = [];
        if ($nama_bendahara === '') $errors['nama_bendahara'] = 'Nama bendahara harus diisi.';
        if ($tahun_periode_awal === '') $errors['tahun_periode_awal'] = 'Tahun periode awal harus diisi.';
        if ($tahun_periode_akhir === '') $errors['tahun_periode_akhir'] = 'Tahun periode akhir harus diisi.';
        if ($jenis_kelamin === '') $errors['jenis_kelamin'] = 'Jenis kelamin harus diisi.';
        if ($universitas === '') $errors['universitas'] = 'Universitas harus diisi.';
        if ($program_studi === '') $errors['program_studi'] = 'Program studi harus diisi.';
        if ($semester_studi === '') $errors['semester_studi'] = 'Semester studi harus diisi.';
        if ($email === '') $errors['email'] = 'Email harus diisi.';
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Format email tidak valid.';

        if (!empty($errors)) {
            \App\Core\Session::flash('errors', $errors);
            \App\Core\Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/unja/profil');
            return;
        }

        $db = \App\Core\Database::connection();

        try {
            $db->beginTransaction();

            // Update email di tbl_user
            $stmtUser = $db->prepare("UPDATE tbl_user SET email = ? WHERE id = ?");
            $stmtUser->execute([$email, $userId]);

            // Cek apakah profil sudah ada
            $stmtCek = $db->prepare("SELECT id FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'unja'");
            $stmtCek->execute([$userId]);
            $profilAda = $stmtCek->fetch();

            if ($profilAda) {
                // Update
                $stmtUpdate = $db->prepare("
                    UPDATE tbl_profil_bendahara 
                    SET nama_bendahara = ?, tahun_periode_awal = ?, tahun_periode_akhir = ?, jenis_kelamin = ?, universitas = ?, program_studi = ?, semester_studi = ?
                    WHERE user_id = ? AND tempat = 'unja'
                ");
                $stmtUpdate->execute([
                    $nama_bendahara,
                    $tahun_periode_awal,
                    $tahun_periode_akhir,
                    $jenis_kelamin,
                    $universitas,
                    $program_studi,
                    (int)$semester_studi,
                    $userId
                ]);
            } else {
                // Insert
                $stmtInsert = $db->prepare("
                    INSERT INTO tbl_profil_bendahara 
                    (user_id, nama_bendahara, tahun_periode_awal, tahun_periode_akhir, tempat, jenis_kelamin, universitas, program_studi, semester_studi)
                    VALUES (?, ?, ?, ?, 'unja', ?, ?, ?, ?)
                ");
                $stmtInsert->execute([
                    $userId,
                    $nama_bendahara,
                    $tahun_periode_awal,
                    $tahun_periode_akhir,
                    $jenis_kelamin,
                    $universitas,
                    $program_studi,
                    (int)$semester_studi
                ]);
            }

            $db->commit();
            \App\Core\Session::flash('swal_success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            $db->rollBack();
            \App\Core\Session::flash('swal_error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }

        $response->redirect('/keuangan/bendahara/unja/profil');
    }
}
