<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_news');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            try {
                $statement = $db->query('PRAGMA table_info(tbl_news)');
                $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
                $columns = array_column($rows, 'name');
            } catch (\Throwable) {
                $columns = [];
            }
        }

        $addColumn = static function (string $sql) use ($db): void {
            try {
                $db->exec($sql);
            } catch (\Throwable) {
            }
        };

        if (!in_array('comments_enabled', $columns, true)) {
            $addColumn('ALTER TABLE tbl_news ADD COLUMN comments_enabled TINYINT(1) NULL AFTER comment');
        }
        if (!in_array('voting_enabled', $columns, true)) {
            $addColumn('ALTER TABLE tbl_news ADD COLUMN voting_enabled TINYINT(1) NULL AFTER comments_enabled');
        }
        if (!in_array('replies_enabled', $columns, true)) {
            $addColumn('ALTER TABLE tbl_news ADD COLUMN replies_enabled TINYINT(1) NULL AFTER voting_enabled');
        }
        if (!in_array('max_reply_depth', $columns, true)) {
            $addColumn('ALTER TABLE tbl_news ADD COLUMN max_reply_depth TINYINT UNSIGNED NULL AFTER replies_enabled');
        }

        try {
            $db->exec("UPDATE tbl_news SET comments_enabled = CASE WHEN comment = 'On' THEN 1 WHEN comment = 'Off' THEN 0 ELSE comments_enabled END WHERE comments_enabled IS NULL");
        } catch (\Throwable) {
        }
    },
];
