<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_feature');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        $idColumn = in_array('feature_id', $columns, true) ? 'feature_id' : (in_array('id', $columns, true) ? 'id' : null);
        if ($idColumn === null || !in_array('title', $columns, true)) {
            return;
        }

        $targetTitles = [
            'KKG',
            'SIGINJAI',
            'GENTALA ARASY',
            'GENBI LEADERSHIP CAMP',
            'GGTC',
            'GENBI SERTIFIKASI',
        ];

        $find = $db->prepare('SELECT ' . $idColumn . ' FROM tbl_feature WHERE title = :title ORDER BY ' . $idColumn . ' ASC');
        foreach ($targetTitles as $title) {
            $find->execute([':title' => $title]);
            $ids = array_map('intval', $find->fetchAll(\PDO::FETCH_COLUMN));
            if (count($ids) <= 1) {
                continue;
            }

            $keep = array_shift($ids);
            if ($keep <= 0 || $ids === []) {
                continue;
            }

            $sets = [];
            if (in_array('show_on_home', $columns, true)) {
                $sets[] = 'show_on_home = 0';
            }
            if (in_array('status', $columns, true)) {
                $sets[] = "status = 'archived'";
            }
            if (in_array('updated_at', $columns, true)) {
                $sets[] = 'updated_at = NOW()';
            }
            if (in_array('deleted_at', $columns, true)) {
                $sets[] = 'deleted_at = NOW()';
            }
            if ($sets === []) {
                continue;
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = 'UPDATE tbl_feature SET ' . implode(', ', $sets) . ' WHERE ' . $idColumn . ' IN (' . $placeholders . ')';
            $stmt = $db->prepare($sql);
            foreach ($ids as $index => $id) {
                $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
            }
            $stmt->execute();
        }
    },
];

