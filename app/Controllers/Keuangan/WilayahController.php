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
                t.tanggal_transaksi, 
                t.keterangan_transaksi, 
                t.alokasi_dana, 
                t.tipe_transaksi, 
                t.nominal, 
                t.bukti_transaksi, 
                t.jenis_entri 
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
                'bukti_transaksi' => $row['bukti_transaksi'] ?? null,
                'jenis_entri' => $row['jenis_entri'] ?? 'kegiatan'
            ];
        }, $data);

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/dashboard.php', 'layouts/bendahara.php', [
            'activeMenu' => 'dashboard',
            'dummyData' => $mappedData,
            'title' => 'Dashboard Bendahara Wilayah'
        ]));
    }

    private function checkProfileComplete(int $userId): bool
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'wilayah'");
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
        $stmt = $db->query("SELECT id, nama_kegiatan, divisi FROM tbl_kegiatan_keuangan ORDER BY id DESC");
        return $stmt->fetchAll() ?: [];
    }

    public function transaksi(Request $request, Response $response): void
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

        // format into what JS needs if we still want JS to render, 
        // or we just pass the raw data and render in PHP.
        // The frontend currently uses JS (bendahara.js) which expects 'date', 'desc', 'category', 'type', 'amount', 'id'.
        $mappedData = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'date' => $row['tanggal_transaksi'],
                'desc' => $row['keterangan_transaksi'],
                'category' => $row['alokasi_dana'] ?: 'Umum',
                'type' => $row['tipe_transaksi'] === 'pemasukan' ? 'in' : 'out',
                'amount' => (float) $row['nominal'],
                'bukti_transaksi' => $row['bukti_transaksi'] ?? null
            ];
        }, $data);

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/transaksi.php', 'layouts/bendahara.php', [
            'activeMenu' => 'transaksi',
            'dummyData' => $mappedData,
            'title' => 'Transaksi Keuangan Wilayah'
        ]));
    }

    public function transaksiCreate(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');

        if (!$this->checkProfileComplete($userId)) {
            \App\Core\Session::flash('swal_error', 'Harap lengkapi profil bendahara Anda terlebih dahulu sebelum menambah transaksi.');
            $response->redirect('/keuangan/bendahara/wilayah/profil');
            return;
        }

        $kegiatanList = $this->getKegiatanList();
        if (empty($kegiatanList)) {
            \App\Core\Session::flash('swal_error', 'Belum ada data kegiatan. Harap tambahkan kegiatan terlebih dahulu.');
            // Jika ada route kegiatan, redirect kesana. Sementara kembali ke dashboard.
            $response->redirect('/keuangan/bendahara/wilayah/dashboard');
            return;
        }

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/transaksi-create.php', 'layouts/bendahara.php', [
            'activeMenu' => 'transaksi',
            'title' => 'Tambah Transaksi Wilayah',
            'kegiatanList' => $kegiatanList
        ]));
    }

    public function transaksiStore(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            $response->redirect('/keuangan/bendahara/wilayah/profil');
            return;
        }

        // Get Profil Detail for dicatat_oleh & periode_kepengurusan
        $db = \App\Core\Database::connection();
        $stmtProf = $db->prepare("SELECT nama_bendahara, tahun_periode_awal, tahun_periode_akhir FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'wilayah'");
        $stmtProf->execute([$userId]);
        $profil = $stmtProf->fetch();
        $dicatatOleh = $profil['nama_bendahara'];
        $periode = $profil['tahun_periode_awal'] . '/' . $profil['tahun_periode_akhir'];

        $jenis_entri = $_POST['jenis_entri'] ?? 'kegiatan';
        $kegiatan_id = $_POST['kegiatan_id'] ?? '';
        $tipe_transaksi = $_POST['tipe_transaksi'] ?? '';
        $nominal = $_POST['nominal'] ?? '';
        $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
        $keterangan_transaksi = trim((string)($_POST['keterangan_transaksi'] ?? ''));
        $alokasi_dana = trim((string)($_POST['alokasi_dana'] ?? ''));
        $sumber_dana = trim((string)($_POST['sumber_dana'] ?? ''));
        $input_mode = $_POST['input_mode'] ?? 'file'; // 'file' or 'link'
        $bukti_link = trim((string)($_POST['bukti_link'] ?? ''));

        $errors = [];
        $error_fields = [];

        if ($jenis_entri === 'invoice') {
            $kegiatan_id = null;
        } else {
            if (empty($kegiatan_id)) {
                $errors[] = 'Kegiatan harus dipilih.';
                $error_fields[] = 'kegiatan_id';
            }
        }
        if (!in_array($tipe_transaksi, ['pemasukan', 'pengeluaran'])) {
            $errors[] = 'Tipe transaksi tidak valid.';
            $error_fields[] = 'tipe_transaksi';
        }
        if (!is_numeric($nominal) || $nominal <= 0) {
            $errors[] = 'Nominal harus berupa angka lebih dari 0.';
            $error_fields[] = 'nominal';
        }
        if (empty($tanggal_transaksi)) {
            $errors[] = 'Tanggal transaksi harus diisi.';
            $error_fields[] = 'tanggal_transaksi';
        }
        if (empty($keterangan_transaksi)) {
            $errors[] = 'Keterangan transaksi harus diisi.';
            $error_fields[] = 'keterangan_transaksi';
        }
        if (empty($alokasi_dana)) {
            $errors[] = 'Alokasi dana harus diisi (pilih dari dropdown atau ketik kustom).';
            $error_fields[] = 'alokasi_dana';
        }

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
                // Input mode file
                if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['bukti_file']['tmp_name'];
                    $fileName = $_FILES['bukti_file']['name'];
                    $fileSize = $_FILES['bukti_file']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $isPdf = $fileExt === 'pdf';
                    $isImg = in_array($fileExt, ['jpg', 'jpeg', 'png']);

                    // MIME Type Validation (SEC-K-02)
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($fileTmp);
                    $validPdfMime = $mimeType === 'application/pdf';
                    $validImgMimes = in_array($mimeType, ['image/jpeg', 'image/png']);

                    if ((!$isPdf && !$isImg) || (!$validPdfMime && !$validImgMimes)) {
                        $errors[] = 'Format file tidak valid atau file telah dimanipulasi (MIME tidak cocok). Hanya PDF, JPG, JPEG, dan PNG yang diizinkan.';
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

                            $newFileName = 'trx_wilayah_' . time() . '_' . uniqid() . '.' . $fileExt;
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
            $response->redirect('/keuangan/bendahara/wilayah/transaksi/create');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO tbl_transaksi_wilayah 
                (user_id, kegiatan_id, jenis_entri, dicatat_oleh, periode_kepengurusan, tipe_transaksi, nominal, tanggal_transaksi, keterangan_transaksi, alokasi_dana, sumber_dana, bukti_transaksi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $kegiatan_id ?: null,
                $jenis_entri,
                $dicatatOleh,
                $periode,
                $tipe_transaksi,
                $nominal,
                $tanggal_transaksi,
                $keterangan_transaksi,
                $alokasi_dana,
                $sumber_dana,
                $bukti_transaksi
            ]);
            \App\Core\Session::flash('swal_success', 'Transaksi berhasil ditambahkan.');
            $response->redirect('/keuangan/bendahara/wilayah/transaksi');
        } catch (\PDOException $e) {
            \App\Core\Session::flash('swal_error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
            $response->redirect('/keuangan/bendahara/wilayah/transaksi/create');
        }
    }

    public function transaksiEdit(Request $request, Response $response, ?string $id): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            \App\Core\Session::flash('swal_error', 'Harap lengkapi profil bendahara Anda.');
            $response->redirect('/keuangan/bendahara/wilayah/profil');
            return;
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_transaksi_wilayah WHERE id = ?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();

        if (!$trx) {
            \App\Core\Session::flash('swal_error', 'Transaksi tidak ditemukan.');
            $response->redirect('/keuangan/bendahara/wilayah/transaksi');
            return;
        }

        $kegiatanList = $this->getKegiatanList();

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/transaksi-edit.php', 'layouts/bendahara.php', [
            'activeMenu' => 'transaksi',
            'title' => 'Edit Transaksi Wilayah',
            'kegiatanList' => $kegiatanList,
            'trx' => $trx
        ]));
    }

    public function transaksiUpdate(Request $request, Response $response, ?string $id): void
    {
        $userId = \App\Core\Session::get('keuangan_user_id');
        if (!$this->checkProfileComplete($userId)) {
            $response->redirect('/keuangan/bendahara/wilayah/profil');
            return;
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM tbl_transaksi_wilayah WHERE id = ?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();
        if (!$trx) {
            $response->redirect('/keuangan/bendahara/wilayah/transaksi');
            return;
        }

        $jenis_entri = $_POST['jenis_entri'] ?? 'kegiatan';
        $kegiatan_id = $_POST['kegiatan_id'] ?? '';
        $tipe_transaksi = $_POST['tipe_transaksi'] ?? '';
        $nominal = $_POST['nominal'] ?? '';
        $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
        $keterangan_transaksi = trim((string)($_POST['keterangan_transaksi'] ?? ''));
        $alokasi_dana = trim((string)($_POST['alokasi_dana'] ?? ''));
        $sumber_dana = trim((string)($_POST['sumber_dana'] ?? ''));
        $input_mode = $_POST['input_mode'] ?? 'file'; // 'file', 'link', or 'keep'
        $bukti_link = trim((string)($_POST['bukti_link'] ?? ''));

        $errors = [];
        $error_fields = [];

        if ($jenis_entri === 'invoice') {
            $kegiatan_id = null;
        } else {
            if (empty($kegiatan_id)) {
                $errors[] = 'Kegiatan harus dipilih.';
                $error_fields[] = 'kegiatan_id';
            }
        }
        if (!in_array($tipe_transaksi, ['pemasukan', 'pengeluaran'])) {
            $errors[] = 'Tipe transaksi tidak valid.';
            $error_fields[] = 'tipe_transaksi';
        }
        if (!is_numeric($nominal) || $nominal <= 0) {
            $errors[] = 'Nominal harus berupa angka lebih dari 0.';
            $error_fields[] = 'nominal';
        }
        if (empty($tanggal_transaksi)) {
            $errors[] = 'Tanggal transaksi harus diisi.';
            $error_fields[] = 'tanggal_transaksi';
        }
        if (empty($keterangan_transaksi)) {
            $errors[] = 'Keterangan transaksi harus diisi.';
            $error_fields[] = 'keterangan_transaksi';
        }
        if (empty($alokasi_dana)) {
            $errors[] = 'Alokasi dana harus diisi (pilih dari dropdown atau ketik kustom).';
            $error_fields[] = 'alokasi_dana';
        }

        $bukti_transaksi = $trx['bukti_transaksi']; // default keep old

        if (empty($errors)) {
            if ($input_mode === 'link') {
                if (empty($bukti_link) || !filter_var($bukti_link, FILTER_VALIDATE_URL)) {
                    $errors[] = 'Link Google Drive tidak valid.';
                    $error_fields[] = 'bukti_link';
                } else {
                    $bukti_transaksi = $bukti_link;
                }
            } elseif ($input_mode === 'file') {
                if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['bukti_file']['tmp_name'];
                    $fileName = $_FILES['bukti_file']['name'];
                    $fileSize = $_FILES['bukti_file']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $isPdf = $fileExt === 'pdf';
                    $isImg = in_array($fileExt, ['jpg', 'jpeg', 'png']);

                    // MIME Type Validation (SEC-K-02)
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($fileTmp);
                    $validPdfMime = $mimeType === 'application/pdf';
                    $validImgMimes = in_array($mimeType, ['image/jpeg', 'image/png']);

                    if ((!$isPdf && !$isImg) || (!$validPdfMime && !$validImgMimes)) {
                        $errors[] = 'Format file tidak valid atau file telah dimanipulasi (MIME tidak cocok). Hanya PDF, JPG, JPEG, dan PNG yang diizinkan.';
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

                            $newFileName = 'trx_wilayah_' . time() . '_' . uniqid() . '.' . $fileExt;
                            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                                $bukti_transaksi = $newFileName;
                                // we can delete old file if it wasn't a link
                                if ($trx['bukti_transaksi'] && !filter_var($trx['bukti_transaksi'], FILTER_VALIDATE_URL)) {
                                    @unlink($uploadDir . $trx['bukti_transaksi']);
                                }
                            } else {
                                $errors[] = 'Gagal mengupload file bukti baru.';
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
            $response->redirect('/keuangan/bendahara/wilayah/transaksi/edit/' . $id);
            return;
        }

        try {
            $stmtUpdate = $db->prepare("UPDATE tbl_transaksi_wilayah 
                SET kegiatan_id = ?, jenis_entri = ?, tipe_transaksi = ?, nominal = ?, tanggal_transaksi = ?, keterangan_transaksi = ?, alokasi_dana = ?, sumber_dana = ?, bukti_transaksi = ? 
                WHERE id = ?");
            $stmtUpdate->execute([
                $kegiatan_id ?: null,
                $jenis_entri,
                $tipe_transaksi,
                $nominal,
                $tanggal_transaksi,
                $keterangan_transaksi,
                $alokasi_dana,
                $sumber_dana,
                $bukti_transaksi,
                $id
            ]);
            \App\Core\Session::flash('swal_success', 'Transaksi berhasil diperbarui.');
            $response->redirect('/keuangan/bendahara/wilayah/transaksi');
        } catch (\PDOException $e) {
            \App\Core\Session::flash('swal_error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
            $response->redirect('/keuangan/bendahara/wilayah/transaksi/edit/' . $id);
        }
    }

    public function transaksiDestroy(Request $request, Response $response, ?string $id): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT bukti_transaksi FROM tbl_transaksi_wilayah WHERE id = ?");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();
        if ($trx) {
            if ($trx['bukti_transaksi'] && !filter_var($trx['bukti_transaksi'], FILTER_VALIDATE_URL)) {
                $file = dirname(__DIR__, 3) . '/public/uploads/keuangan/' . $trx['bukti_transaksi'];
                if (file_exists($file)) @unlink($file);
            }
            $stmtDel = $db->prepare("DELETE FROM tbl_transaksi_wilayah WHERE id = ?");
            $stmtDel->execute([$id]);
            \App\Core\Session::flash('swal_success', 'Transaksi berhasil dihapus.');
        }
        $response->redirect('/keuangan/bendahara/wilayah/transaksi');
    }


    public function profil(Request $request, Response $response): void
    {
        $userId = Session::get('keuangan_user_id');
        $db = Database::connection();

        // Ambil data user untuk email
        $stmtUser = $db->prepare("SELECT email FROM tbl_user WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch();

        // Ambil profil bendahara
        $stmtProfil = $db->prepare("SELECT * FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'wilayah'");
        $stmtProfil->execute([$userId]);
        $profil = $stmtProfil->fetch() ?: [];

        $response->html($this->renderer->renderWithLayout('keuangan/bendahara/wilayah/profil.php', 'layouts/bendahara.php', [
            'activeMenu' => 'profil',
            'title' => 'Profil Bendahara Wilayah',
            'user' => $user,
            'profil' => $profil
        ]));
    }

    public function updateProfil(Request $request, Response $response): void
    {
        $userId = Session::get('keuangan_user_id');

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
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $response->redirect('/keuangan/bendahara/wilayah/profil');
            return;
        }

        $db = Database::connection();

        try {
            $db->beginTransaction();

            // Update email di tbl_user
            $stmtUser = $db->prepare("UPDATE tbl_user SET email = ? WHERE id = ?");
            $stmtUser->execute([$email, $userId]);

            // Cek apakah profil sudah ada
            $stmtCek = $db->prepare("SELECT id FROM tbl_profil_bendahara WHERE user_id = ? AND tempat = 'wilayah'");
            $stmtCek->execute([$userId]);
            $profilAda = $stmtCek->fetch();

            if ($profilAda) {
                // Update
                $stmtUpdate = $db->prepare("
                    UPDATE tbl_profil_bendahara 
                    SET nama_bendahara = ?, tahun_periode_awal = ?, tahun_periode_akhir = ?, jenis_kelamin = ?, universitas = ?, program_studi = ?, semester_studi = ?
                    WHERE user_id = ? AND tempat = 'wilayah'
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
                    VALUES (?, ?, ?, ?, 'wilayah', ?, ?, ?, ?)
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
            Session::flash('swal_success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            $db->rollBack();
            Session::flash('swal_error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }

        $response->redirect('/keuangan/bendahara/wilayah/profil');
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

        Session::flash('swal_success', 'Kegiatan berhasil ditambahkan!');
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
            Session::flash('swal_error', 'Kegiatan tidak ditemukan atau bukan tingkat wilayah.');
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
            Session::flash('swal_error', 'Kegiatan tidak valid.');
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

        Session::flash('swal_success', 'Kegiatan berhasil diperbarui!');
        $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
    }

    public function hapusKegiatan(Request $request, Response $response, array $args): void
    {
        $id = $args['id'] ?? null;

        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM tbl_kegiatan_keuangan WHERE id = ? AND tingkat = 'wilayah'");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            Session::flash('swal_success', 'Kegiatan berhasil dihapus.');
        } else {
            Session::flash('swal_error', 'Gagal menghapus kegiatan.');
        }

        $response->redirect('/keuangan/bendahara/wilayah/kegiatan');
    }

    public function unja(Request $request, Response $response): void
    {
        $this->renderKomsatView($request, $response, 'unja');
    }

    public function uin(Request $request, Response $response): void
    {
        $this->renderKomsatView($request, $response, 'uin');
    }

    private function renderKomsatView(Request $request, Response $response, string $komsat): void
    {
        $db = Database::connection();
        $divisiFilter = $_GET['divisi'] ?? 'Semua Divisi';

        $tableName = $komsat === 'unja' ? 'tbl_transaksi_unja' : 'tbl_transaksi_uin';
        $komsatName = $komsat === 'unja' ? 'Komsat UNJA' : 'Komsat UIN';
        $bpiName = $komsat === 'unja' ? 'BPI Komsat UNJA' : 'BPI Komsat UIN';

        $divisions = [
            'Semua Divisi',
            'Kewirausahaan',
            'Lingkungan Hidup',
            'Pendidikan dan Kebudayaan',
            'Pengabdian Masyarakat',
            'Pengembangan Sumber Daya Manusia',
            'Publikasi dan Sosial',
            $bpiName
        ];

        // Base Query
        $sql = "
            SELECT t.*, k.nama_kegiatan, k.divisi
            FROM {$tableName} t
            LEFT JOIN tbl_kegiatan_keuangan k ON t.kegiatan_id = k.id
            WHERE 1=1
        ";
        $params = [];

        if ($divisiFilter !== 'Semua Divisi') {
            $sql .= " AND t.alokasi_dana = ?";
            $params[] = $divisiFilter;
        }

        $sql .= " ORDER BY t.tanggal_transaksi DESC, t.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $transaksiList = $stmt->fetchAll();

        // Calculate Totals
        $totalPemasukan = 0;
        $totalPengeluaran = 0;
        foreach ($transaksiList as $t) {
            if ($t['tipe_transaksi'] === 'pemasukan') {
                $totalPemasukan += (float) $t['nominal'];
            } else {
                $totalPengeluaran += (float) $t['nominal'];
            }
        }
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Group data for Chart (Monthly totals)
        $chartData = [];
        foreach ($transaksiList as $t) {
            $month = date('M Y', strtotime($t['tanggal_transaksi']));
            if (!isset($chartData[$month])) {
                $chartData[$month] = ['pemasukan' => 0, 'pengeluaran' => 0];
            }
            if ($t['tipe_transaksi'] === 'pemasukan') {
                $chartData[$month]['pemasukan'] += (float) $t['nominal'];
            } else {
                $chartData[$month]['pengeluaran'] += (float) $t['nominal'];
            }
        }

        // Sort chart data chronologically
        uksort($chartData, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $chartLabels = array_keys($chartData);
        $chartPemasukan = array_column($chartData, 'pemasukan');
        $chartPengeluaran = array_column($chartData, 'pengeluaran');

        $response->html($this->renderer->renderWithLayout("keuangan/bendahara/wilayah/komsat-{$komsat}.php", 'layouts/bendahara.php', [
            'activeMenu' => "komsat_{$komsat}",
            'title' => "Data Transaksi {$komsatName}",
            'komsatName' => $komsatName,
            'divisions' => $divisions,
            'selectedDivisi' => $divisiFilter,
            'transaksiList' => $transaksiList,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $saldo,
            'chartLabels' => json_encode($chartLabels),
            'chartPemasukan' => json_encode($chartPemasukan),
            'chartPengeluaran' => json_encode($chartPengeluaran),
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
