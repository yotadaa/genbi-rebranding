<?php

declare(strict_types=1);

namespace App\Services;

final class SeoConfig
{
    public const SITE_NAME = 'GenBI Provinsi Jambi';
    public const BASE_URL = 'https://genbijambi.com';
    public const DEFAULT_OG_IMAGE = '/assets/images/default-og-genbi.jpg';
    public const DEFAULT_DESCRIPTION = 'Profil, kegiatan, berita, prestasi, dan agenda GenBI Provinsi Jambi.';

    /** @return array<string, array{title: string, description: string, path: string}> */
    public static function pages(): array
    {
        return [
            'index.html' => [
                'title' => 'GenBI Provinsi Jambi | Generasi Baru Indonesia',
                'description' => 'Website resmi GenBI Provinsi Jambi. Profil organisasi, berita, prestasi, agenda, dan informasi penerima beasiswa Bank Indonesia.',
                'path' => '/',
            ],
            'about.html' => [
                'title' => 'Tentang GenBI Provinsi Jambi',
                'description' => 'Profil organisasi Generasi Baru Indonesia (GenBI) Provinsi Jambi, visi misi, dan sejarah.',
                'path' => '/about',
            ],
            'team.html' => [
                'title' => 'Pengurus dan Anggota GenBI Provinsi Jambi',
                'description' => 'Struktur kepengurusan dan anggota GenBI Provinsi Jambi dari berbagai komisariat.',
                'path' => '/team',
            ],
            'prestasi.html' => [
                'title' => 'Prestasi GenBI Provinsi Jambi',
                'description' => 'Daftar prestasi dan pencapaian anggota GenBI Provinsi Jambi di tingkat regional dan nasional.',
                'path' => '/prestasi',
            ],
            'news.html' => [
                'title' => 'Berita GenBI Provinsi Jambi',
                'description' => 'Berita terbaru seputar kegiatan dan program GenBI Provinsi Jambi.',
                'path' => '/news',
            ],
            'contact.html' => [
                'title' => 'Kontak GenBI Provinsi Jambi',
                'description' => 'Informasi kontak resmi dan alamat sekretariat GenBI Provinsi Jambi.',
                'path' => '/contact',
            ],
        ];
    }
}
