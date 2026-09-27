<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'VulcanPro — Marketplace' }}</title>

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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white dark:bg-gray-950 antialiased text-slate-900 dark:text-slate-100">

    {{-- HEADER --}}
    @include('layouts.customer.header')

    {{-- PAGE CONTENT --}}
    <main class="min-h-[60vh]">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    @include('layouts.customer.footer')

    {{-- Theme toggle script --}}
    <script>
        window.toggleTheme = function () {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };
    </script>
</body>
</html>