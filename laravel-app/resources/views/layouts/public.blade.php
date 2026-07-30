@php
$themeKey = $themeKey ?? 'genbi';
$inlineThemeCss = $inlineThemeCss ?? '';
$sitePayload = $sitePayload ?? [];
$settingsJson = json_encode($sitePayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
@endphp
<!doctype html>
<html lang="id" data-theme="{{ $themeKey }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {!! $meta ?? '<title>GenBI Provinsi Jambi</title>' !!}
  @if ($inlineThemeCss !== '')<style>{!! $inlineThemeCss !!}</style>@endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700&display=swap"></noscript>
  <link rel="stylesheet" href="/assets/css/tailwind.css?v=20260616g">
  <link rel="stylesheet" href="/assets/css/theme.css?v=20260510a">
  <link rel="stylesheet" href="/assets/css/styles.css?v=20260617a">
  {!! $jsonld ?? '' !!}
  @if (!empty($sitePayload['favicon']))<link rel="icon" href="{{ (string) $sitePayload['favicon'] }}">@else<link rel="icon" href="/uploads/logo.png">@endif
</head>
<body class="{{ $bodyClass ?? '' }}">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-blue-700 focus:rounded focus:shadow-lg">Langsung ke konten</a>
  <div id="site-header">@include('partials.public-header')</div>
  <main id="main-content">
    @yield('content')
  </main>
  <footer id="site-footer">@include('partials.public-footer')</footer>
  <div id="modal-root"></div>
  <script>window.GenBISiteSettings = {!! $settingsJson !!};</script>
  <script defer src="/assets/js/dist/data.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/api-core.js?v=20260730b"></script>
  <script defer src="/assets/js/dist/api.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/app.js?v=20260730b"></script>
  <script defer src="/assets/js/dist/lib/ui.js?v=20260616g"></script>
  {!! $scripts ?? '' !!}
</body>
</html>
