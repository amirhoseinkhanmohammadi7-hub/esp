<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .auth-gradient-bg {
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            }
            .auth-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }
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
            .floating-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(60px);
                opacity: 0.3;
                animation: float 6s ease-in-out infinite;
            }
            .shape-1 {
                width: 300px;
                height: 300px;
                background: #a855f7;
                top: -100px;
                left: -100px;
                animation-delay: 0s;
            }
            .shape-2 {
                width: 400px;
                height: 400px;
                background: #ec4899;
                bottom: -150px;
                right: -150px;
                animation-delay: 2s;
            }
            .shape-3 {
                width: 200px;
                height: 200px;
                background: #06b6d4;
                top: 50%;
                right: -80px;
                animation-delay: 4s;
            }
            @keyframes float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(20px, -20px) scale(1.05); }
            }
        </style>
    </head>
    <body class="font-sans antialiased auth-gradient-bg min-h-screen relative overflow-hidden">
        <!-- Floating Background Shapes -->
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 relative z-10">
            <div class="mb-6 text-center">
                <a href="/" class="inline-block">
                    <h1 class="text-3xl font-bold text-white tracking-tight">{{ config('app.name', 'Habit Tracker') }}</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 auth-card rounded-2xl">
                {{ $slot }}
            </div>
            
            <p class="mt-8 text-sm text-white/40">&copy; {{ date('Y') }} {{ config('app.name', 'Habit Tracker') }}. All rights reserved.</p>
        </div>
    </body>
</html>
