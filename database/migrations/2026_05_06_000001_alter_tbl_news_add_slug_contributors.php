<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_news');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        $add = static function (string $sql) use ($db): void {
            try { $db->exec($sql); } catch (\Throwable) {}
        };

        if (!in_array('slug', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN slug VARCHAR(255) NULL AFTER news_id');
        }
        if (!in_array('contributor_redaksi', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN contributor_redaksi VARCHAR(120) NULL AFTER comment');
        }
        if (!in_array('contributor_pewarta', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN contributor_pewarta VARCHAR(120) NULL AFTER contributor_redaksi');
        }
        if (!in_array('contributor_editor', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN contributor_editor VARCHAR(120) NULL AFTER contributor_pewarta');
        }
        if (!in_array('content_json', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN content_json LONGTEXT NULL AFTER news_content');
        }
        if (!in_array('status', $columns, true)) {
            $add("ALTER TABLE tbl_news ADD COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'published' AFTER published");
        }
        if (!in_array('published_at', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN published_at DATETIME NULL AFTER status');
        }
        if (!in_array('created_at', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN created_at DATETIME NULL AFTER published_at');
        }
        if (!in_array('updated_at', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN updated_at DATETIME NULL AFTER created_at');
        }
        if (!in_array('deleted_at', $columns, true)) {
            $add('ALTER TABLE tbl_news ADD COLUMN deleted_at DATETIME NULL AFTER updated_at');
        }

        // Add indexes (ignore if already exist)
        $add('ALTER TABLE tbl_news ADD UNIQUE KEY uq_tbl_news_slug (slug)');
        $add('ALTER TABLE tbl_news ADD INDEX idx_tbl_news_category_id (category_id)');
        $add('ALTER TABLE tbl_news ADD INDEX idx_tbl_news_date (news_date)');
        $add('ALTER TABLE tbl_news ADD INDEX idx_tbl_news_status (status)');
        $add('ALTER TABLE tbl_news ADD INDEX idx_tbl_news_published (published)');
    },
];
