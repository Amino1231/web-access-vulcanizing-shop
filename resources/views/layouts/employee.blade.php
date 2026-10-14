<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'VulcanPro — Employee Portal' }}</title>

    {{-- Prevent flash of wrong theme --}}
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    {{-- Leaflet — free maps --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        .dark .leaflet-tile { filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7); }
        .dark .leaflet-container { background: #0f172a; }
        .dark .leaflet-popup-content-wrapper,
        .dark .leaflet-popup-tip { background: #1e293b; color: #f1f5f9; }
        .dark .leaflet-popup-content a { color: #ff5e14; }

        .marker-pulse { animation: marker-pulse 2s ease-out infinite; }
        @keyframes marker-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
            70%  { box-shadow: 0 0 0 16px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .leaflet-container { z-index: 0 !important; isolation: isolate; }
        .leaflet-pane          { z-index: 1 !important; }
        .leaflet-tile-pane     { z-index: 100 !important; }
        .leaflet-overlay-pane  { z-index: 200 !important; }
        .leaflet-marker-pane   { z-index: 300 !important; }
        .leaflet-popup-pane    { z-index: 400 !important; }
        .leaflet-top,
        .leaflet-bottom        { z-index: 500 !important; }
        .leaflet-control       { z-index: 500 !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-50 dark:bg-gray-950 antialiased text-slate-900 dark:text-slate-100">
    <div class="flex min-h-full flex-col">

        {{-- HEADER --}}
        @include('layouts.employee.header')

        {{-- PAGE CONTENT --}}
        <main class="flex-1">
            <div class="p-4 lg:p-8">
                {{ $slot }}
            </div>
        </main>

        {{-- FOOTER --}}
        @include('layouts.employee.footer')
    </div>

    {{-- Theme toggle helper --}}
    <script>
        window.toggleTheme = function () {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };
    </script>

    @livewireScripts

    {{-- Pusher + Echo for real-time notifications --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ config('broadcasting.connections.pusher.key') }}',
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            forceTLS: true,
        });

        document.addEventListener('DOMContentLoaded', function () {
            const userId = {{ auth()->id() ?? 'null' }};
            if (! userId || ! window.Echo) {
                console.warn('[Notif] Not authenticated or Echo missing');
                return;
            }

            console.log('[Notif] Subscribing to: App.Models.User.' + userId);

            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log('[Notif] Received:', notification);

                    // 1. Refresh the bell
                    window.dispatchEvent(new CustomEvent('livewire-notification', {
                        detail: notification
                    }));

                    // 2. Show toast for employee-assigned jobs
                    if (notification.kind && notification.kind.startsWith('employee.')) {
                        window.dispatchEvent(new CustomEvent('notification-toast', {
                            detail: {
                                title: notification.title || 'New notification',
                                message: notification.message || '',
                                urgency: notification.urgency || 'normal',
                            }
                        }));

                        // 3. Beep for urgent
                        if (notification.urgency === 'urgent') {
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.frequency.value = 880;
                                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                                osc.start(ctx.currentTime);
                                osc.stop(ctx.currentTime + 0.3);
                            } catch (e) { /* ignore */ }
                        }
                    }
                });
        });
    </script>
</body>
</html>