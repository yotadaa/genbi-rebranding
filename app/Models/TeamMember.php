<?php

declare(strict_types=1);

namespace App\Models;

class TeamMember
{
    public function __construct(private ?\PDO $db = null) {}

    /**
     * Map a raw DB row to a normalized frontend-friendly shape.
     */
    public static function mapRow(array $row): array
    {
        $name = $row['name'] ?? '';
        $designation = $row['designation'] ?? '';
        $detail = $row['detail'] ?? '';
        $photo = $row['photo'] ?? '';

        // Extract campus from detail HTML (e.g., "<p><b>UNIVERSITAS JAMBI</b></p>")
        $campus = '';
        if (preg_match('/<b>([^<]+)<\/b>/', $detail, $matches)) {
            $campus = trim($matches[1]);
        }

        // Normalize campus name
        $campusLower = mb_strtolower($campus);
        if (str_contains($campusLower, 'uin') || str_contains($campusLower, 'sulthan') || str_contains($campusLower, 'thaha')) {
            $campus = 'UIN STS Jambi';
        } elseif (str_contains($campusLower, 'universitas jambi') || str_contains($campusLower, 'unja')) {
            $campus = 'Universitas Jambi';
        }

        // Derive commission from campus
        $commission = match (true) {
            str_contains($campusLower, 'uin') || str_contains($campusLower, 'sulthan') => 'Komisariat UIN STS Jambi',
            str_contains($campusLower, 'universitas jambi') || str_contains($campusLower, 'unja') => 'Komisariat Universitas Jambi',
            default => 'Badan Pengurus Inti',
        };

        // Derive division from designation
        $designationLower = mb_strtolower($designation);
        $division = 'Umum';
        if (str_contains($designationLower, 'ketua') || str_contains($designationLower, 'sekretaris') || str_contains($designationLower, 'bendahara') || str_contains($designationLower, 'koordinator')) {
            $division = 'Badan Pengurus Inti';
            $commission = 'Badan Pengurus Inti';
        } elseif (str_contains($designationLower, 'media') || str_contains($designationLower, 'multimedia') || str_contains($designationLower, 'kreatif') || str_contains($designationLower, 'creative')) {
            $division = 'Multimedia';
        } elseif (str_contains($designationLower, 'redaksi') || str_contains($designationLower, 'website') || str_contains($designationLower, 'web')) {
            $division = 'Redaksi';
        } elseif (str_contains($designationLower, 'lingkungan')) {
            $division = 'Lingkungan Hidup';
        } elseif (str_contains($designationLower, 'pendidikan') || str_contains($designationLower, 'literasi')) {
            $division = 'Pendidikan';
        } elseif (str_contains($designationLower, 'sosial') || str_contains($designationLower, 'masyarakat')) {
            $division = 'Sosial Masyarakat';
        } elseif (str_contains($designationLower, 'ekonomi') || str_contains($designationLower, 'kewirausahaan')) {
            $division = 'Ekonomi';
        }

        // Build photo URL
        $photoUrl = '';
        if ($photo !== '') {
            $photoUrl = str_starts_with($photo, 'http') ? $photo : 'https://genbijambi.com/public/uploads/' . $photo;
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => $name,
            'role' => $designation,
            'division' => $division,
            'campus' => $campus,
            'commission' => $commission,
            'year' => '2025',
            'status' => 'Pengurus',
            'bio' => strip_tags($detail) ?: $designation,
            'photo' => $photoUrl,
            'email' => $row['email'] ?? '',
            'instagram' => $row['instagram'] ?? '',
        ];
    }

    /**
     * Get all active team members.
     */
    public function allActive(): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->query('SELECT * FROM tbl_team_member ORDER BY id ASC');
            return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Find a team member by ID.
     */
    public function findById(int $id): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare('SELECT * FROM tbl_team_member WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? self::mapRow($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get BPI (core leadership) members.
     */
    public function bpiCore(int $limit = 10): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM tbl_team_member WHERE LOWER(designation) REGEXP 'ketua|sekretaris|bendahara|koordinator' ORDER BY id ASC LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable) {
            return [];
        }
    }
}
