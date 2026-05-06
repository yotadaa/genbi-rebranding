<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("ALTER TABLE tbl_news
            ADD COLUMN slug VARCHAR(255) NULL AFTER news_id,
            ADD COLUMN contributor_redaksi VARCHAR(120) NULL AFTER comment,
            ADD COLUMN contributor_pewarta VARCHAR(120) NULL AFTER contributor_redaksi,
            ADD COLUMN contributor_editor VARCHAR(120) NULL AFTER contributor_pewarta,
            ADD COLUMN content_json LONGTEXT NULL AFTER news_content,
            ADD COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'published' AFTER published,
            ADD COLUMN published_at DATETIME NULL AFTER status,
            ADD COLUMN created_at DATETIME NULL AFTER published_at,
            ADD COLUMN updated_at DATETIME NULL AFTER created_at,
            ADD COLUMN deleted_at DATETIME NULL AFTER updated_at");

        $db->exec('ALTER TABLE tbl_news
            ADD UNIQUE KEY uq_tbl_news_slug (slug),
            ADD INDEX idx_tbl_news_category_id (category_id),
            ADD INDEX idx_tbl_news_date (news_date),
            ADD INDEX idx_tbl_news_status (status),
            ADD INDEX idx_tbl_news_published (published)');
    },
];
