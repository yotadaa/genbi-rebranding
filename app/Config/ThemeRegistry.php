<?php

declare(strict_types=1);

namespace App\Config;

final class ThemeRegistry
{
    private const GENBI_TOKENS = [
        'background-default' => '247 250 255',
        'background-elevated' => '255 255 255',
        'background-muted' => '237 244 251',
        'surface-default' => '255 255 255',
        'surface-raised' => '255 255 255',
        'surface-sunken' => '242 247 253',
        'text-primary' => '23 23 23',
        'text-secondary' => '82 82 82',
        'text-tertiary' => '117 107 99',
        'border-subtle' => '23 23 23',
        'border-strong' => '23 23 23',
        'brand-primary' => '17 75 154',
        'brand-primary-hover' => '12 53 114',
        'brand-soft' => '238 246 255',
        'status-success' => '34 139 34',
        'status-warning' => '180 120 20',
        'status-error' => '192 40 40',
        'focus-ring' => '17 75 154',
        'shadow-card' => '0 20px 70px rgb(23 23 23 / 0.045)',
        'font-sans' => '"Inter"',
        'font-serif' => '"Source Serif 4"',
        'radius-card' => '1.55rem',
        'radius-pill' => '999px',
    ];

    private const THEME_META = [
        'genbi' => ['name' => 'GenBI Template', 'mode' => 'light', 'locked' => true, 'personality' => 'Softened BI blue with warm editorial neutrals.'],
        'light-01' => ['name' => 'Paper', 'mode' => 'light', 'personality' => 'Warm neutral paper with blue-gray accent.'],
        'light-02' => ['name' => 'Sakura', 'mode' => 'light', 'personality' => 'Soft blush surfaces with restrained rose accent.'],
        'light-03' => ['name' => 'Sage', 'mode' => 'light', 'personality' => 'Muted olive accent with calm ivory surfaces.'],
        'light-04' => ['name' => 'Ocean', 'mode' => 'light', 'personality' => 'Airy cyan surfaces with navy structure.'],
        'light-05' => ['name' => 'Sandstone', 'mode' => 'light', 'personality' => 'Desert tan base with terracotta warmth.'],
        'light-06' => ['name' => 'Lavender', 'mode' => 'light', 'personality' => 'Soft violet background with indigo restraint.'],
        'light-07' => ['name' => 'Citrus', 'mode' => 'light', 'personality' => 'Pale cream with amber accent and graphite ink.'],
        'light-08' => ['name' => 'Mint', 'mode' => 'light', 'personality' => 'Cool mint surfaces with emerald accent.'],
        'light-09' => ['name' => 'Editorial', 'mode' => 'light', 'personality' => 'Pure paper, quiet dividers, black ink.'],
        'light-10' => ['name' => 'Porcelain', 'mode' => 'light', 'personality' => 'Cool near-white with subtle blue accent.'],
        'dark-01' => ['name' => 'Midnight', 'mode' => 'dark', 'personality' => 'Near-black navy with bright cyan accent.'],
        'dark-02' => ['name' => 'Slate', 'mode' => 'dark', 'personality' => 'Graphite dark UI with electric blue accent.'],
        'dark-03' => ['name' => 'Forest', 'mode' => 'dark', 'personality' => 'Deep green-black with sage balance.'],
        'dark-04' => ['name' => 'Wine', 'mode' => 'dark', 'personality' => 'Oxblood base with champagne contrast.'],
        'dark-05' => ['name' => 'Charcoal', 'mode' => 'dark', 'personality' => 'Neutral charcoal with cozy amber accent.'],
        'dark-06' => ['name' => 'Abyss', 'mode' => 'dark', 'personality' => 'Teal-black surfaces with aqua glow.'],
        'dark-07' => ['name' => 'Violet', 'mode' => 'dark', 'personality' => 'Purple-black base with lavender highlight.'],
        'dark-08' => ['name' => 'Copper', 'mode' => 'dark', 'personality' => 'Warm brown-dark base with copper accent.'],
        'dark-09' => ['name' => 'Obsidian', 'mode' => 'dark', 'personality' => 'Pure black with one clean blue accent.'],
        'dark-10' => ['name' => 'Aurora', 'mode' => 'dark', 'personality' => 'Navy foundation with vivid green-magenta accents.'],
    ];

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        static $themes = null;
        if (is_array($themes)) {
            return $themes;
        }

        $themes = [];
        $order = array_keys(self::THEME_META);
        foreach ($order as $index => $key) {
            $meta = self::THEME_META[$key];
            $themes[$key] = [
                'key' => $key,
                'name' => $meta['name'],
                'mode' => $meta['mode'],
                'locked' => $meta['locked'] ?? false,
                'personality' => $meta['personality'],
                'tokens' => self::buildTokens($key, $index),
            ];
        }

        return $themes;
    }

    /** @return array<string, mixed> */
    public static function get(string $key): array
    {
        $themes = self::all();
        return $themes[$key] ?? $themes['genbi'];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** @return list<array<string, mixed>> */
    public static function summaries(): array
    {
        $summary = [];
        foreach (self::all() as $key => $theme) {
            $tokens = $theme['tokens'];
            $summary[] = [
                'key' => $key,
                'name' => $theme['name'],
                'mode' => $theme['mode'],
                'locked' => $theme['locked'],
                'personality' => $theme['personality'],
                'tokens' => self::cssVariables($tokens),
                'swatches' => [
                    'rgb(' . $tokens['background-default'] . ')',
                    'rgb(' . $tokens['surface-default'] . ')',
                    'rgb(' . $tokens['brand-primary'] . ')',
                    'rgb(' . $tokens['text-primary'] . ')',
                ],
            ];
        }

        return $summary;
    }

    public static function inlineCss(string $key): string
    {
        $theme = self::get($key);
        $lines = [];
        foreach (self::cssVariables($theme['tokens']) as $token => $value) {
            $lines[] = '--' . $token . ': ' . $value . ';';
        }

        return ':root, html[data-theme="' . $theme['key'] . '"]{' . implode('', $lines) . '}';
    }

    /** @param array<string, string> $tokens @return array<string, string> */
    private static function cssVariables(array $tokens): array
    {
        $variables = $tokens;
        $variables['blue-900'] = $tokens['brand-primary-hover'];
        $variables['blue-800'] = $tokens['brand-primary'];
        $variables['blue-700'] = self::lightenChannels($tokens['brand-primary'], 18);
        $variables['blue-50'] = $tokens['brand-soft'];
        $variables['cream'] = $tokens['background-default'];
        $variables['stone'] = $tokens['background-muted'];
        $variables['surface-soft'] = $tokens['surface-sunken'];
        $variables['ink'] = $tokens['text-primary'];

        return $variables;
    }

    private static function lightenChannels(string $channels, int $amount): string
    {
        $parts = array_map('intval', preg_split('/\s+/', trim($channels)) ?: []);
        $parts = array_map(static fn (int $value): int => max(0, min(255, $value + $amount)), $parts);
        return implode(' ', $parts);
    }

    /** @return array<string, string> */
    private static function buildTokens(string $key, int $index): array
    {
        if ($key === 'genbi') {
            return self::GENBI_TOKENS;
        }

        $tokens = self::GENBI_TOKENS;
        $isDark = str_starts_with($key, 'dark-');
        $variant = $isDark ? $index - 10 : $index;

        if ($isDark) {
            $base = 12 + ($variant * 2);
            $tokens['background-default'] = $base . ' ' . ($base + 3) . ' ' . ($base + 8);
            $tokens['background-elevated'] = ($base + 8) . ' ' . ($base + 10) . ' ' . ($base + 14);
            $tokens['background-muted'] = ($base + 14) . ' ' . ($base + 16) . ' ' . ($base + 20);
            $tokens['surface-default'] = $tokens['background-elevated'];
            $tokens['surface-raised'] = ($base + 12) . ' ' . ($base + 14) . ' ' . ($base + 18);
            $tokens['surface-sunken'] = $tokens['background-default'];
            $tokens['text-primary'] = '242 239 232';
            $tokens['text-secondary'] = '204 201 194';
            $tokens['text-tertiary'] = '160 155 147';
            $tokens['brand-primary'] = match ($key) {
                'dark-01' => '64 196 255',
                'dark-02' => '82 151 255',
                'dark-03' => '110 170 132',
                'dark-04' => '207 175 143',
                'dark-05' => '219 158 76',
                'dark-06' => '57 197 205',
                'dark-07' => '157 126 232',
                'dark-08' => '193 126 69',
                'dark-09' => '83 145 255',
                default => '90 208 171',
            };
            $tokens['brand-primary-hover'] = match ($key) {
                'dark-01' => '35 160 223',
                'dark-02' => '56 117 217',
                'dark-03' => '86 137 103',
                'dark-04' => '180 142 104',
                'dark-05' => '186 128 51',
                'dark-06' => '33 167 175',
                'dark-07' => '128 95 205',
                'dark-08' => '165 98 42',
                'dark-09' => '51 113 223',
                default => '58 176 141',
            };
            $tokens['brand-soft'] = '34 45 60';
            $tokens['shadow-card'] = '0 20px 60px rgb(0 0 0 / 0.28)';
        } else {
            $warm = 245 + min($variant, 5);
            $tokens['background-default'] = $warm . ' ' . ($warm - 2) . ' ' . ($warm - 5);
            $tokens['background-elevated'] = '255 255 255';
            $tokens['background-muted'] = (238 + $variant) . ' ' . (239 + max(0, $variant - 2)) . ' ' . (240 + max(0, $variant - 4));
            $tokens['surface-default'] = '255 255 255';
            $tokens['surface-raised'] = '255 255 255';
            $tokens['surface-sunken'] = (244 + min(4, $variant)) . ' ' . (244 + max(0, min(4, $variant - 1))) . ' ' . (245 + max(0, min(4, $variant - 2)));
            $tokens['brand-primary'] = match ($key) {
                'light-01' => '74 95 120',
                'light-02' => '176 92 122',
                'light-03' => '95 125 92',
                'light-04' => '38 114 136',
                'light-05' => '166 103 67',
                'light-06' => '99 93 173',
                'light-07' => '176 121 26',
                'light-08' => '54 135 111',
                'light-09' => '22 78 99',
                default => '78 108 152',
            };
            $tokens['brand-primary-hover'] = match ($key) {
                'light-01' => '54 73 95',
                'light-02' => '148 68 99',
                'light-03' => '72 101 69',
                'light-04' => '22 87 109',
                'light-05' => '138 77 44',
                'light-06' => '73 69 145',
                'light-07' => '145 95 14',
                'light-08' => '36 109 88',
                'light-09' => '17 59 74',
                default => '56 86 126',
            };
            $tokens['brand-soft'] = match ($key) {
                'light-02' => '249 234 239',
                'light-05' => '248 236 226',
                'light-06' => '241 237 252',
                'light-08' => '233 247 242',
                'light-09' => '234 242 246',
                default => '238 246 255',
            };
            $tokens['shadow-card'] = '0 18px 52px rgb(23 23 23 / 0.05)';
        }

        return $tokens;
    }
}
