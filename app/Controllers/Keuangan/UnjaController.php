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

    private function checkProfileComplete(int $userId): bool
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'unja'");
        $stmt->execute([$userId]);
        $profil = $stmt->fetch();
        if (!$profil) return false;

        $requiredFields = ['nama_bendahara', 'tahun_periode_awal', 'tahun_periode_akhir', 'jenis_kelamin', 'universitas', 'program_studi', 'semester_studi'];
        foreach ($requiredFields as $field) {
            if (empty($profil[$field])) return false;
        }
        return true;
    }

    private function getKegiatanList(): array
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->query("SELECT id, nama_kegiatan, divisi FROM tbl_kegiatan_keuangan WHERE tingkat = 'unja' ORDER BY id DESC");
        return $stmt->fetchAll() ?: [];
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
                t.nominal, 
                t.bukti_transaksi, 
                t.jenis_entri 
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
                'amount' => (float) $row['nominal'],
                'bukti_transaksi' => $row['bukti_transaksi'] ?? null,
                'jenis_entri' => $row['jenis_entri'] ?? 'kegiatan'
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

    public function transaksi(Request $request, Response $response): void
    {
        $db = \App\Core\Database::connection();
        $sql = "
            SELECT t.*, k.nama_kegiatan, k.divisi 
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
                'amount' => (float) $row['nominal'],
                'bukti_transaksi' => $row['bukti_transaksi'] ?? null,
                'jenis_entri' => $row['jenis_entri'] ?? 'kegiatan'
            ];
        }, $data);

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/transaksi/index.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'transaksi',
            'dummyData' => $mappedData,
            'title' => 'Transaksi Keuangan Komsat UNJA'
        ]));
    }

    public function transaksiCreate(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');

        if (!$this->checkProfileComplete($userId)) {
            \App\Core\Session::flash('swal_error', 'Harap lengkapi profil bendahara Anda terlebih dahulu sebelum menambah transaksi.');
            $response->redirect('/keuangan/bendahara/unja/profil');
            return;
        }

        $kegiatanList = $this->getKegiatanList();
        if (empty($kegiatanList)) {
            \App\Core\Session::flash('swal_error', 'Belum ada data kegiatan. Harap tambahkan kegiatan terlebih dahulu.');
            $response->redirect('/keuangan/bendahara/unja/kegiatan');
            return;
        }

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/transaksi/form.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'transaksi',
            'title' => 'Tambah Transaksi UNJA',
            'kegiatanList' => $kegiatanList,
            'isEdit' => false,
            'trx' => []
        ]));
    }

    public function transaksiStore(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            $response->redirect('/keuangan/bendahara/unja/profil');
            return;
        }

        $db = \App\Core\Database::connection();
        $stmtProf = $db->prepare("SELECT nama_bendahara, tahun_periode_awal, tahun_periode_akhir FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'unja'");
        $stmtProf->execute([$userId]);
        $profil = $stmtProf->fetch();
        $dicatatOleh = $profil['nama_bendahara'] ?? '';
        $periode = ($profil['tahun_periode_awal'] ?? '') . '/' . ($profil['tahun_periode_akhir'] ?? '');

        $kegiatan_id = $_POST['kegiatan_id'] ?? '';
        $jenis_entri = $_POST['jenis_entri'] ?? 'kegiatan';
        $tipe_transaksi = $_POST['tipe_transaksi'] ?? '';
        $nominal = $_POST['nominal'] ?? '';
        $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
        $keterangan_transaksi = trim((string)($_POST['keterangan_transaksi'] ?? ''));
        $alokasi_dana = trim((string)($_POST['alokasi_dana'] ?? ''));
        $sumber_dana = trim((string)($_POST['sumber_dana'] ?? ''));
        $input_mode = $_POST['input_mode'] ?? 'file'; 
        $bukti_link = trim((string)($_POST['bukti_link'] ?? ''));

        $errors = [];
        $error_fields = [];
        if ($jenis_entri === 'kegiatan' && empty($kegiatan_id)) { $errors[] = 'Kegiatan harus dipilih.'; $error_fields[] = 'kegiatan_id'; }
        if (!in_array($tipe_transaksi, ['pemasukan', 'pengeluaran'])) { $errors[] = 'Tipe transaksi tidak valid.'; $error_fields[] = 'tipe_transaksi'; }
        if (!is_numeric($nominal) || $nominal <= 0) { $errors[] = 'Nominal harus berupa angka lebih dari 0.'; $error_fields[] = 'nominal'; }
        if (empty($tanggal_transaksi)) { $errors[] = 'Tanggal transaksi harus diisi.'; $error_fields[] = 'tanggal_transaksi'; }
        if (empty($keterangan_transaksi)) { $errors[] = 'Keterangan transaksi harus diisi.'; $error_fields[] = 'keterangan_transaksi'; }
        if (empty($alokasi_dana)) { $errors[] = 'Alokasi dana harus diisi.'; $error_fields[] = 'alokasi_dana'; }

        $bukti_transaksi = null;

        if (empty($errors)) {
            if ($input_mode === 'link') {
                if (empty($bukti_link) || !filter_var($bukti_link, FILTER_VALIDATE_URL)) {
                    $errors[] = 'Link Google Drive tidak valid.';
                    $error_fields[] = 'bukti_link';
                } else {
                    $bukti_transaksi = $bukti_link;
                }
            } else {
                if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['bukti_file']['tmp_name'];
                    $fileName = $_FILES['bukti_file']['name'];
                    $fileSize = $_FILES['bukti_file']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $isPdf = $fileExt === 'pdf';
                    $isImg = in_array($fileExt, ['jpg', 'jpeg', 'png']);

                    if (!$isPdf && !$isImg) {
                        $errors[] = 'Format file harus PDF, JPG, JPEG, atau PNG.';
                        $error_fields[] = 'bukti_file';
                    } else {
                        if ($isPdf && $fileSize > 10 * 1024 * 1024) {
                            $errors[] = 'Ukuran PDF maksimal 10 MB.';
                            $error_fields[] = 'bukti_file';
                        } elseif ($isImg && $fileSize > 5 * 1024 * 1024) {
                            $errors[] = 'Ukuran gambar maksimal 5 MB.';
                            $error_fields[] = 'bukti_file';
                        } else {
                            $uploadDir = dirname(__DIR__, 3) . '/public/uploads/keuangan/';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                            $newFileName = 'trx_unja_' . time() . '_' . uniqid() . '.' . $fileExt;
                            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                                $bukti_transaksi = $newFileName;
                            } else {
                                $errors[] = 'Gagal mengupload file bukti.';
                                $error_fields[] = 'bukti_file';
                            }
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            \App\Core\Session::flash('swal_error', implode("<br>", $errors));
            \App\Core\Session::flash('error_fields', $error_fields);
            \App\Core\Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/unja/transaksi/tambah');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO tbl_transaksi_unja 
                (user_id, kegiatan_id, jenis_entri, dicatat_oleh, periode_kepengurusan, tipe_transaksi, nominal, tanggal_transaksi, keterangan_transaksi, alokasi_dana, sumber_dana, bukti_transaksi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId, $kegiatan_id ?: null, $jenis_entri, $dicatatOleh, $periode, $tipe_transaksi, $nominal, $tanggal_transaksi, $keterangan_transaksi, $alokasi_dana, $sumber_dana, $bukti_transaksi
            ]);
            \App\Core\Session::flash('swal_success', 'Transaksi berhasil ditambahkan.');
            $response->redirect('/keuangan/bendahara/unja/transaksi');
        } catch (\PDOException $e) {
            \App\Core\Session::flash('swal_error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
            $response->redirect('/keuangan/bendahara/unja/transaksi/tambah');
        }
    }

    public function transaksiEdit(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            \App\Core\Session::flash('swal_error', 'Harap lengkapi profil bendahara Anda.');
            $response->redirect('/keuangan/bendahara/unja/profil');
            return;
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_transaksi_unja WHERE id = ?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();

        if (!$trx) {
            \App\Core\Session::flash('swal_error', 'Transaksi tidak ditemukan.');
            $response->redirect('/keuangan/bendahara/unja/transaksi');
            return;
        }

        $kegiatanList = $this->getKegiatanList();

        $response->html($this->view->renderWithLayout('keuangan/bendahara/unja/transaksi/form.php', 'layouts/bendaharaKomsatUnja.php', [
            'activeMenu' => 'transaksi',
            'title' => 'Edit Transaksi UNJA',
            'kegiatanList' => $kegiatanList,
            'isEdit' => true,
            'trx' => $trx
        ]));
    }

    public function transaksiUpdate(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            $response->redirect('/keuangan/bendahara/unja/profil');
            return;
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_transaksi_unja WHERE id = ?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();

        if (!$trx) {
            \App\Core\Session::flash('swal_error', 'Transaksi tidak ditemukan.');
            $response->redirect('/keuangan/bendahara/unja/transaksi');
            return;
        }

        $kegiatan_id = $_POST['kegiatan_id'] ?? '';
        $jenis_entri = $_POST['jenis_entri'] ?? 'kegiatan';
        $tipe_transaksi = $_POST['tipe_transaksi'] ?? '';
        $nominal = $_POST['nominal'] ?? '';
        $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
        $keterangan_transaksi = trim((string)($_POST['keterangan_transaksi'] ?? ''));
        $alokasi_dana = trim((string)($_POST['alokasi_dana'] ?? ''));
        $sumber_dana = trim((string)($_POST['sumber_dana'] ?? ''));
        $input_mode = $_POST['input_mode'] ?? 'file'; 
        $bukti_link = trim((string)($_POST['bukti_link'] ?? ''));

        $errors = [];
        $error_fields = [];
        if ($jenis_entri === 'kegiatan' && empty($kegiatan_id)) { $errors[] = 'Kegiatan harus dipilih.'; $error_fields[] = 'kegiatan_id'; }
        if (!in_array($tipe_transaksi, ['pemasukan', 'pengeluaran'])) { $errors[] = 'Tipe transaksi tidak valid.'; $error_fields[] = 'tipe_transaksi'; }
        if (!is_numeric($nominal) || $nominal <= 0) { $errors[] = 'Nominal harus berupa angka lebih dari 0.'; $error_fields[] = 'nominal'; }
        if (empty($tanggal_transaksi)) { $errors[] = 'Tanggal transaksi harus diisi.'; $error_fields[] = 'tanggal_transaksi'; }
        if (empty($keterangan_transaksi)) { $errors[] = 'Keterangan transaksi harus diisi.'; $error_fields[] = 'keterangan_transaksi'; }
        if (empty($alokasi_dana)) { $errors[] = 'Alokasi dana harus diisi.'; $error_fields[] = 'alokasi_dana'; }

        $bukti_transaksi = $trx['bukti_transaksi'];

        if (empty($errors)) {
            if ($input_mode === 'link') {
                if (!empty($bukti_link) && filter_var($bukti_link, FILTER_VALIDATE_URL)) {
                    $bukti_transaksi = $bukti_link;
                }
            } else {
                if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['bukti_file']['tmp_name'];
                    $fileName = $_FILES['bukti_file']['name'];
                    $fileSize = $_FILES['bukti_file']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $isPdf = $fileExt === 'pdf';
                    $isImg = in_array($fileExt, ['jpg', 'jpeg', 'png']);

                    if (!$isPdf && !$isImg) {
                        $errors[] = 'Format file harus PDF, JPG, JPEG, atau PNG.';
                        $error_fields[] = 'bukti_file';
                    } else {
                        if ($isPdf && $fileSize > 10 * 1024 * 1024) {
                            $errors[] = 'Ukuran PDF maksimal 10 MB.';
                            $error_fields[] = 'bukti_file';
                        } elseif ($isImg && $fileSize > 5 * 1024 * 1024) {
                            $errors[] = 'Ukuran gambar maksimal 5 MB.';
                            $error_fields[] = 'bukti_file';
                        } else {
                            $uploadDir = dirname(__DIR__, 3) . '/public/uploads/keuangan/';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                            $newFileName = 'trx_unja_' . time() . '_' . uniqid() . '.' . $fileExt;
                            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                                $bukti_transaksi = $newFileName;
                            } else {
                                $errors[] = 'Gagal mengupload file bukti.';
                                $error_fields[] = 'bukti_file';
                            }
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            \App\Core\Session::flash('swal_error', implode("<br>", $errors));
            \App\Core\Session::flash('error_fields', $error_fields);
            \App\Core\Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/unja/transaksi/edit/' . $id);
            return;
        }

        try {
            $updateStmt = $db->prepare("
                UPDATE tbl_transaksi_unja 
                SET kegiatan_id = ?, jenis_entri = ?, tipe_transaksi = ?, nominal = ?, tanggal_transaksi = ?, keterangan_transaksi = ?, alokasi_dana = ?, sumber_dana = ?, bukti_transaksi = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $kegiatan_id ?: null, $jenis_entri, $tipe_transaksi, $nominal, $tanggal_transaksi, $keterangan_transaksi, $alokasi_dana, $sumber_dana, $bukti_transaksi, $id
            ]);

            \App\Core\Session::flash('swal_success', 'Transaksi berhasil diperbarui.');
            $response->redirect('/keuangan/bendahara/unja/transaksi');
        } catch (\PDOException $e) {
            \App\Core\Session::flash('swal_error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
            $response->redirect('/keuangan/bendahara/unja/transaksi/edit/' . $id);
        }
    }

    public function transaksiHapus(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("DELETE FROM tbl_transaksi_unja WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            \App\Core\Session::flash('swal_success', 'Transaksi berhasil dihapus.');
        } else {
            \App\Core\Session::flash('swal_error', 'Gagal menghapus transaksi.');
        }

        $response->redirect('/keuangan/bendahara/unja/transaksi');
    }
}
