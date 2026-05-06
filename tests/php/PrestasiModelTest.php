<?php

declare(strict_types=1);

use App\Models\Prestasi;
use App\Models\PrestasiToken;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

// --- Prestasi::mapRow with full backend fields ---
$row = Prestasi::mapRow([
    'prestasi_id' => 5,
    'slug' => 'juara-kti-nasional-5',
    'judul_prestasi' => 'Juara KTI Nasional',
    'nama_anggota' => 'Amalia',
    'komisariat' => 'Universitas Jambi',
    'kategori' => 'Karya Tulis Ilmiah',
    'tahun' => '2026',
    'deskripsi_singkat' => 'Ringkasan prestasi',
    'deskripsi_lengkap' => 'Deskripsi lengkap prestasi.',
    'foto' => '/uploads/prestasi/kti.jpg',
    'institusi_penyelenggara' => 'Kemendikbud',
    'status' => 'published',
    'created_at' => '2026-05-06 10:00:00',
    'updated_at' => '2026-05-06 12:00:00',
]);

assert($row['id'] === 5);
assert($row['prestasi_id'] === 5);
assert($row['slug'] === 'juara-kti-nasional-5');
assert($row['title'] === 'Juara KTI Nasional');
assert($row['name'] === 'Amalia');
assert($row['campus'] === 'Universitas Jambi');
assert($row['category'] === 'Karya Tulis Ilmiah');
assert($row['year'] === '2026');
assert($row['description'] === 'Ringkasan prestasi');
assert($row['content'] === 'Deskripsi lengkap prestasi.');
assert($row['image'] === '/uploads/prestasi/kti.jpg');
assert($row['institution'] === 'Kemendikbud');
assert($row['status'] === 'published');
assert($row['created_at'] === '2026-05-06 10:00:00');
assert($row['updated_at'] === '2026-05-06 12:00:00');

// --- Prestasi::mapRow with alternative/minimal field names ---
$row2 = Prestasi::mapRow([
    'id' => 10,
    'title' => 'Juara Debat',
    'member_name' => 'Budi',
    'category' => 'Debat',
    'year' => '2025',
    'detail' => 'Detail lomba debat',
    'photo' => '/uploads/prestasi/debat.jpg',
]);

assert($row2['id'] === 10);
assert($row2['title'] === 'Juara Debat');
assert($row2['name'] === 'Budi');
assert($row2['member_name'] === 'Budi');
assert($row2['category'] === 'Debat');
assert($row2['year'] === '2025');
assert($row2['content'] === 'Detail lomba debat');
assert($row2['detail'] === 'Detail lomba debat');
assert($row2['image'] === '/uploads/prestasi/debat.jpg');
assert($row2['photo'] === '/uploads/prestasi/debat.jpg');
assert($row2['status'] === 'published'); // default

// --- Prestasi::mapRow resolves Google Drive image links ---
$row3 = Prestasi::mapRow([
    'prestasi_id' => 11,
    'title' => 'Juara QRIS',
    'photo' => 'https://drive.google.com/open?id=1XNlgeReWNp-w9uqkT4pAfcsZWyx-fnWn',
]);

assert($row3['image'] === 'https://drive.google.com/thumbnail?id=1XNlgeReWNp-w9uqkT4pAfcsZWyx-fnWn&sz=w1000');
assert($row3['photo'] === 'https://drive.google.com/thumbnail?id=1XNlgeReWNp-w9uqkT4pAfcsZWyx-fnWn&sz=w1000');

// --- PrestasiToken::mapRow ---
$token = PrestasiToken::mapRow([
    'token_id' => 3,
    'token_hash' => 'abc123hash',
    'label' => 'Token untuk KTI 2026',
    'status' => 'active',
    'created_by' => 1,
    'used_at' => null,
    'expires_at' => '2026-06-01 00:00:00',
    'created_at' => '2026-05-06 08:00:00',
]);

assert($token['id'] === 3);
assert($token['token_id'] === 3);
assert($token['token_hash'] === 'abc123hash');
assert($token['label'] === 'Token untuk KTI 2026');
assert($token['status'] === 'active');
assert($token['created_by'] === 1);
assert($token['used_at'] === null);
assert($token['expires_at'] === '2026-06-01 00:00:00');
assert($token['created_at'] === '2026-05-06 08:00:00');

// --- PrestasiToken::mapRow with keterangan field ---
$token2 = PrestasiToken::mapRow([
    'id' => 7,
    'keterangan' => 'Token debat',
    'status' => 'used',
    'created_by' => 2,
]);

assert($token2['id'] === 7);
assert($token2['label'] === 'Token debat');
assert($token2['status'] === 'used');
assert($token2['created_by'] === 2);

echo "PHP prestasi model tests passed\n";
