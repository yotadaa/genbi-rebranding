(function () {
  'use strict';
const site = {
  name: 'GenBI Provinsi Jambi',
  tagline: 'Bersama GenBI, Energi untuk Negeri',
  email: 'genbijambibi@gmail.com',
  phone: '089627896750',
  phoneAlt: '082280226716',
  address: 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
  baseUrl: 'https://genbijambi.com',
  logo: 'https://genbijambi.com/public/uploads/logo.png',
  heroSlides: [
    {
      image: 'https://genbijambi.com/public/uploads/slider-1.png',
      eyebrow: 'GenBI Provinsi Jambi',
      title: 'Sedikit kartu, lebih banyak alur baca.',
      caption: 'Program tampil sebagai daftar editorial. Pengunjung bisa menangkap nama program, fokus, dan manfaat tanpa visual yang terlalu ramai.'
    },
    {
      image: 'https://genbijambi.com/public/uploads/slider-4.png',
      eyebrow: 'Energi untuk Negeri',
      title: 'Sedikit kartu, lebih banyak alur baca.',
      caption: 'Program tampil sebagai daftar editorial. Pengunjung bisa menangkap nama program, fokus, dan manfaat tanpa visual yang terlalu ramai.'
    }
  ],
  videoResourceUrl: 'https://www.youtube.com/embed/ashD1p7d29s?si=FFGjlxX7oNn_OWVq'
};

const navItems = [
  { label: 'Home', href: '/', key: 'home' },
  { label: 'About', href: '/about', key: 'about' },
  { label: 'Team', href: '/team', key: 'team' },
  { label: 'Prestasi', href: '/prestasi', key: 'prestasi' },
  { label: 'News', href: '/news', key: 'news' },
  { label: 'Contact', href: '/contact', key: 'contact' }
];


const stats = [
  { value: '2011', label: 'GenBI diresmikan secara nasional' },
  { value: '2', label: 'Kampus utama di Jambi' },
  { value: '6+', label: 'Program komunitas aktif' },
  { value: '100%', label: 'Bergerak lewat kolaborasi' }
];

const aboutBlocks = [
  {
    title: 'Tentang GenBI',
    text: 'Generasi Baru Indonesia adalah komunitas mahasiswa penerima beasiswa Bank Indonesia. GenBI Jambi menjadi ruang pembinaan, edukasi publik, literasi kebanksentralan, dan pengembangan kepemimpinan bagi mahasiswa Universitas Jambi dan UIN STS Jambi.'
  },
  {
    title: 'Visi',
    text: 'Menjadikan kaum muda Indonesia sebagai generasi yang kompeten dalam berbagai bidang keilmuan, mampu membawa perubahan positif, dan menjadi inspirasi bagi bangsa dan negara.'
  },
  {
    title: 'Misi',
    text: 'Menggagas kegiatan pemberdayaan, melakukan aksi nyata, peduli terhadap masyarakat, serta berbagi inspirasi dan motivasi sebagai energi bagi negeri.'
  }
];

const programs = [
  {
    title: 'KKG',
    name: 'Kegiatan Kenal GenBI',
    description: 'Membangun jaringan awal, memperkenalkan budaya organisasi, dan memperkuat dasar komunikasi anggota baru.',
    focus: 'Internal komunitas'
  },
  {
    title: 'SIGINJAI',
    name: 'Semarak Ekonomi dan Keuangan Syariah Negeri Jambi',
    description: 'Ruang edukasi ekonomi syariah, halal value chain, dan kolaborasi publik bersama mitra strategis.',
    focus: 'Literasi syariah'
  },
  {
    title: 'GENTALA ARASY',
    name: 'Gebyar Ekonomi Digital dan Literasi Jambi',
    description: 'Edukasi ekonomi digital, QRIS, keamanan transaksi, dan kesiapan pemuda menghadapi ruang digital.',
    focus: 'Literasi digital'
  },
  {
    title: 'GGTC',
    name: 'GenBI Goes To Campus',
    description: 'Sosialisasi beasiswa Bank Indonesia, kebanksentralan, Cinta Bangga Paham Rupiah, dan iBI Library.',
    focus: 'Kampus'
  },
  {
    title: 'Leadership Camp',
    name: 'Pembinaan Kepemimpinan',
    description: 'Membentuk karakter, kerja tim, tanggung jawab sosial, dan kesiapan menjadi future leaders.',
    focus: 'Kepemimpinan'
  }
];



const publicEvents = [
  { id: 1, title: 'GenBI PEKA', date: '23 Januari 2025', type: 'Sosial', icon: 'heart', description: 'Aksi kepedulian dan edukasi komunitas untuk memperkuat kepekaan sosial anggota.' },
  { id: 2, title: 'GenBI Ceria', date: '21 Desember 2024', type: 'Komunitas', icon: 'users', description: 'Agenda kebersamaan, edukasi, dan relawan yang membuka ruang interaksi lintas anggota.' },
  { id: 3, title: 'GenBI for UMKM', date: '20 Desember 2024', type: 'Literasi UMKM', icon: 'chart', description: 'Pendampingan literasi digital dan pencatatan keuangan sederhana bagi pelaku usaha.' },
  { id: 4, title: 'PTBI 2024', date: '29 November 2024', type: 'Kebanksentralan', icon: 'calendar', description: 'Partisipasi anggota pada agenda Bank Indonesia untuk memperluas pemahaman ekonomi.' }
];

const teamMembers = [
  { id: 1, name: 'Abdul Haris', role: 'Kadiv Lingkungan Hidup', division: 'Lingkungan Hidup', campus: 'Universitas Jambi', commission: 'Komisariat Universitas Jambi', year: '2026', status: 'Alumni', bio: 'Aktif dalam agenda lingkungan hidup, kampanye kebersihan, dan edukasi perilaku berkelanjutan.' },
  { id: 2, name: 'Abdul Matin Aji Saka', role: 'Anggota Creative', division: 'Creative', campus: 'UIN STS Jambi', commission: 'Badan Pengurus Inti', year: '2026', status: 'Anggota', bio: 'Mendukung produksi konten kreatif dan dokumentasi kegiatan GenBI Jambi.' },
  { id: 3, name: 'Adelia Fitri', role: 'Koordinator', division: 'Lingkungan Hidup', campus: 'Universitas Jambi', commission: 'Komisariat Universitas Jambi', year: '2026', status: 'Pengurus', bio: 'Mengelola koordinasi program lingkungan hidup dan aktivitas relawan anggota.' },
  { id: 4, name: 'Muhammad David', role: 'Penanggung Jawab Website GenBI Jambi', division: 'Multimedia', campus: 'Universitas Jambi', commission: 'Badan Pengurus Inti', year: '2026', status: 'Pengurus', bio: 'Mengelola publikasi digital, berita, dan kebutuhan informasi pada kanal web GenBI Jambi.' },
  { id: 5, name: 'M. Irfan Azhar', role: 'Tim Multimedia', division: 'Multimedia', campus: 'UIN STS Jambi', commission: 'Badan Pengurus Inti', year: '2026', status: 'Pengurus', bio: 'Mendukung produksi visual, publikasi, dan arsip dokumentasi kegiatan.' },
  { id: 6, name: 'Rintan Niar Legi', role: 'Tim Redaksi', division: 'Redaksi', campus: 'UIN STS Jambi', commission: 'Badan Pengurus Inti', year: '2026', status: 'Pengurus', bio: 'Menyusun narasi kegiatan dan publikasi berita organisasi.' },
  { id: 7, name: 'Thariani Saffanah', role: 'Tim Redaksi', division: 'Redaksi', campus: 'Universitas Jambi', commission: 'Badan Pengurus Inti', year: '2026', status: 'Pengurus', bio: 'Berperan dalam penyuntingan konten dan komunikasi publik.' },
  { id: 8, name: 'Ilham Jaya Kusuma', role: 'Ketua Umum', division: 'Badan Pengurus Inti', campus: 'UIN STS Jambi', commission: 'Badan Pengurus Inti', year: '2025', status: 'Pengurus', bio: 'Mengawal arah kerja organisasi dan koordinasi lintas divisi.' },
  { id: 9, name: 'Ananda Marisa Pertiwi', role: 'Pengurus Divisi', division: 'Pendidikan', campus: 'Universitas Jambi', commission: 'Komisariat Universitas Jambi', year: '2025', status: 'Pengurus', bio: 'Mendukung kegiatan literasi dan edukasi publik untuk pelajar serta mahasiswa.' },
  { id: 10, name: 'Depi Susanti', role: 'Anggota', division: 'Sosial Masyarakat', campus: 'UIN STS Jambi', commission: 'Komisariat UIN STS Jambi', year: '2025', status: 'Anggota', bio: 'Terlibat dalam kegiatan sosial, pengabdian, dan pemberdayaan komunitas.' }
];



const bpiMembers = [
  { id: 1, name: 'Ilham Jaya Kusuma', role: 'Ketua Umum GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-37.jpg' },
  { id: 2, name: 'Ananda Marisa Pertiwi', role: 'Sekretaris Umum GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-38.jpg' },
  { id: 3, name: 'Depi Susanti', role: 'Bendahara Umum GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-39.jpg' },
  { id: 4, name: 'Raihan Aulia Aridestama', role: 'Koordinator Tim Media GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-40.jpg' },
  { id: 5, name: 'Rona Muthia Syaputri', role: 'Koordinator Media Sosial GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-41.jpg' },
  { id: 6, name: 'Rizki Ramadhan', role: 'Koordinator Kreatif GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-42.jpg' },
  { id: 7, name: 'Muhammad David', role: 'Penanggung Jawab Website GenBI Jambi', commission: 'BPI GenBI Provinsi Jambi', image: 'https://genbijambi.com/public/uploads/team-member-45.jpg' }
];

const prestasi = [
  { id: 1, name: 'Mahmudi', title: 'Juara 3 Lomba QRIS Jelajah Budaya Indonesia 2025', campus: 'UIN STS Jambi', category: 'Literasi QRIS', year: '2025', description: 'Capaian anggota GenBI dalam kompetisi nasional bertema QRIS dan budaya Indonesia.' },
  { id: 2, name: 'Irfan Aziz', title: 'Juara 1 Karya Tulis Ilmiah National Agriculture Competition Week 2025', campus: 'Universitas Jambi', category: 'Karya Tulis Ilmiah', year: '2025', description: 'Karya tulis ilmiah subtema inovasi digitalisasi pertanian dan modern smart farming.' },
  { id: 3, name: 'Rizky Desryan Syah', title: 'Gold Medal dan Bronze Medal Essay National Competition Jecofest', campus: 'Universitas Jambi', category: 'Essay', year: '2025', description: 'Prestasi nasional dalam kompetisi essay bidang ekonomi dan kewirausahaan.' },
  { id: 4, name: 'Rizky Desryan Syah', title: 'Penerima Pendanaan Program Inovasi Desa', campus: 'Universitas Jambi', category: 'Inovasi Desa', year: '2025', description: 'Program pendanaan inovasi yang mendukung pengembangan gagasan sosial di tingkat desa.' },
  { id: 5, name: 'Amalia Jelita', title: 'Pro Ide Ok Hadroh Nurul Musthafa', campus: 'Universitas Jambi', category: 'Kreativitas', year: '2025', description: 'Penghargaan dalam bidang kreativitas dan pengembangan aktivitas seni keagamaan.' },
  { id: 6, name: 'Catur Ragil Saputra', title: 'Juara 3 LKTI MUNAS Forum Nasional Manajemen Keuangan Syariah', campus: 'UIN STS Jambi', category: 'Ekonomi Syariah', year: '2025', description: 'Capaian pada forum nasional manajemen keuangan syariah di UIN Sunan Gunung Djati Bandung.' }
];

const news = [
  {
    id: 100,
    title: 'Talkshow Siginjai Fest 2026 Dorong Generasi Muda Berkarya',
    category: 'BANK INDONESIA',
    date: 'April 30, 2026',
    readTime: '5 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-100.jpg',
    excerpt: 'Talkshow Siginjai Fest 2026 digelar di Jambi Town Square dengan tema generasi muda muslim yang kreatif, produktif, dan berlandaskan prinsip syariah.',
    body: [
      'Pada Kamis, 30 April 2026, Siginjai Fest 2026 menghadirkan talkshow bertema Generasi Muda Muslim: Berkarya Sekarang, Berkah Selamanya di Jambi Town Square.',
      'Acara ini dihadiri GenBI Provinsi Jambi, mahasiswa, pelaku usaha muda, serta masyarakat umum yang tertarik pada pengembangan diri dan ekonomi syariah.',
      'Narasumber membahas keberanian memulai usaha, konsistensi proses, strategi komunikasi, branding, dan pentingnya prinsip syariah dalam kegiatan bisnis.',
      'Kegiatan berlangsung interaktif melalui sesi diskusi dan tanya jawab. Peserta aktif menyampaikan pertanyaan dan tanggapan selama acara berlangsung.'
    ],
    author: 'Muhamad David',
    editor: 'Mukhtada Billah Nst'
  },
  {
    id: 98,
    title: 'Talkshow Ekonomi Syariah Siginjai Fest',
    category: 'BANK INDONESIA',
    date: 'April 30, 2026',
    readTime: '5 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-98.jpg',
    excerpt: 'Siginjai Fest 2026 menggelar talkshow bertema penguatan ekonomi syariah dan halal value chain bersama Bank Indonesia, KNEKS, dan UIN STS Jambi.',
    body: [
      'Kegiatan talkshow dilaksanakan pada 29 April 2026 di Jambi Town Square sebagai bagian dari rangkaian Siginjai Fest 2026.',
      'Tema utama kegiatan adalah penguatan ekonomi syariah dan ekosistem halal value chain sebagai ruang edukasi publik yang inklusif.',
      'Narasumber dari Bank Indonesia, KNEKS, dan UIN STS Jambi menyampaikan perspektif kebijakan, praktik industri, dan penguatan akademik.',
      'Kegiatan ditutup dengan dokumentasi bersama dan harapan agar literasi ekonomi syariah semakin dekat dengan masyarakat luas.'
    ],
    author: 'Muhamad David',
    editor: 'Mukhtada Billah Nst'
  },
  {
    id: 97,
    title: 'GenBI Goes To Campus Universitas Jambi',
    category: 'GenBI Kolaborasi',
    date: 'April 24, 2026',
    readTime: '4 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-97.jpg',
    excerpt: 'BI Jambi menggelar GGTC UNJA 2026 yang diikuti 453 mahasiswa untuk memperkuat akses beasiswa kebanksentralan dan literasi ekonomi.',
    body: [
      'GenBI Goes To Campus Universitas Jambi melibatkan 453 peserta dan berfokus pada sosialisasi bantuan pendidikan kebanksentralan.',
      'Kegiatan ini turut memperkuat kerja sama kelembagaan melalui penandatanganan Perjanjian Kerja Sama antara Bank Indonesia dan Universitas Jambi.',
      'Peserta memperoleh pemahaman tentang peran Bank Indonesia, pengendalian inflasi, Cinta Bangga Paham Rupiah, QRIS, dan iBI Library.',
      'Program ini diharapkan dapat memperluas akses beasiswa dan meningkatkan literasi ekonomi mahasiswa.'
    ],
    author: 'Lisa Ardila',
    editor: 'Redaksi GenBI Jambi'
  },
  {
    id: 96,
    title: 'GenBI Goes To Campus UIN STS Jambi',
    category: 'BANK INDONESIA',
    date: 'April 15, 2026',
    readTime: '4 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-96.jpg',
    excerpt: 'Kegiatan kolaboratif Bank Indonesia dan GenBI digelar di UIN STS Jambi untuk sosialisasi program bantuan pendidikan kebanksentralan.',
    body: [
      'GenBI Goes To Campus UIN STS Jambi digelar sebagai wadah edukatif dan informatif bagi mahasiswa.',
      'Sosialisasi berfokus pada program beasiswa Bank Indonesia, persyaratan, mekanisme seleksi, dan manfaat bagi penerima.',
      'Materi juga mencakup peran Bank Indonesia, pengendalian inflasi, QRIS, Cinta Bangga Paham Rupiah, dan pemanfaatan iBI Library.',
      'Kegiatan ini menegaskan kolaborasi kampus dan Bank Indonesia dalam peningkatan kualitas sumber daya manusia.'
    ],
    author: 'Redaksi GenBI Jambi',
    editor: 'Redaksi GenBI Jambi'
  },
  {
    id: 95,
    title: 'GenBI Jambi Laksanakan Kegiatan Buka Bersama dan Aksi Sosial',
    category: 'GenBI Wilayah',
    date: 'March 07, 2026',
    readTime: '3 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-95.jpg',
    excerpt: 'GenBI Jambi melaksanakan kegiatan buka bersama dan aksi sosial di Panti Asuhan Al-Mahri.',
    body: [
      'Kegiatan buka bersama dan aksi sosial menjadi ruang kepedulian anggota GenBI Jambi kepada masyarakat.',
      'Agenda ini memperkuat nilai berbagi, kebersamaan, dan pengabdian sosial selama bulan Ramadan.',
      'Kegiatan diharapkan mampu membangun kepekaan sosial anggota dan memperluas manfaat organisasi.'
    ],
    author: 'Redaksi GenBI Jambi',
    editor: 'Redaksi GenBI Jambi'
  },
  {
    id: 94,
    title: 'GenBI Jambi Hadiri Diseminasi Kajian UMKM KCBN Muara Jambi',
    category: 'BANK INDONESIA',
    date: 'March 17, 2026',
    readTime: '4 menit baca',
    image: 'https://genbijambi.com/public/uploads/news-94.jpg',
    excerpt: 'KPw BI Provinsi Jambi menggelar diseminasi kajian pengembangan UMKM di Kawasan Cagar Budaya Nasional Muarajambi.',
    body: [
      'Diseminasi kajian UMKM KCBN Muara Jambi membahas integrasi desa penyangga dengan pariwisata budaya.',
      'Kegiatan ini diarahkan sebagai sumber pertumbuhan ekonomi baru yang berkelanjutan di Jambi.',
      'GenBI Jambi hadir sebagai bagian dari penguatan literasi dan kolaborasi komunitas muda.'
    ],
    author: 'Redaksi GenBI Jambi',
    editor: 'Redaksi GenBI Jambi'
  }
];


const adminStats = [
  { label: 'Total News Categories', value: 6, note: 'Kategori berita aktif', icon: 'newspaper', tone: 'blue' },
  { label: 'Total News', value: 80, note: 'Artikel terpublikasi', icon: 'documentText', tone: 'blue' },
  { label: 'Total Events', value: 6, note: 'Agenda komunitas', icon: 'calendar', tone: 'sky' },
  { label: 'Total Team Members', value: 19, note: 'Anggota dalam direktori', icon: 'users', tone: 'blue' },
  { label: 'Total Photos', value: 6, note: 'Galeri dokumentasi', icon: 'photo', tone: 'sky' },
  { label: 'Total Services', value: 6, note: 'Konten layanan', icon: 'squares', tone: 'blue' },
  { label: 'Total Testimonials', value: 0, note: 'Belum ada data', icon: 'chat', tone: 'muted' },
  { label: 'Pricing Tables', value: 3, note: 'Modul bawaan CMS', icon: 'table', tone: 'muted' }
];

const settingTabs = [
  { key: 'logo', label: 'Logo', icon: 'photo' },
  { key: 'favicon', label: 'Favicon', icon: 'sparkles' },
  { key: 'topbar', label: 'Top Bar', icon: 'bars' },
  { key: 'footer', label: 'Footer', icon: 'window' },
  { key: 'email', label: 'Email', icon: 'mail' },
  { key: 'banner', label: 'Banner', icon: 'image' },
  { key: 'sidebar', label: 'Sidebar', icon: 'list' },
  { key: 'color', label: 'Color', icon: 'swatch' }
];

const adminActivity = [
  { title: 'Logo publik diperiksa', area: 'Settings', time: 'Hari ini, 22.48', status: 'Selesai' },
  { title: 'Berita Siginjai Fest masuk daftar recent posts', area: 'News', time: 'Hari ini, 22.42', status: 'Aktif' },
  { title: 'Slider 1 dan Slider 4 dipakai sebagai hero background', area: 'Slider', time: 'Hari ini, 22.35', status: 'Review' },
  { title: 'Dashboard ringkas disiapkan untuk merge tahap awal', area: 'Admin', time: 'Hari ini, 22.30', status: 'Draft' }
];

window.GenBIData = {
  site,
  navItems,
  stats,
  aboutBlocks,
  programs,
  publicEvents,
  teamMembers,
  bpiMembers,
  prestasi,
  news,
  adminStats,
  settingTabs,
  adminActivity,
};

})();
