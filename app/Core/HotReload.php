<?php

declare(strict_types=1);

namespace App\Core;

final class HotReload
{
    private const ENDPOINT = '/__hot-reload';
    private const INTERVAL_MS = 1000;

    public static function enabled(): bool
    {
        if (PHP_SAPI !== 'cli-server') {
            return false;
        }

        $flag = getenv('GENBI_HOT_RELOAD');
        return $flag === false || $flag === '' || !in_array(strtolower((string) $flag), ['0', 'false', 'off'], true);
    }

    public static function endpoint(): string
    {
        return self::ENDPOINT;
    }

    public static function intervalMs(): int
    {
        return self::INTERVAL_MS;
    }

    public static function token(string $rootPath): string
    {
        $files = self::watchedFiles($rootPath);
        $payload = [];

        foreach ($files as $file) {
            $payload[] = $file['path'] . ':' . $file['mtime'] . ':' . $file['size'];
        }

        return sha1(implode('|', $payload));
    }

    public static function inject(string $html): string
    {
        if (!self::enabled()) {
            return $html;
        }

        $snippet = self::snippet();
        $position = strripos($html, '</body>');
        if ($position === false) {
            return $html . $snippet;
        }

        return substr($html, 0, $position) . $snippet . substr($html, $position);
    }

    private static function snippet(): string
    {
        $endpoint = json_encode(self::endpoint(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $interval = (string) self::intervalMs();

        return <<<HTML
<script>
(function () {
  if (window.__genbiHotReload) {
    return;
  }

  window.__genbiHotReload = true;

  const endpoint = {$endpoint};
  const interval = {$interval};
  let token = null;
  let inFlight = false;

  async function poll() {
    if (inFlight) {
      return;
    }

    inFlight = true;

    try {
      const response = await fetch(endpoint, { cache: 'no-store', credentials: 'same-origin' });
      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload || typeof payload.token !== 'string') {
        return;
      }

      if (token === null) {
        token = payload.token;
        return;
      }

      if (payload.token !== token) {
        window.location.reload();
      }
    } catch (error) {
      // Ignore transient dev-server errors.
    } finally {
      inFlight = false;
    }
  }

  poll();
  window.setInterval(poll, interval);
})();
</script>
HTML;
    }

    /**
     * @return array<int, array{path: string, mtime: int, size: int}>
     */
    private static function watchedFiles(string $rootPath): array
    {
        $watchTargets = [
            $rootPath . '/app',
            $rootPath . '/bootstrap',
            $rootPath . '/database',
            $rootPath . '/fallbacks',
            $rootPath . '/public',
            $rootPath . '/routes',
            $rootPath . '/package.json',
            $rootPath . '/tailwind.config.js',
        ];

        $files = [];

        foreach ($watchTargets as $target) {
            if (is_file($target)) {
                $files[] = self::fileState($rootPath, $target);
                continue;
            }

            if (!is_dir($target)) {
                continue;
            }

            $directoryIterator = new \RecursiveDirectoryIterator(
                $target,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            );
            $filterIterator = new \RecursiveCallbackFilterIterator(
                $directoryIterator,
                static function (\SplFileInfo $item): bool {
                    $path = str_replace('\\', '/', $item->getPathname());
                    return !preg_match('#/(?:storage|uploads|node_modules|\.git)(?:/|$)#', $path);
                }
            );
            $iterator = new \RecursiveIteratorIterator($filterIterator);

            foreach ($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $files[] = self::fileState($rootPath, $item->getPathname());
            }
        }

        usort($files, static fn (array $left, array $right): int => $left['path'] <=> $right['path']);

        return $files;
    }

    /**
     * @return array{path: string, mtime: int, size: int}
     */
    private static function fileState(string $rootPath, string $path): array
    {
        $relative = $path;
        if (str_starts_with($path, $rootPath)) {
            $relative = substr($path, strlen($rootPath));
        }
        $relative = str_replace('\\', '/', ltrim($relative, '/\\'));
        $mtime = filemtime($path);
        $size = filesize($path);

        return [
            'path' => $relative,
            'mtime' => $mtime === false ? 0 : (int) $mtime,
            'size' => $size === false ? 0 : (int) $size,
        ];
    }
}
