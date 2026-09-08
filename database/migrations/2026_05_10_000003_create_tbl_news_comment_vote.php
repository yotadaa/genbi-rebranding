<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_news_comment_vote (
            vote_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            comment_id INT NOT NULL,
            news_id INT NOT NULL,
            voter_key CHAR(64) NOT NULL,
            value TINYINT NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (vote_id),
            UNIQUE KEY uq_vote_per_commenter (comment_id, voter_key),
            KEY idx_vote_comment (comment_id),
            KEY idx_vote_news (news_id),
            KEY idx_vote_created_at (created_at),
            CONSTRAINT fk_vote_comment FOREIGN KEY (comment_id) REFERENCES tbl_news_comment(comment_id) ON DELETE CASCADE,
            CONSTRAINT fk_vote_news FOREIGN KEY (news_id) REFERENCES tbl_news(news_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
