<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $columns = $db->query('SHOW COLUMNS FROM teams')->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('show_on_home', $columns, true)) {
            $db->exec('ALTER TABLE teams ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 0 AFTER tahun');
        }

        if (!in_array('home_sort_order', $columns, true)) {
            $db->exec('ALTER TABLE teams ADD COLUMN home_sort_order INT NOT NULL DEFAULT 0 AFTER show_on_home');
        }

        if (!in_array('deleted_at', $columns, true)) {
            $db->exec('ALTER TABLE teams ADD COLUMN deleted_at DATETIME NULL AFTER updated_at');
        }

        $indexes = $db->query('SHOW INDEX FROM teams')->fetchAll(\PDO::FETCH_ASSOC);
        $indexNames = array_column($indexes, 'Key_name');
        if (!in_array('idx_teams_home', $indexNames, true)) {
            $db->exec('ALTER TABLE teams ADD INDEX idx_teams_home (show_on_home, home_sort_order)');
        }
    },
];
