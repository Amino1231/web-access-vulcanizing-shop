<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        {{-- Apply theme before render to avoid flash --}}
        <script>
            (function () {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Leaflet — free maps, no API key --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <style>
            /* Leaflet dark mode */
            .dark .leaflet-tile { filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7); }
            .dark .leaflet-container { background: #0f172a; }
            .dark .leaflet-popup-content-wrapper,
            .dark .leaflet-popup-tip { background: #1e293b; color: #f1f5f9; }
            .dark .leaflet-popup-content a { color: #ff5e14; }

            /* Custom marker animations */
            .marker-pulse { animation: marker-pulse 2s ease-out infinite; }
            @keyframes marker-pulse {
                0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
                70%  { box-shadow: 0 0 0 16px rgba(239, 68, 68, 0); }
                100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }

            /* ─── Clamp Leaflet z-indexes so they never escape the map box ─── */
            .leaflet-container {
                z-index: 0 !important;
                isolation: isolate;
            }
            .leaflet-pane          { z-index: 1 !important; }
            .leaflet-tile-pane     { z-index: 100 !important; }
            .leaflet-overlay-pane  { z-index: 200 !important; }
            .leaflet-marker-pane   { z-index: 300 !important; }
            .leaflet-popup-pane    { z-index: 400 !important; }
            .leaflet-top,
            .leaflet-bottom        { z-index: 500 !important; }
            .leaflet-control       { z-index: 500 !important; }
        </style>
        @livewireStyles
    </head>

    <body class="min-h-screen bg-slate-100 text-slate-800 antialiased dark:bg-gray-950 dark:text-gray-100">

        <div class="flex min-h-screen flex-col lg:flex-row">

            @include('layouts.shop_owner.navbar')

            <div class="flex min-h-screen min-w-0 flex-1 flex-col">

                @include('layouts.shop_owner.header')

                <main class="flex-1 overflow-x-hidden px-4 py-6 sm:px-6 lg:p-6">
                    <div class="mx-auto w-full max-w-7xl">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts

        <script>
            (function () {
                const sidebar  = document.getElementById('ownerSidebar');
                const overlay  = document.getElementById('ownerSidebarOverlay');
                const openBtn  = document.getElementById('ownerSidebarToggle');
                const closeBtn = document.getElementById('ownerSidebarClose');

                if (! sidebar || ! overlay) return;

                function openSidebar() {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function closeSidebar() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }

                openBtn?.addEventListener('click', openSidebar);
                closeBtn?.addEventListener('click', closeSidebar);
                overlay?.addEventListener('click', closeSidebar);

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeSidebar();
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.add('hidden');
                        document.body.style.overflow = '';
                    } else {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            })();
        </script>
    </body>
</html>