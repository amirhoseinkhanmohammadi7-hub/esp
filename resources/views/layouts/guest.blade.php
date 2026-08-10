<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .auth-input {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }
            .auth-input:focus {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(168, 85, 247, 0.5);
                box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
            }
            .auth-input::placeholder {
                color: rgba(255, 255, 255, 0.4);
            }
            .auth-btn-gradient {
                background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
                transition: all 0.3s ease;
            }
            .auth-btn-gradient:hover {
                background: linear-gradient(135deg, #c084fc 0%, #f472b6 100%);
                transform: translateY(-2px);
                box-shadow: 0 10px 40px rgba(168, 85, 247, 0.4);
            }
            .auth-link {
                color: rgba(255, 255, 255, 0.7);
                transition: all 0.2s ease;
            }
            .auth-link:hover {
                color: #a855f7;
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen relative overflow-x-hidden font-body">
        <div class="fixed inset-0 -z-10 bg-slate-950">
            <div class="absolute top-0 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob"></div>
            <div class="absolute top-0 -right-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 2s"></div>
            <div class="absolute -bottom-40 left-20 w-96 h-96 bg-pink-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 4s"></div>
        </div>
        
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 relative z-10">
            <div class="mb-6 text-center">
                <a href="/" class="inline-block">
                    <h1 class="text-3xl font-bold text-white tracking-tight">{{ config('app.name', 'Habit Tracker') }}</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 glass-card rounded-2xl border border-white/10">
                {{ $slot }}
            </div>
            
            <p class="mt-8 text-sm text-white/40">&copy; {{ date('Y') }} {{ config('app.name', 'Habit Tracker') }}. تمام حقوق محفوظ است.</p>
        </div>
    </body>
</html>
