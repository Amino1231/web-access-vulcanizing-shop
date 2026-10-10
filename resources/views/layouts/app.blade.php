<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Leaflet — free maps, no API key --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        /* Leaflet dark mode */
        .dark .leaflet-tile {
            filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
        }

        .dark .leaflet-container {
            background: #0f172a;
        }

        .dark .leaflet-popup-content-wrapper,
        .dark .leaflet-popup-tip {
            background: #1e293b;
            color: #f1f5f9;
        }

        .dark .leaflet-popup-content a {
            color: #ff5e14;
        }

        /* Custom marker animations */
        .marker-pulse {
            animation: marker-pulse 2s ease-out infinite;
        }

        @keyframes marker-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6);
            }

            70% {
                box-shadow: 0 0 0 16px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* ─── Clamp Leaflet z-indexes so they never escape the map box ─── */
        .leaflet-container {
            z-index: 0 !important;
            isolation: isolate;
        }

        .leaflet-pane {
            z-index: 1 !important;
        }

        .leaflet-tile-pane {
            z-index: 100 !important;
        }

        .leaflet-overlay-pane {
            z-index: 200 !important;
        }

        .leaflet-marker-pane {
            z-index: 300 !important;
        }

        .leaflet-popup-pane {
            z-index: 400 !important;
        }

        .leaflet-top,
        .leaflet-bottom {
            z-index: 500 !important;
        }

        .leaflet-control {
            z-index: 500 !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body>


    @include('layouts.public.header')
    <main>
        {{ $slot }}
    </main>

    @include('layouts.public.footer')
    @livewireScripts
</body>

</html>