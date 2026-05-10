<?php

declare(strict_types=1);

namespace App\Services;

final class CommentThrottleService
{
    private const WINDOW_SECONDS = 900;
    private const STORAGE_DIR = '/storage/throttle/';

    public function tooManyAttempts(string $bucket, string $ip, int $limit): bool
    {
        if ($limit < 1 || $ip === '') {
            return false;
        }

        return count($this->readBucket($bucket, $ip)) >= $limit;
    }

    public function recordAttempt(string $bucket, string $ip): void
    {
        if ($ip === '') {
            return;
        }

        $entries = $this->readBucket($bucket, $ip);
        $entries[] = time();

        $dir = $this->storageDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = $dir . '/' . $this->filename($bucket, $ip);
        @file_put_contents($file, json_encode($entries), LOCK_EX);
    }

    /** @return array<int, int> */
    private function readBucket(string $bucket, string $ip): array
    {
        $file = $this->storageDir() . '/' . $this->filename($bucket, $ip);
        if (!is_file($file)) {
            return [];
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return [];
        }

        $entries = json_decode($data, true);
        if (!is_array($entries)) {
            return [];
        }

        $cutoff = time() - self::WINDOW_SECONDS;

        return array_values(array_filter(
            array_map('intval', $entries),
            static fn(int $ts): bool => $ts >= $cutoff
        ));
    }

    private function filename(string $bucket, string $ip): string
    {
        return hash('sha256', $bucket . '|' . $ip) . '.json';
    }

    private function storageDir(): string
    {
        return dirname(__DIR__, 2) . self::STORAGE_DIR;
    }
}
