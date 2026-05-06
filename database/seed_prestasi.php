<?php

declare(strict_types=1);

[$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';

$db = \App\Core\Database::connection();

$items = [
    [
        'tanggal' => '25/09/2025 21:50:42',
        'nama' => 'Mahmudi',
        'prodi' => 'Hukum Keluarga Islam',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'QRIS JELAJAH BUDAYA INDONESIA 2025',
        'prestasi' => 'Juara 3 Lomba QRIS JELAJAH BUDAYA INDONESIA Provinsi Jambi',
        'link' => ['https://drive.google.com/open?id=1XNlgeReWNp-w9uqkT4pAfcsZWyx-fnWn'],
        'sosmed' => '@bank_indonesia_jambi',
    ],
    [
        'tanggal' => '25/09/2025 22:21:03',
        'nama' => 'Irfan Aziz',
        'prodi' => 'Sistem Informasi',
        'kampus' => 'Universitas Jambi',
        'acara' => 'National Agriculture Competition Week 2025',
        'prestasi' => "Juara 1 Karya Tulis Ilmiah Sub Tema Inovasi Digitalisasi Pertanian dalam Mendorong Smart Farming di Tingkat Daerah\nJuara 2 Essay Ilmiah Sub Tema Inovasi Rehabilitasi Lingkungan Lahan Pertanian dan Kawasan Hutan\nJuara 3 Karya Tulis Ilmiah Sub Tema Transformasi Inovasi Produk Pertanian Lokal yang Menyokong Pertanian Berkelanjutan\nBest Poster Karya Tulis Ilmiah",
        'link' => [
            'https://drive.google.com/open?id=1P55Z5wU62vafISgGahu10PDQ_z0Im_ba',
            'https://drive.google.com/open?id=1kaUb60e9kvX-4a2lAkcubL3c0K663vk1',
            'https://drive.google.com/open?id=1HiiDzTeCOez0ruyOa7BiSHmPdHcuXkRh',
            'https://drive.google.com/open?id=1ozFs8_RzOdKVUfeCYCYBT3oVIdrFFIof',
            'https://drive.google.com/open?id=1_9oan2jxAQQjWhIkGl5BIiXZyBT2W4nv',
        ],
        'sosmed' => 'https://www.instagram.com/nacweekfaperta_official?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==',
    ],
    [
        'tanggal' => '25/09/2025 23:39:57',
        'nama' => 'Rizky Desryan Syah',
        'prodi' => 'Biologi',
        'kampus' => 'Universitas Jambi',
        'acara' => 'National Essay Competition Jecofest',
        'prestasi' => 'Gold Medal & Bronze Medal Essay',
        'link' => ['https://drive.google.com/open?id=1Y0eCJtOT5Ge2nYBtYBJHD6SX5wWeEYIG'],
        'sosmed' => 'https://www.instagram.com/jecofest1.0?igsh=MTdwbHZ3ZWliM2JtZg==',
    ],
    [
        'tanggal' => '26/09/2025 2:14:14',
        'nama' => 'Rizky Desryan Syah',
        'prodi' => 'Biologi',
        'kampus' => 'Universitas Jambi',
        'acara' => 'Program Inovasi Desa',
        'prestasi' => 'Penerima Pendanaan',
        'link' => ['https://drive.google.com/open?id=12vTf-WgKBhHfHbFPNSk5H2q3av9cCVKm'],
        'sosmed' => 'https://www.instagram.com/proide_desasembubuk?igsh=enlqYW9sMm94aXBu',
    ],
    [
        'tanggal' => '26/09/2025 13:25:18',
        'nama' => 'Amalia Jelita',
        'prodi' => 'Pemanfaatan sumberdaya perikanan',
        'kampus' => 'Universitas Jambi',
        'acara' => 'Pro Ide Ok Hadroh Nurul Musthafa',
        'prestasi' => '-',
        'link' => ['https://drive.google.com/open?id=1gCS1-c0mXpu8Q5xrncPCg6cbPgGKZ95D'],
        'sosmed' => 'https://www.instagram.com/proide_okhadrohnurulmustofa?igsh=dmtveWFzZTJnamFu',
    ],
    [
        'tanggal' => '26/09/2025 21:34:17',
        'nama' => 'Catur Ragil Saputra',
        'prodi' => 'Manajemen Keuangan Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'MUNAS Forum Nasional Manajemen Keuangan Syariah di UIN Sunan Gunung Djati Bandung',
        'prestasi' => 'Juara 3 LKTI',
        'link' => ['https://drive.google.com/open?id=192npIyBXXObyXIUa2h2j-qulFiignRwv'],
        'sosmed' => 'https://www.instagram.com/p/DOSolJzAQoV/?igsh=ZG0xbjhuaHdrcDZ2',
    ],
    [
        'tanggal' => '29/09/2025 16:15:13',
        'nama' => 'Ilham Wisnu Herlambang',
        'prodi' => 'Akuntansi Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Shariah Economic Expo 2025',
        'prestasi' => 'Olimpiade Ekonomi Islam',
        'link' => [
            'https://drive.google.com/open?id=1oYt79RvL8JPgUffySpaMnrCiAKXkFug7',
            'https://drive.google.com/open?id=1EFij3swFy42XmTn5qGKEiZW2vu4jtTVb',
        ],
        'sosmed' => 'https://www.instagram.com/see.lkei2025?igsh=cnVlZjAxZGo3cW82',
    ],
    [
        'tanggal' => '30/09/2025 14:55:43',
        'nama' => 'Andi Fakhira Khairani',
        'prodi' => 'Akuntansi Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Sharia Economic Expo 2025',
        'prestasi' => 'Olimpiade Ekonomi Islam',
        'link' => ['https://drive.google.com/open?id=1gWMzB6JYTA0E6cd83P1fPfaZ8BMxCKhV'],
        'sosmed' => 'https://www.instagram.com/see.lkei2025?igsh=MXdwbDQza3lodWdvZA==',
    ],
    [
        'tanggal' => '03/10/2025 19:56:54',
        'nama' => 'Andrian Jandra Kurniawan',
        'prodi' => 'Sistem Informasi',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'MTQ Tingkat Kabupaten Tebo',
        'prestasi' => 'Juara II Kalaigrafi Kontemporer',
        'link' => [
            'https://drive.google.com/open?id=18nn-qACnq31z0j2I0qFmzv8epr8KuKQ_',
            'https://drive.google.com/open?id=18ouMRyodOmaxMLsDvZi4YVWSG0lscYiR',
        ],
        'sosmed' => 'Ga ada mba',
    ],
    [
        'tanggal' => '06/10/2025 7:28:13',
        'nama' => 'DEPI SUSANTI',
        'prodi' => 'MANAJEMEN KEUANGAN SYARIAH',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Musyawarah Nasional Forum Nasional Manajemen Keuangan Syariah',
        'prestasi' => 'Juara 3 Lomba Karya Tulis Ilmiah',
        'link' => ['https://drive.google.com/open?id=1djvym_jy7atvWFwU2c-Ndhty3IZCoVMT'],
        'sosmed' => 'https://www.instagram.com/mksuinsgdbandung?igsh=ejVjcndqN2hiemFs',
    ],
    [
        'tanggal' => '29/10/2025 12:03:55',
        'nama' => 'Sakinah Rahman',
        'prodi' => 'Ekonomi Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Ignite Future Fest 2025',
        'prestasi' => 'Bronze Medal | Essay Nasional Competition',
        'link' => ['https://drive.google.com/open?id=1CGPbLMIWQY2LR0NKJF5xBAg2zlcufB-h'],
        'sosmed' => 'https://www.instagram.com/futurainnovationhub.id?igsh=MWVqeWQ1YW0yYm96Nw==',
    ],
    [
        'tanggal' => '29/10/2025 12:36:56',
        'nama' => 'Vanessa Nurul Annisa',
        'prodi' => "Ekonomi Syari'ah",
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Ignite Future Fest 2025 - Futura Innovation Hub x Himasepta UNRAM',
        'prestasi' => "1. Silver medal-Business Plan Kategori Pertanian dan Pangan\n2. Bronze medal-Essay Kategori Sosial dan Ekonomi\n3. Bronze medal-Essay Kategori Energi dan Lingkungan",
        'link' => ['https://drive.google.com/open?id=1VsEZUieRU5OLEqB_l5IpXfCK0il1q_wP'],
        'sosmed' => 'https://www.instagram.com/futurainnovationhub.id?igsh=MWZudmJvMTl3bjFhNg==',
    ],
    [
        'tanggal' => '02/11/2025 15:57:28',
        'nama' => 'MUHAMAD DIMAS SAPUTRA, IRFAN AZIZ, FARHAN SYIFA PRAYOGA',
        'prodi' => 'Ilmu pemerintahan, Sistem Informasi, Matematika',
        'kampus' => 'Universitas Jambi',
        'acara' => 'NATIONAL WRITING COMPETITION UNIVERSITAS ANDALAS 2025',
        'prestasi' => 'GOLD MEDAL, SILVER MEDAL, BRONZE MEDAL, JUARA UMUM UNIVERSITAS JAMBI',
        'link' => [
            'https://drive.google.com/open?id=1BE_mlXRKArB-xtDTJbp6fszYZLwJnpnN',
            'https://drive.google.com/open?id=1Dey9bdQcgpe9V9o9HqK9U936pBYOumly',
            'https://drive.google.com/open?id=14cDIIjoMAWGnYdnCcW0t4aUF3m4nQZIK',
        ],
        'sosmed' => 'https://www.instagram.com/inteleksa?igsh=Y3cwZGxiN25sMG1x',
    ],
    [
        'tanggal' => '04/11/2025 22:17:22',
        'nama' => 'Juliana Paelori',
        'prodi' => 'Ilmu Pemerintahan',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'MTQ Tingkat Kecamatan Nipah Panjang',
        'prestasi' => 'Juara 2 Lomba Barzanji Putri',
        'link' => ['https://drive.google.com/open?id=1eIh5QRvGX6foGZuuuBE_tIyXtmZR6mxy'],
        'sosmed' => 'https://desapemusiran.id/berita/142/desa-pemusiran-kembali-raih-juara-umum-pada-mtq-ke-48-kecamatan-nipah-panjang',
    ],
    [
        'tanggal' => '05/11/2025 0:26:18',
        'nama' => 'Etika Wulandari',
        'prodi' => 'Ilmu pemerintahan',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Peransaka nasional tahun 2025 di Gorontalo',
        'prestasi' => 'Di Gorontalo',
        'link' => ['https://drive.google.com/open?id=1unVyk3qz-ZYkXBvQAnf_mTT8c7WZdTVn'],
        'sosmed' => '@Dk.nasional, @kwarda Jambi. @Pramuka uin',
    ],
    [
        'tanggal' => '05/11/2025 6:37:46',
        'nama' => 'Laura Tisya Yordani',
        'prodi' => 'Tadris Matematika',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Forum Ilmiah Matematika Nasional (FIMNAS) 2025',
        'prestasi' => 'Juara 3 Mathematics Paper Competition',
        'link' => ['https://drive.google.com/open?id=1AfT-a_ag-pgPNNuw99UXEETu7tYFA3pW'],
        'sosmed' => '@fimunnes',
    ],
    [
        'tanggal' => '05/11/2025 18:34:01',
        'nama' => 'Abdul Matin Aji Saka',
        'prodi' => 'Akuntansi Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Lomba Video Konten Literasi - Dinas Perpustakaan dan Arsip Daerah Provinsi Jambi Tahun 2025',
        'prestasi' => 'Juara III',
        'link' => ['https://drive.google.com/open?id=1W3nDC832BIom4jjcXA6iJ-YnHdTfHdDx'],
        'sosmed' => 'https://www.instagram.com/p/DKM0yXoyBGR/?igsh=cnJ4Nng2dzRoa3Bt',
    ],
    [
        'tanggal' => '17/11/2025 10:12:17',
        'nama' => 'Vanessa Nurul Annisa',
        'prodi' => 'Ekonomi Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'International Conference On Islamic Economic and Business 2025 - Student Mobility',
        'prestasi' => "#2 Most Creative - Business Plan\n#3 Most Visionary - Business Plan",
        'link' => ['https://drive.google.com/open?id=1bUrGN3_Q4GoLXLAqd2eq5RHTtKOvhIqQ'],
        'sosmed' => 'https://www.instagram.com/cetarfebi?igsh=djJjY3NmMWlyOGh0',
    ],
    [
        'tanggal' => '20/11/2025 0:39:20',
        'nama' => 'Thariani Saffanah',
        'prodi' => 'Hukum Pidana Islam',
        'kampus' => 'UIN STS Jambi',
        'acara' => "Kompetisi Internasional Antar Negara Melayu Serumpun\nfakultas Syari'ah Universitas Islam Negeri Sulthan Thaha Saifuddin Jambi Tahun 2025",
        'prestasi' => 'Juara 3 Lomba Karya Tulis Ilmiah',
        'link' => ['https://drive.google.com/open?id=1HYdeZjB7d-Kwn-nq4-z6wgyw7YomxrOM'],
        'sosmed' => "Kompetisi Internasional Antar Negara Melayu Serumpun\nfakultas Syari'ah Universitas Islam Negeri Sulthan Thaha Saifuddin Jambi Tahun 2025",
    ],
    [
        'tanggal' => '20/11/2025 9:05:25',
        'nama' => 'Oxtavia Rizel Larasati Xenzho',
        'prodi' => 'Hukum Pidana Islam',
        'kampus' => 'UIN STS Jambi',
        'acara' => "Kompetisi Internasional Antar Negara Melayu Serumpun\nfakultas Syari'ah Universitas Islam Negeri Sulthan Thaha Saifuddin Jambi Tahun 2025",
        'prestasi' => 'Juara 1 lomba debat',
        'link' => ['https://drive.google.com/open?id=19LKGdtVgasx8HCXKNmUtcC9F9ckomuOS'],
        'sosmed' => '-',
    ],
    [
        'tanggal' => '21/11/2025 21:22:22',
        'nama' => 'Catur Ragil Saputra',
        'prodi' => 'Manajemen Keuangan Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Competition Legacy National Scientific Paper (LKTIN) yang di selenggarakan UIN Sayyid Ali Rahmatullah Tulungagung, Jawa Timur.',
        'prestasi' => 'Bronze medal (Juara 3)',
        'link' => ['https://drive.google.com/open?id=1lWsoQfO969CPE4_ffSi7jKobwcNfQmQ6'],
        'sosmed' => 'https://www.instagram.com/p/DP_K8bYEmVF/?igsh=MWpoODdrZWo2Z2szZQ==',
    ],
    [
        'tanggal' => '22/11/2025 20:13:10',
        'nama' => 'Depi Susanti',
        'prodi' => 'Manajemen Keuangan Syariah',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'MKS LEGACY 2025 YANG DIADAKAN OLEH HMPS MKS UIN SAYYID ALI RAHMATULLAH TULUNGAGUNG',
        'prestasi' => 'Juara 3 Lomba Karya Tulis Ilmiah Nasional',
        'link' => ['https://drive.google.com/open?id=17dKHDe-I0jVQZWJPtvt_vd8q3Jz-QLjn'],
        'sosmed' => 'https://www.instagram.com/p/DP_K8bYEmVF/?igsh=dWZkemZjamVudTU4',
    ],
    [
        'tanggal' => '13/12/2025 18:33:22',
        'nama' => 'Fitriani Lianti',
        'prodi' => 'Pendidikan Matematika',
        'kampus' => 'Universitas Jambi',
        'acara' => 'Literafest x Teater Tuju',
        'prestasi' => 'Terbaik 2 Resensi Mahasiswa Tingkat Nasional',
        'link' => ['https://drive.google.com/open?id=1cGCmXymYfBf1qg5p5MqaBypifOJpGaUs'],
        'sosmed' => 'https://www.instagram.com/literafest_2025?igsh=MTliMWhubzJ1emlleQ==',
    ],
    [
        'tanggal' => '31/12/2025 6:45:43',
        'nama' => 'Imelda Agustin',
        'prodi' => 'Sistem Informasi',
        'kampus' => 'Universitas Jambi',
        'acara' => "Musabaqah Tilawatil Qur'an ke-56 Tingkat Kecamatan Muara Tembesi",
        'prestasi' => "Juara 2 Fahmil Qur'an Putri",
        'link' => ['https://drive.google.com/open?id=1F6XVBgRVVjV3HKfJvL1s0cyA-HqFemZT'],
        'sosmed' => 'Tutup MTQ Kecamatan Muara Tembesi, Camat Ajak Hidup Berperilaku Al Quran - Kabar Jambi Kito https://share.google/ajYl14JUUopfnw4y5',
    ],
    [
        'tanggal' => '30/01/2026 20:19:37',
        'nama' => 'Andrian Jandra Kurniawan',
        'prodi' => 'Sistem Informasi',
        'kampus' => 'UIN STS Jambi',
        'acara' => 'Musabaqah Tilawatil Qur’an',
        'prestasi' => 'Kaligrafi Kontemporer',
        'link' => ['https://drive.google.com/open?id=1SXPv3JOU0SxFuUQUHzvaAEVhf_LVWhKw'],
        'sosmed' => 'tidak ada',
    ],
];

$stmt = $db->prepare(
    'INSERT INTO tbl_prestasi (title, slug, category, year, member_name, institution, description, detail, photo, status, meta_title, meta_keyword, meta_description, created_at, updated_at)
     VALUES (:title, :slug, :category, :year, :member_name, :institution, :description, :detail, :photo, :status, :meta_title, :meta_keyword, :meta_description, :created_at, NOW())
     ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        category = VALUES(category),
        year = VALUES(year),
        member_name = VALUES(member_name),
        institution = VALUES(institution),
        description = VALUES(description),
        detail = VALUES(detail),
        photo = VALUES(photo),
        status = VALUES(status),
        meta_title = VALUES(meta_title),
        meta_keyword = VALUES(meta_keyword),
        meta_description = VALUES(meta_description),
        updated_at = NOW(),
        deleted_at = NULL'
);

$inserted = 0;
foreach ($items as $item) {
    $date = parseImportedDate($item['tanggal']);
    $achievement = normalizeWhitespace($item['prestasi']);
    $event = normalizeWhitespace($item['acara']);
    $name = normalizeWhitespace($item['nama']);
    $slug = mb_substr(slugify($name . ' ' . ($achievement === '-' ? $event : $achievement)), 0, 240);
    $description = implode(' | ', array_filter([
        'Prestasi: ' . $achievement,
        'Program studi: ' . normalizeWhitespace($item['prodi']),
        'Kampus: ' . normalizeWhitespace($item['kampus']),
        'Acara: ' . $event,
    ]));
    $links = $item['link'];
    $detailLines = [
        'Prestasi: ' . $achievement,
        'Nama: ' . $name,
        'Program studi: ' . normalizeWhitespace($item['prodi']),
        'Kampus: ' . normalizeWhitespace($item['kampus']),
        'Acara: ' . $event,
        'Dokumentasi: ' . implode(', ', $links),
        'Sosial media/sumber: ' . normalizeWhitespace($item['sosmed']),
    ];

    $stmt->execute([
        ':title' => truncate($achievement === '-' ? $event : $achievement, 255),
        ':slug' => $slug,
        ':category' => truncate(firstAchievementLine($achievement === '-' ? $event : $achievement), 100),
        ':year' => (int) $date->format('Y'),
        ':member_name' => truncate($name, 120),
        ':institution' => truncate($event, 120),
        ':description' => truncate($description, 5000),
        ':detail' => implode("\n", $detailLines),
        ':photo' => truncate($links[0] ?? '', 1000),
        ':status' => 'published',
        ':meta_title' => truncate(($achievement === '-' ? $event : $achievement) . ' | GenBI Jambi', 255),
        ':meta_keyword' => truncate(implode(', ', array_filter([$name, $event, normalizeWhitespace($item['kampus']), 'Prestasi GenBI Jambi'])), 1000),
        ':meta_description' => truncate($description, 1000),
        ':created_at' => $date->format('Y-m-d H:i:s'),
    ]);
    $inserted++;
}

echo "Seeded {$inserted} prestasi rows.\n";

function parseImportedDate(string $value): DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $value);
    if ($date instanceof DateTimeImmutable) {
        return $date;
    }

    return new DateTimeImmutable();
}

function normalizeWhitespace(string $value): string
{
    return trim((string) preg_replace('/\s+/', ' ', $value));
}

function firstAchievementLine(string $value): string
{
    $lines = preg_split('/\R+/', trim($value)) ?: [];
    return normalizeWhitespace($lines[0] ?? $value);
}

function truncate(string $value, int $limit): string
{
    return mb_substr(normalizeWhitespace($value), 0, $limit);
}

function slugify(string $value): string
{
    $value = mb_strtolower($value);
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/[\s-]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'prestasi';
}
