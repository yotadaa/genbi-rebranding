<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HtmlSanitizer;
use PDO;
use Throwable;

final class ContactSetting
{
    private const DEFAULTS = [
        'place_name' => 'Bank Indonesia Jambi',
        'address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
        'email' => 'genbijambibi@gmail.com',
        'phone' => '085669152702',
        'coordinates_label' => '9HRM+74 Telanaipura, Kota Jambi, Jambi',
        'maps_url' => 'https://www.google.com/maps/place/Bank+Indonesia+Jambi/@-1.6092871,103.5827899,17z/data=!3m1!4b1!4m6!3m5!1s0x2e25885c04515687:0xe424228e0264e09a!8m2!3d-1.6092871!4d103.5827899!16s%2Fg%2F1pzr95__x?hl=id&entry=ttu',
        'latitude' => '-1.609287',
        'longitude' => '103.582790',
        'meta_title' => 'Contact | GenBI Provinsi Jambi',
        'meta_keyword' => 'GenBI Jambi, Contact',
        'meta_description' => 'Hubungi GenBI Provinsi Jambi untuk kolaborasi, informasi kegiatan, dan kebutuhan komunikasi resmi.',
    ];

    public function __construct(private ?PDO $db = null) {}

    /** @return array<string, string> */
    public function get(): array
    {
        if (!$this->db) {
            return $this->decorate(self::DEFAULTS);
        }

        try {
            $stmt = $this->db->query('SELECT * FROM tbl_contact_setting WHERE id = 1 LIMIT 1');
            $row = $stmt?->fetch(PDO::FETCH_ASSOC);
            if (!is_array($row)) {
                return $this->decorate(self::DEFAULTS);
            }

            $data = self::DEFAULTS;
            foreach (array_keys(self::DEFAULTS) as $field) {
                if (isset($row[$field]) && $row[$field] !== null) {
                    $data[$field] = trim((string) $row[$field]);
                }
            }

            return $this->decorate($data);
        } catch (Throwable) {
            return $this->decorate(self::DEFAULTS);
        }
    }

    /** @param array<string, mixed> $payload */
    public function save(array $payload): bool
    {
        if (!$this->db) {
            return false;
        }

        $clean = $this->sanitize($payload);
        $now = date('Y-m-d H:i:s');

        try {
            $existsStmt = $this->db->query('SELECT id FROM tbl_contact_setting WHERE id = 1 LIMIT 1');
            $exists = (int) ($existsStmt?->fetchColumn() ?? 0) === 1;

            if ($exists) {
                $sql = 'UPDATE tbl_contact_setting
                    SET place_name = :place_name, address = :address, email = :email, phone = :phone, coordinates_label = :coordinates_label,
                        maps_url = :maps_url, latitude = :latitude, longitude = :longitude, meta_title = :meta_title, meta_keyword = :meta_keyword,
                        meta_description = :meta_description, updated_at = :updated_at
                    WHERE id = 1';
            } else {
                $sql = 'INSERT INTO tbl_contact_setting
                    (id, place_name, address, email, phone, coordinates_label, maps_url, latitude, longitude, meta_title, meta_keyword, meta_description, created_at, updated_at)
                    VALUES (1, :place_name, :address, :email, :phone, :coordinates_label, :maps_url, :latitude, :longitude, :meta_title, :meta_keyword, :meta_description, :created_at, :updated_at)';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':place_name' => $clean['place_name'],
                ':address' => $clean['address'],
                ':email' => $clean['email'],
                ':phone' => $clean['phone'],
                ':coordinates_label' => $clean['coordinates_label'],
                ':maps_url' => $clean['maps_url'],
                ':latitude' => $clean['latitude'],
                ':longitude' => $clean['longitude'],
                ':meta_title' => $clean['meta_title'],
                ':meta_keyword' => $clean['meta_keyword'],
                ':meta_description' => $clean['meta_description'],
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** @return array<string, string> */
    public function sanitize(array $payload): array
    {
        $current = $this->get();
        $clean = [];
        foreach (self::DEFAULTS as $field => $default) {
            $value = array_key_exists($field, $payload) ? (string) $payload[$field] : ($current[$field] ?? $default);
            $clean[$field] = trim(strip_tags($value));
        }

        $clean['place_name'] = mb_substr($clean['place_name'], 0, 120);
        $clean['address'] = mb_substr($clean['address'], 0, 255);
        $clean['email'] = mb_substr($clean['email'], 0, 120);
        $clean['phone'] = mb_substr($clean['phone'], 0, 80);
        $clean['coordinates_label'] = mb_substr($clean['coordinates_label'], 0, 160);
        $clean['meta_title'] = mb_substr($clean['meta_title'], 0, 255);
        $clean['meta_keyword'] = mb_substr($clean['meta_keyword'], 0, 255);
        $clean['meta_description'] = mb_substr($clean['meta_description'], 0, 1000);

        $clean['maps_url'] = HtmlSanitizer::sanitizeMapEmbedUrl($clean['maps_url']);

        $coordsFromUrl = self::extractLatLngFromUrl($clean['maps_url']);
        $lat = self::normalizeCoord($clean['latitude']);
        $lng = self::normalizeCoord($clean['longitude']);
        if ($lat === '' || $lng === '') {
            if ($coordsFromUrl !== null) {
                $lat = $coordsFromUrl['lat'];
                $lng = $coordsFromUrl['lng'];
            }
        }
        $clean['latitude'] = $lat;
        $clean['longitude'] = $lng;

        return $clean;
    }

    /** @param array<string, string> $data @return array<string, string> */
    private function decorate(array $data): array
    {
        $data['map_embed_url'] = '';
        if ($data['latitude'] !== '' && $data['longitude'] !== '') {
            $data['map_embed_url'] = sprintf(
                'https://www.google.com/maps?q=%s,%s&z=17&output=embed',
                rawurlencode($data['latitude']),
                rawurlencode($data['longitude'])
            );
        }

        return $data;
    }

    private static function normalizeCoord(string $value): string
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/^-?\d{1,3}(?:\.\d+)?$/', $value)) {
            return '';
        }
        $number = (float) $value;
        return number_format($number, 6, '.', '');
    }

    /** @return array{lat: string, lng: string}|null */
    public static function extractLatLngFromUrl(string $url): ?array
    {
        if ($url === '') {
            return null;
        }

        if (preg_match('/@(-?\d{1,3}(?:\.\d+)?),(-?\d{1,3}(?:\.\d+)?)/', $url, $matches) === 1) {
            return [
                'lat' => self::normalizeCoord($matches[1]),
                'lng' => self::normalizeCoord($matches[2]),
            ];
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if (!is_string($query)) {
            return null;
        }
        parse_str($query, $params);
        $q = trim((string) ($params['q'] ?? ''));
        if ($q === '' || !preg_match('/(-?\d{1,3}(?:\.\d+)?),\s*(-?\d{1,3}(?:\.\d+)?)/', $q, $matches)) {
            return null;
        }

        return [
            'lat' => self::normalizeCoord($matches[1]),
            'lng' => self::normalizeCoord($matches[2]),
        ];
    }
}
