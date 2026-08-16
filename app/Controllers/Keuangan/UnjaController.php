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

    public function kegiatan(Request $request, Response $response): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_kegiatan_keuangan WHERE tingkat = 'unja' ORDER BY created_at DESC");
        $stmt->execute();
        $kegiatan = $stmt->fetchAll();

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/kegiatan/index.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'kegiatan',
            'kegiatan' => $kegiatan,
            'title' => 'Kegiatan Komsat UNJA'
        ]));
    }

    public function tambahKegiatan(Request $request, Response $response): void
    {
        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/kegiatan/form.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'kegiatan',
            'isEdit' => false,
            'kegiatan' => [],
            'title' => 'Tambah Kegiatan Komsat UNJA'
        ]));
    }

    public function storeKegiatan(Request $request, Response $response): void
    {
        $nama_kegiatan = trim((string) ($_POST['nama_kegiatan'] ?? ''));
        $divisi = trim((string) ($_POST['divisi'] ?? ''));
        $tanggal_mulai = trim((string) ($_POST['tanggal_mulai'] ?? ''));
        $tanggal_selesai = trim((string) ($_POST['tanggal_selesai'] ?? ''));
        $keterangan_kegiatan = trim((string) ($_POST['keterangan_kegiatan'] ?? ''));
        $tingkat = trim((string) ($_POST['tingkat'] ?? 'unja'));

        $errors = [];
        if ($nama_kegiatan === '') $errors['nama_kegiatan'] = 'Nama kegiatan harus diisi.';
        if ($tanggal_mulai === '') $errors['tanggal_mulai'] = 'Tanggal mulai harus diisi.';

        if ($tanggal_selesai !== '' && $tanggal_mulai !== '' && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
            $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        if ($tingkat !== 'unja') {
            $errors['tingkat'] = 'Akses ditolak: Anda hanya dapat menambahkan kegiatan untuk tingkat Komsat UNJA.';
        }

        if (!empty($errors)) {
            \App\Core\Session::flash('errors', $errors);
            \App\Core\Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/unja/kegiatan/tambah');
            return;
        }

        $userId = \App\Core\Session::get('keuangan_user_id');

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("
            INSERT INTO tbl_kegiatan_keuangan 
            (user_id, nama_kegiatan, tingkat, divisi, tanggal_mulai, tanggal_selesai, keterangan_kegiatan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $nama_kegiatan,
            'unja',
            $divisi ?: null,
            $tanggal_mulai ?: null,
            $tanggal_selesai ?: null,
            $keterangan_kegiatan ?: null
        ]);

        \App\Core\Session::flash('swal_success', 'Kegiatan berhasil ditambahkan!');
        $response->redirect('/keuangan/bendahara/unja/kegiatan');
    }

    public function editKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'unja'");
        $stmt->execute([$id]);
        $kegiatan = $stmt->fetch();

        if (!$kegiatan) {
            \App\Core\Session::flash('swal_error', 'Kegiatan tidak ditemukan atau bukan tingkat UNJA.');
            $response->redirect('/keuangan/bendahara/unja/kegiatan');
            return;
        }

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/kegiatan/form.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'kegiatan',
            'isEdit' => true,
            'kegiatan' => $kegiatan,
            'title' => 'Edit Kegiatan Komsat UNJA'
        ]));
    }

    public function updateKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT id FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'unja'");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            \App\Core\Session::flash('swal_error', 'Kegiatan tidak valid.');
            $response->redirect('/keuangan/bendahara/unja/kegiatan');
            return;
        }

        $nama_kegiatan = trim((string) ($_POST['nama_kegiatan'] ?? ''));
        $divisi = trim((string) ($_POST['divisi'] ?? ''));
        $tanggal_mulai = trim((string) ($_POST['tanggal_mulai'] ?? ''));
        $tanggal_selesai = trim((string) ($_POST['tanggal_selesai'] ?? ''));
        $keterangan_kegiatan = trim((string) ($_POST['keterangan_kegiatan'] ?? ''));
        $tingkat = trim((string) ($_POST['tingkat'] ?? 'unja'));

        $errors = [];
        if ($nama_kegiatan === '') $errors['nama_kegiatan'] = 'Nama kegiatan harus diisi.';
        if ($tanggal_mulai === '') $errors['tanggal_mulai'] = 'Tanggal mulai harus diisi.';

        if ($tanggal_selesai !== '' && $tanggal_mulai !== '' && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
            $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        if ($tingkat !== 'unja') {
            $errors['tingkat'] = 'Akses ditolak: Anda hanya dapat mengubah kegiatan untuk tingkat Komsat UNJA.';
        }

        if (!empty($errors)) {
            \App\Core\Session::flash('errors', $errors);
            \App\Core\Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/unja/kegiatan/edit/' . $id);
            return;
        }

        $updateStmt = $db->prepare("
            UPDATE tbl_kegiatan_keuangan 
            SET nama_kegiatan = ?, divisi = ?, tanggal_mulai = ?, tanggal_selesai = ?, keterangan_kegiatan = ?
            WHERE id = ? AND tingkat = 'unja'
        ");
        $updateStmt->execute([
            $nama_kegiatan,
            $divisi ?: null,
            $tanggal_mulai ?: null,
            $tanggal_selesai ?: null,
            $keterangan_kegiatan ?: null,
            $id
        ]);

        \App\Core\Session::flash('swal_success', 'Kegiatan berhasil diperbarui!');
        $response->redirect('/keuangan/bendahara/unja/kegiatan');
    }

    public function hapusKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("DELETE FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'unja'");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            \App\Core\Session::flash('swal_success', 'Kegiatan berhasil dihapus.');
        } else {
            \App\Core\Session::flash('swal_error', 'Gagal menghapus kegiatan.');
        }

        $response->redirect('/keuangan/bendahara/unja/kegiatan');
    }
}
