<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $rows = [
            ['comments.enabled', '1', 'bool', 'Allow comments globally'],
            ['comments.voting_enabled', '1', 'bool', 'Allow upvote/downvote'],
            ['comments.replies_enabled', '1', 'bool', 'Allow nested replies'],
            ['comments.max_reply_depth', '3', 'int', 'Max visible reply depth'],
            ['comments.replies_require_moderation', '1', 'bool', 'Replies require admin approval'],
            ['comments.reply_sort', 'oldest_first', 'string', 'Reply sort order'],
            ['comments.root_sort', 'newest_first', 'string', 'Root comment sort order'],
            ['comments.rate_limit_per_ip_per_15min', '20', 'int', 'Comment submit rate limit'],
            ['comments.vote_rate_limit_per_ip_per_15min', '60', 'int', 'Vote rate limit'],
        ];

        $insert = $db->prepare('INSERT INTO tbl_setting (setting_key, setting_value, setting_type, description, updated_at) VALUES (:key, :value, :type, :description, CURRENT_TIMESTAMP)');
        $update = $db->prepare('UPDATE tbl_setting SET setting_value = :value, setting_type = :type, description = :description, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :key');
        $exists = $db->prepare('SELECT 1 FROM tbl_setting WHERE setting_key = :key LIMIT 1');

        foreach ($rows as [$key, $value, $type, $description]) {
            $exists->execute([':key' => $key]);
            $params = [
                ':key' => $key,
                ':value' => $value,
                ':type' => $type,
                ':description' => $description,
            ];

            if ($exists->fetchColumn()) {
                $update->execute($params);
                continue;
            }

            $insert->execute($params);
        }
    },
];
