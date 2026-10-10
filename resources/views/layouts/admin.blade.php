<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

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

        @livewireStyles
    </head>

    <body class="min-h-screen bg-slate-100 text-slate-800 antialiased dark:bg-gray-950 dark:text-gray-100">

        <div class="flex min-h-screen flex-col lg:flex-row">

            @include('layouts.admin.navbar')

            <div class="flex min-h-screen min-w-0 flex-1 flex-col">

                @include('layouts.admin.header')

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
                const sidebar  = document.getElementById('adminSidebar');
                const overlay  = document.getElementById('adminSidebarOverlay');
                const openBtn  = document.getElementById('adminSidebarToggle');
                const closeBtn = document.getElementById('adminSidebarClose');

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
                    }
                });
            })();
        </script>
    </body>
</html>