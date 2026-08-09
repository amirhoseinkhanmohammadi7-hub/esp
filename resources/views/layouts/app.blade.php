<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>espira - @yield('title', 'مسیر پیشرفت')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen relative overflow-x-hidden font-body">
    <div class="fixed inset-0 -z-10 bg-slate-950">
        <div class="absolute top-0 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob"></div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 2s"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-pink-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 4s"></div>
    </div>

    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')
        <main class="flex-grow py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
            @if (session('success'))
                <div class="glass-card mb-4 p-3 border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-2">
                    <span>✨</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="glass-card mb-4 p-3 border-red-500/30 text-red-300 text-sm flex items-center gap-2">
                    <span>⚠️</span> {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
        <footer class="text-center py-6 text-white/30 text-xs">
            <span class="font-logo text-sm">espira.top</span> — مسیر پیشرفت تو
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
