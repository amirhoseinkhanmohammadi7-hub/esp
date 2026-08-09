<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="{{ $habit->user->name }} در مسیر {{ $habit->title }}">
    <meta property="og:description" content="🔥 {{ $habit->getCurrentStreak() }} روز استریک">
    <title>{{ $habit->title }} - espira</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&family=Estedad:wght@300;400;500;600;700&family=Dana:wght@300;400;500;600;700&family=Shabnam:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body antialiased min-h-screen">
    <div class="fixed inset-0 -z-10 bg-slate-950">
        <div class="absolute top-0 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob"></div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 2s"></div>
    </div>

    <div class="min-h-screen py-6 px-4 max-w-md mx-auto">
        <!-- کارت استوری -->
        <div id="story-card" class="glass-card p-6 aspect-[9/16] max-h-[85vh] overflow-hidden flex flex-col relative">
            <div class="absolute top-3 left-3 font-logo text-white/30 text-xs tracking-wider">espira.top</div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-heading shadow-lg">
                        {{ mb_substr($habit->user->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-heading text-sm">{{ $habit->user->name }}</div>
                        <div class="text-[10px] text-white/50">در مسیر پیشرفت</div>
                    </div>
                </div>
                <div class="text-4xl">{{ $habit->emoji }}</div>
            </div>

            <h1 class="text-xl font-heading mb-2 leading-tight">{{ $habit->title }}</h1>
            @if($habit->description)
                <p class="text-white/60 mb-6 text-xs leading-relaxed font-quote">{{ $habit->description }}</p>
            @endif

            <!-- استریک -->
            <div class="glass-card p-5 mb-4 bg-gradient-to-br from-orange-500/20 to-yellow-500/20 border-orange-500/40 text-center">
                <div class="text-6xl font-heading streak-glow leading-none">{{ $habit->getCurrentStreak() }}</div>
                <div class="text-sm mt-2 text-white/80 font-heading">روز استریک متوالی 🔥</div>
            </div>

            <!-- آمار -->
            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="glass-card p-3 text-center">
                    <div class="text-xl font-heading text-emerald-400">{{ $habit->logs_count }}</div>
                    <div class="text-[9px] text-white/50 mt-0.5">کل روزها</div>
                </div>
                <div class="glass-card p-3 text-center">
                    <div class="text-xl font-heading text-cyan-400">{{ $habit->getMicroCount() }}</div>
                    <div class="text-[9px] text-white/50 mt-0.5">میکرو</div>
                </div>
                <div class="glass-card p-3 text-center">
                    <div class="text-xl font-heading text-pink-400">{{ $habit->signatures_count }}</div>
                    <div class="text-[9px] text-white/50 mt-0.5">حامی</div>
                </div>
            </div>

            <div class="mt-auto glass-card p-4 bg-white/5 border-white/10">
                <p class="text-xs text-white/70 leading-relaxed font-quote">
                    💫 <strong class="text-yellow-300 font-heading">قانون من:</strong> یک روز استراحت مجازه، اما دو روز پشت سر هم از دست نمی‌دم.
                    <br>هر روز یا ۲ دقیقه یا کامل. مهم <strong class="text-white font-heading">حضور</strong> داشتنه.
                </p>
            </div>
        </div>

        <!-- دکمه‌ها -->
        <div class="mt-4 space-y-2">
            <button onclick="downloadAsImage()" class="btn-primary w-full text-sm py-3">📸 دانلود عکس برای استوری</button>
            <button onclick="copyLink()" class="btn-secondary w-full text-sm py-2.5">🔗 کپی کردن لینک</button>
        </div>

        <!-- بخش امضا -->
        <div class="glass-card p-5 mt-6">
            <h2 class="text-sm font-heading mb-1">✍️ امضای حمایت</h2>
            <p class="text-white/50 text-xs mb-4">
                @auth
                    اگه این مسیر رو تحسین می‌کنی، امضا کن.
                @else
                    برای امضا باید <a href="{{ route('login') }}" class="text-cyan-300 underline">وارد شوید</a>.
                @endauth
            </p>

            @if(session('success'))
                <div class="mb-3 p-2 glass-card border-emerald-500/50 text-emerald-300 text-xs">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-3 p-2 glass-card border-red-500/50 text-red-300 text-xs">{{ session('error') }}</div>
            @endif

            @auth
                @if(!$habit->signatures()->where('user_id', auth()->id())->exists())
                    <form action="{{ route('signatures.store', $habit->share_token) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="message" class="glass-input" rows="2" placeholder="پیام تشویقی (اختیاری)">{{ old('message') }}</textarea>
                        <button type="submit" class="btn-primary w-full text-xs">امضا می‌کنم ✨</button>
                    </form>
                @else
                    <div class="text-center py-3 text-xs text-white/60">💫 شما قبلاً این مسیر را حمایت کرده‌اید</div>
                @endif
            @endauth

            @if($habit->signatures_count > 0)
                <div class="mt-5 pt-4 border-t border-white/10">
                    <h3 class="font-heading text-xs mb-3 text-white/70">{{ $habit->signatures_count }} نفر امضا کردن:</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($habit->signatures as $sig)
                            <div class="glass-card p-3 text-xs">
                                <div class="font-heading text-cyan-300">{{ $sig->name }}</div>
                                @if($sig->message)
                                    <div class="text-white/60 mt-1 font-quote">"{{ $sig->message }}"</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        async function downloadAsImage() {
            const card = document.getElementById('story-card');
            const btn = event.target;
            btn.innerText = '⏳ در حال ساخت عکس...';
            const canvas = await html2canvas(card, { scale: 3, backgroundColor: '#020617', useCORS: true, logging: false });
            const link = document.createElement('a');
            link.download = 'espira-streak-{{ $habit->getCurrentStreak() }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            btn.innerText = '📸 دانلود عکس برای استوری';
        }
        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            const btn = event.target;
            btn.innerText = '✅ لینک کپی شد!';
            setTimeout(() => btn.innerText = '🔗 کپی کردن لینک', 2000);
        }
    </script>
</body>
</html>
