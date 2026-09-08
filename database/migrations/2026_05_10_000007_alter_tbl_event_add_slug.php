<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $columns = [];
        try {
            $statement = $db->query('DESCRIBE tbl_event');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        if ($columns === []) {
            return;
        }

        if (!in_array('slug', $columns, true)) {
            try {
                $db->exec('ALTER TABLE tbl_event ADD COLUMN slug VARCHAR(255) NULL AFTER event_id');
            } catch (\Throwable) {
                // Keep migration idempotent on partially modified schemas.
            }
        }

        $existingIndexes = [];
        try {
            $statement = $db->query('SHOW INDEX FROM tbl_event');
            $existingIndexes = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Key_name') : [];
        } catch (\Throwable) {
            $existingIndexes = [];
        }

        $rows = [];
        try {
            $statement = $db->query('SELECT event_id, event_title, slug FROM tbl_event ORDER BY event_id ASC');
            $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable) {
            $rows = [];
        }

        if ($rows !== []) {
            $usedSlugs = [];
            foreach ($rows as $row) {
                $eventId = (int) ($row['event_id'] ?? 0);
                if ($eventId < 1) {
                    continue;
                }

                $currentSlug = trim((string) ($row['slug'] ?? ''));
                $base = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', (string) ($row['event_title'] ?? 'event')), '-'));
                $base = $base !== '' ? $base : 'event';
                $slug = $currentSlug !== '' ? $currentSlug : $base . '-' . $eventId;
                $slug = strtolower(trim((string) preg_replace('/[^a-z0-9-]+/i', '-', $slug), '-'));
                $slug = $slug !== '' ? $slug : 'event-' . $eventId;

                if (!preg_match('/-' . preg_quote((string) $eventId, '/') . '$/', $slug)) {
                    $slug .= '-' . $eventId;
                }

                $candidate = $slug;
                $suffix = 2;
                while (isset($usedSlugs[$candidate])) {
                    $candidate = $slug . '-' . $suffix;
                    $suffix++;
                }
                $usedSlugs[$candidate] = true;

                try {
                    $statement = $db->prepare('UPDATE tbl_event SET slug = :slug WHERE event_id = :id');
                    $statement->execute([
                        ':slug' => $candidate,
                        ':id' => $eventId,
                    ]);
                } catch (\Throwable) {
                    // Continue best-effort backfill for remaining rows.
                }
            }
        }

        if (!in_array('uq_tbl_event_slug', $existingIndexes, true)) {
            try {
                $db->exec('ALTER TABLE tbl_event ADD UNIQUE KEY uq_tbl_event_slug (slug)');
            } catch (\Throwable) {
                // Skip if an equivalent index already exists under a different name.
            }
        }
    },
];
