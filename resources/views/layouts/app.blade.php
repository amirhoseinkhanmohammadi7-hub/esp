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
        
        <!-- نوتیفیکیشن یادآوری ثبت خواب -->
        @auth
        <div x-data="sleepReminder()" x-init="init()" class="fixed top-20 left-4 z-40 transition-all duration-500"
             x-show="showNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-10"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-10"
             x-cloak>
            <a :href="sleepRoute" 
               class="glass-card p-4 max-w-xs cursor-pointer hover:bg-white/10 transition-all border-l-4 border-indigo-400 shadow-2xl"
               @click="dismiss()">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">🌙</div>
                    <div class="flex-1">
                        <p class="text-sm font-heading text-white/90 mb-1">ثبت خواب دیشب</p>
                        <p class="text-xs text-white/60">ساعت خواب و بیداریت رو ثبت کن تا بتونی روندت رو دنبال کنی.</p>
                    </div>
                    <button @click.stop="dismiss()" class="text-white/30 hover:text-white transition text-lg">×</button>
                </div>
            </a>
        </div>
        @endauth
        
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
    
    <!-- اسکریپت یادآوری ثبت خواب -->
    @auth
    <script>
    function sleepReminder() {
        return {
            showNotification: false,
            sleepRoute: '',
            
            async init() {
                // بررسی می‌کنیم که آیا کاربر قبلاً نوتیفیکیشن را بسته است
                const dismissed = localStorage.getItem('sleepReminderDismissed');
                const dismissedDate = dismissed ? parseInt(dismissed) : 0;
                const today = new Date().setHours(0, 0, 0, 0);
                
                // اگر امروز قبلاً بسته شده، نشان نده
                if (dismissedDate >= today) {
                    return;
                }
                
                try {
                    const response = await fetch('/api/sleep-check', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.should_remind) {
                            this.sleepRoute = data.route;
                            this.showNotification = true;
                        }
                    }
                } catch (error) {
                    console.error('Error checking sleep reminder:', error);
                }
            },
            
            dismiss() {
                this.showNotification = false;
                // ذخیره تاریخ امروز برای اینکه دیگر امروز نشان داده نشود
                localStorage.setItem('sleepReminderDismissed', new Date().setHours(0, 0, 0, 0));
            }
        }
    }
    </script>
    @endauth
    
    @stack('scripts')
</body>
</html>
