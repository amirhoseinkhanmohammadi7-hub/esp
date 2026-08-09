<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>espira - مسیر پیشرفت تو</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden font-body">
    <div class="fixed inset-0 -z-10 bg-slate-950">
        <div class="absolute top-0 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob"></div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 2s"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-pink-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 4s"></div>
    </div>

    <div class="text-center px-4 max-w-2xl">
        <h1 class="font-logo text-7xl md:text-8xl text-white mb-4 tracking-wider">espira</h1>
        <p class="text-white/60 text-sm mb-2 font-quote">مسیر پیشرفت تو، با هوشمندی</p>
        <p class="text-white/40 text-xs mb-8 max-w-md mx-auto">عادت‌های کوچک، تغییرات بزرگ. با قانون دو حالته و حمایت دوستان.</p>
        <div class="flex gap-3 justify-center">
            <a href="{{ route('register') }}" class="btn-primary">شروع کن</a>
            <a href="{{ route('login') }}" class="btn-secondary">ورود</a>
        </div>
        <div class="mt-12 text-white/20 text-[10px] font-logo tracking-widest">espira.top</div>
    </div>
</body>
</html>
