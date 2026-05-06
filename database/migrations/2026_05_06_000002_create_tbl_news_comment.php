<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE tbl_news_comment (
            comment_id INT NOT NULL AUTO_INCREMENT,
            news_id INT NOT NULL,
            parent_id INT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL,
            website VARCHAR(180) NULL,
            content TEXT NOT NULL,
            status ENUM('pending','approved','rejected','spam') NOT NULL DEFAULT 'pending',
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            moderated_by INT NULL,
            moderated_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (comment_id),
            INDEX idx_news_comment_news_id (news_id),
            INDEX idx_news_comment_parent_id (parent_id),
            INDEX idx_news_comment_status (status),
            INDEX idx_news_comment_created_at (created_at),
            CONSTRAINT fk_news_comment_news FOREIGN KEY (news_id) REFERENCES tbl_news(news_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
