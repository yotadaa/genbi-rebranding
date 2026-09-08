@php
$site = $site ?? [];
$logo = $site['logo'] ?? 'https://genbijambi.com/public/uploads/logo.png';
$siteName = $site['name'] ?? 'GenBI Provinsi Jambi';
$email = $site['email'] ?? 'genbijambibi@gmail.com';
$phone = $site['phone'] ?? '085669152702';
$socials = $site['socials'] ?? [
    ['name' => 'Instagram', 'url' => 'https://instagram.com/genbijambi', 'label' => 'Ig'],
    ['name' => 'YouTube', 'url' => 'https://youtube.com/@genbijambi', 'label' => 'Yt'],
    ['name' => 'WhatsApp', 'url' => 'https://wa.me/6285669152702', 'label' => 'Wa'],
];
$socialIcon = static function (string $name, string $url = ''): string {
    $channel = strtolower(trim($name . ' ' . $url));

    if (str_contains($channel, 'youtube') || str_contains($channel, 'youtu.be')) {
        return '<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M21 8.25a3 3 0 0 0-2.1-2.13C17.06 5.62 12 5.62 12 5.62s-5.06 0-6.9.5A3 3 0 0 0 3 8.25a31.9 31.9 0 0 0-.38 3.75A31.9 31.9 0 0 0 3 15.75a3 3 0 0 0 2.1 2.13c1.84.5 6.9.5 6.9.5s5.06 0 6.9-.5a3 3 0 0 0 2.1-2.13 31.9 31.9 0 0 0 .38-3.75A31.9 31.9 0 0 0 21 8.25Z" stroke="currentColor" stroke-width="1.65"/><path d="m10.25 15.15 4.5-3.15-4.5-3.15v6.3Z" fill="currentColor"/></svg>';
    }

    if (str_contains($channel, 'instagram')) {
        return '<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" focusable="false"><rect x="3.25" y="3.25" width="17.5" height="17.5" rx="5"/><circle cx="12" cy="12" r="4.1"/><circle cx="17.4" cy="6.7" r=".8" fill="currentColor" stroke="none"/></svg>';
    }

    if (str_contains($channel, 'whatsapp') || str_contains($channel, 'wa.me')) {
        return '<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.4 11.75a8.4 8.4 0 0 1-12.45 7.38L3.6 20.4l1.27-4.22A8.4 8.4 0 1 1 20.4 11.75Z"/><path d="M8.15 7.8c.2-.45.42-.46.73-.47h.62c.2 0 .4.03.53.36l.78 1.89c.1.28.03.49-.1.68l-.59.75c-.15.18-.12.36 0 .54.67 1.14 1.57 2.03 2.72 2.69.18.1.36.14.54-.02l.84-.98c.17-.2.38-.24.62-.14l1.85.87c.26.12.43.3.39.57-.16 1.08-.69 1.78-1.55 2.12-.7.28-1.58.2-2.49-.18a12.6 12.6 0 0 1-5.82-5.08c-.52-.84-.74-1.66-.64-2.35.07-.48.32-.9.57-1.25Z"/></svg>';
    }

    return '<svg class="social-mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M13.5 4.5h6v6"/><path d="m19.5 4.5-9 9"/><path d="M10.5 6.75H6A1.5 1.5 0 0 0 4.5 8.25V18A1.5 1.5 0 0 0 6 19.5h9.75a1.5 1.5 0 0 0 1.5-1.5v-4.5"/></svg>';
};
$navItems = [
    ['label' => 'Home', 'key' => 'home', 'href' => url('/')],
    ['label' => 'About', 'key' => 'about', 'href' => url('/about')],
    ['label' => 'Team', 'key' => 'team', 'href' => url('/team')],
    ['label' => 'Prestasi', 'key' => 'prestasi', 'href' => url('/prestasi')],
    ['label' => 'Kegiatan', 'key' => 'kegiatan', 'href' => url('/event'), 'children' => [
        ['label' => 'Agenda', 'key' => 'event', 'href' => url('/event')],
        ['label' => 'Program Utama', 'key' => 'feature', 'href' => url('/feature')],
    ]],
    ['label' => 'News', 'key' => 'news', 'href' => url('/news')],
    ['label' => 'Contact', 'key' => 'contact', 'href' => url('/contact')],
];
$activeKey = $activeNav ?? '';
@endphp
<div id="site-header-shell" class="site-header-shell">
  <div class="top-strip hidden md:block">
    <div class="site-container flex h-9 items-center justify-between text-[13px] text-white/90">
      <div class="flex items-center gap-4">
        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 hover:text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.62a2.25 2.25 0 0 1-2.36 0l-7.5-4.62a2.25 2.25 0 0 1-1.07-1.92v-.24"/></svg>{{ $email }}</a>
        <span class="h-4 w-px bg-white/30"></span>
        <a href="tel:{{ $phone }}" class="inline-flex items-center gap-2 hover:text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z"/></svg>{{ $phone }}</a>
      </div>
      <nav class="flex items-center gap-3" aria-label="Social links">
        @foreach ($socials as $social)
          @if (!empty($social['url']))
            <a href="{{ $social['url'] }}" class="social-mini" aria-label="Buka {{ $social['name'] }} GenBI di tab baru" title="{{ $social['name'] }}" target="_blank" rel="noopener noreferrer">{!! $socialIcon((string) ($social['name'] ?? ''), (string) $social['url']) !!}</a>
          @endif
        @endforeach
      </nav>
    </div>
  </div>
  <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
    <div class="site-container flex h-20 items-center justify-between">
      <a data-transition href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Go to home">
        <span class="logo-shell"><img src="{{ $logo }}" alt="{{ $siteName }}" class="h-9 w-auto" /></span>
        <span class="leading-tight">
          <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
          <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
        </span>
      </a>
      <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
        @foreach ($navItems as $item)
          @php
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            $isActive = $item['key'] === $activeKey || array_reduce($children, static fn(bool $carry, array $child): bool => $carry || ($child['key'] ?? '') === $activeKey, false);
          @endphp
          @if ($children)
            <div class="nav-dropdown {{ $isActive ? 'is-active' : '' }}">
              <a data-transition href="{{ $item['href'] }}" class="nav-link nav-dropdown-trigger {{ $isActive ? 'nav-link-active' : '' }}" aria-haspopup="true" aria-expanded="false">
                {{ $item['label'] }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
              </a>
              <div class="nav-dropdown-menu" role="menu">
                @foreach ($children as $child)
                  <a data-transition href="{{ $child['href'] }}" role="menuitem" class="{{ ($child['key'] ?? '') === $activeKey ? 'is-active' : '' }}">{{ $child['label'] ?? '' }}</a>
                @endforeach
              </div>
            </div>
          @else
            <a data-transition href="{{ $item['href'] }}" class="nav-link {{ $isActive ? 'nav-link-active' : '' }}">{{ $item['label'] }}</a>
          @endif
        @endforeach
      </nav>
      <div class="hidden items-center gap-3 lg:flex">
        <a data-transition href="{{ url('/contact') }}" class="btn btn-primary">Hubungi Kami <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></a>
      </div>
      <button id="open-menu" class="btn-icon lg:hidden" aria-label="Open menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg></button>
    </div>
  </header>
</div>
<div id="site-header-spacer" aria-hidden="true"></div>
<div id="mobile-panel" class="fixed inset-0 z-[70] hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden">
  <div class="mobile-sheet">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="logo-shell"><img src="{{ $logo }}" alt="{{ $siteName }}" class="h-8 w-auto" /></span>
        <span class="font-semibold text-neutral-950">Menu</span>
      </div>
      <button id="close-menu" class="btn-icon" aria-label="Close menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
    </div>
    <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
      @foreach ($navItems as $item)
        @php
          $children = is_array($item['children'] ?? null) ? $item['children'] : [];
          $isActive = $item['key'] === $activeKey || array_reduce($children, static fn(bool $carry, array $child): bool => $carry || ($child['key'] ?? '') === $activeKey, false);
        @endphp
        @if ($children)
          <div class="mobile-link-group">
            <a data-transition href="{{ $item['href'] }}" class="mobile-link {{ $isActive ? 'mobile-link-active' : '' }}">{{ $item['label'] }}<span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg></span></a>
            <div class="mobile-sub-links">
              @foreach ($children as $child)
                <a data-transition href="{{ $child['href'] }}" class="mobile-sub-link {{ ($child['key'] ?? '') === $activeKey ? 'is-active' : '' }}">{{ $child['label'] ?? '' }}</a>
              @endforeach
            </div>
          </div>
        @else
          <a data-transition href="{{ $item['href'] }}" class="mobile-link {{ $isActive ? 'mobile-link-active' : '' }}">{{ $item['label'] }}<span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg></span></a>
        @endif
      @endforeach
    </nav>
    <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
      <strong>{{ $siteName }}</strong><br />{{ $site['tagline'] ?? 'Bersama GenBI, Energi untuk Negeri' }}
    </div>
  </div>
</div>
