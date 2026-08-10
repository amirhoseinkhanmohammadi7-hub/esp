@extends('layouts.app')
@section('title', 'عادت‌های من')
@section('content')
<div class="space-y-6">
    <!-- جمله انگیزشی -->
    @php
        $quotes = [
            ['text' => 'رویاهای خود را زندگی نکنید، زندگی خود را رویایی کنید.', 'author' => 'نیکولا تسلا'],
            ['text' => 'نبغه یک درصد الهام و نود و نه درصد تلاش است.', 'author' => 'توماس ادیسون'],
            ['text' => 'خیال‌پردازی مهم‌تر از دانش است.', 'author' => 'آلبرت انیشتین'],
            ['text' => 'شروع کردن سخت‌ترین بخش کار است، بقیه فقط پایداری می‌خواهد.', 'author' => 'افلاطون'],
            ['text' => 'موفقیت یعنی رفتن از شکستی به شکست دیگر بدون از دست دادن اشتیاق.', 'author' => 'وینستون چرچیل'],
            ['text' => 'تنها راه انجام کار بزرگ، عشق ورزیدن به کاری است که انجام می‌دهید.', 'author' => 'استیو جابز'],
            ['text' => 'اگر می‌خواهید پرواز کنید، باید چیزهایی که سنگینی می‌کنند را رها کنید.', 'author' => 'تونیا موریسون'],
            ['text' => 'آینده متعلق به کسانی است که زیبایی رویاهایشان را باور دارند.', 'author' => 'النور روزولت'],
            ['text' => 'محدودیت‌ها فقط در ذهن وجود دارند.', 'author' => 'جیمی پائولتی'],
            ['text' => 'هر روز یک فرصت جدید برای تغییر زندگی شماست.', 'author' => 'ناشناخته'],
            ['text' => 'پیروزی نهایی نیست، شکست کشنده نیست: این شجاعت ادامه دادن است که اهمیت دارد.', 'author' => 'وینستون چرچیل'],
            ['text' => 'بهترین زمان برای کاشتن درخت بیست سال پیش بود. دومین زمان مناسب حالا است.', 'author' => 'ضرب‌المثل چینی'],
            ['text' => 'شما نمی‌توانید جهت باد را تغییر دهید، اما می‌توانید بادبان‌هایتان را تنظیم کنید.', 'author' => 'جیمی دین'],
            ['text' => 'تنها کسی که باید سعی کنید بهتر از او باشید، کسی است که دیروز بودید.', 'author' => 'ناشناخته'],
            ['text' => 'موفقیت مجموعه‌ای از تلاش‌های کوچک است که هر روز تکرار می‌شوند.', 'author' => 'رابرت کالیر'],
            ['text' => 'اگر نمی‌توانید پرواز کنید، بدوید. اگر نمی‌توانید بدوید، راه بروید. اگر نمی‌توانید راه بروید، بخزید. اما هر کاری می‌کنید، به حرکت ادامه دهید.', 'author' => 'مارتین لوتر کینگ'],
            ['text' => 'زندگی ۱۰٪ آنچه برای شما اتفاق می‌افتد و ۹۰٪ نحوه واکنش شما به آن است.', 'author' => 'چارلز سویندول'],
            ['text' => 'قدرتمندترین جنگجو زمان و صبر است.', 'author' => 'لئو تولستوی'],
            ['text' => 'آنچه را که می‌توانی، با آنچه داری، در جایی که هستی انجام بده.', 'author' => 'تئودور روزولت'],
            ['text' => 'موفقیت معمولاً برای کسانی که خیلی مشغول جستجوی آن هستند اتفاق می‌افتد.', 'author' => 'هنری دیوید ثورو'],
            ['text' => 'زندگی یا جسورانه است یا هیچ.', 'author' => 'هلن کلر'],
            ['text' => 'شجاعت این نیست که نترسی، بلکه این است که با ترس بجنگی.', 'author' => 'نلسون ماندلا'],
            ['text' => 'بزرگ‌ترین افتخار ما در این نیست که هرگز زمین نخوریم، بلکه در این است که هر بار بلند شویم.', 'author' => 'کنفوسیوس'],
            ['text' => 'اگر می‌خواهی جهان را تغییر دهی، اول خودت را تغییر بده.', 'author' => 'مهاتما گاندی'],
            ['text' => 'هیچ چیز غیرممکن نیست، خود کلمه می‌گوید من ممکن هستم!', 'author' => 'آدری هپبورن'],
            ['text' => 'راز پیشرفت، شروع کردن است.', 'author' => 'مارک تواین'],
            ['text' => 'تو نویسنده داستان زندگی خودت هستی.', 'author' => 'ناشناس'],
            ['text' => 'بهترین انتقام، موفقیت بزرگ است.', 'author' => 'فرانک سیناترا'],
            ['text' => 'اگر می‌توانی رویا ببافی، می‌توانی آن را محقق کنی.', 'author' => 'والت دیزنی'],
            ['text' => 'عادت‌های کوچک، نتایج بزرگ می‌سازند.', 'author' => 'جیمز کلیر'],
            ['text' => 'هر عادت کوچک، سرمایه‌گذاری برای آینده توست.', 'author' => 'ناشناس'],
            ['text' => 'دنیا به کسانی که حرکت می‌کنند پاداش می‌دهد.', 'author' => 'مارک تواین'],
            ['text' => 'باور داشته باش که می‌توانی، آنگاه نیمی از راه را رفته‌ای.', 'author' => 'تئودور روزولت'],
            ['text' => 'هر سفر هزار مایلی با یک قدم شروع می‌شود.', 'author' => 'لائو تزو'],
            ['text' => 'تغییر را دوست داشته باش، از آن نترس.', 'author' => 'تونی رابینز'],
            ['text' => 'رویاهایت را زندگی کن، نه زندگی دیگران.', 'author' => 'اپرا وینفری'],
            ['text' => 'خودت باش، همه نقش‌های دیگر گرفته شده‌اند.', 'author' => 'اسکار وایلد'],
            ['text' => 'صبر کلید موفقیت است.', 'author' => 'بلز پاسکال'],
            ['text' => 'امروز همان دیروز است که منتظرش بودی.', 'author' => 'ناشناس'],
            ['text' => 'کوچک فکر نکن، بزرگ عمل کن.', 'author' => 'ناشناس'],
        ];
        $randomQuote = $quotes[array_rand($quotes)];
    @endphp
    <div class="glass-card p-4 border-r-4 border-purple-500">
        <p class="text-sm text-white/80 italic">«{{ $randomQuote['text'] }}»</p>
        <p class="text-xs text-white/50 mt-2 text-left">— {{ $randomQuote['author'] }}</p>
    </div>
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-heading bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 bg-clip-text text-transparent">مسیر پیشرفت تو</h1>
            <p class="text-white/50 mt-1 text-sm">هر روز یک قدم کوچک</p>
        </div>
        <a href="{{ route('habits.create') }}" class="btn-primary">+ عادت جدید</a>
    </div>
    
    @if($habits->isEmpty())
        <div class="glass-card p-10 text-center">
            <div class="flex justify-center mb-4">
                <img src="{{ auth()->user()->profile_picture_url }}" alt="عکس پروفایل" class="w-24 h-24 rounded-full object-cover border-4 border-purple-500/30">
            </div>
            <h2 class="text-lg font-heading mb-2">هنوز عادتی نساختی!</h2>
            <p class="text-white/50 text-sm mb-5">اولین قدم همیشه سخت‌ترین قدمه.</p>
            <a href="{{ route('habits.create') }}" class="btn-primary">ساختن اولین عادت</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($habits as $habit)
                <a href="{{ route('habits.show', $habit) }}" class="glass-card p-5 group hover:scale-[1.02] block">
                    <div class="flex items-start justify-between mb-3">
                        <div class="text-3xl group-hover:scale-110 transition-transform">{{ $habit->emoji }}</div>
                        <div class="text-left">
                            <div class="text-2xl font-heading streak-glow">{{ $habit->getCurrentStreak() }}</div>
                            <div class="text-[10px] text-white/50">روز استریک 🔥</div>
                        </div>
                    </div>
                    <h3 class="text-sm font-heading mb-1">{{ $habit->title }}</h3>
                    @if($habit->description)
                        <p class="text-white/50 text-xs mb-3 line-clamp-2">{{ $habit->description }}</p>
                    @endif
                    <div class="flex items-center gap-3 text-[10px] text-white/50 pt-3 border-t border-white/10">
                        <span>💪 {{ $habit->getFullCount() }}</span>
                        <span>⚡ {{ $habit->getMicroCount() }}</span>
                        <span>✍️ {{ $habit->signatures->count() }}</span>
                    </div>
                    @if($habit->is_completed)
                        <div class="mt-3 text-center py-1.5 bg-yellow-500/20 border border-yellow-500/30 rounded-lg text-yellow-300 text-[10px] font-bold">🏆 تکمیل شده</div>
                    @elseif($habit->hasLoggedToday())
                        <div class="mt-3 text-center py-1.5 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-[10px] font-bold">✓ امروز انجام دادی</div>
                    @else
                        <div class="mt-3 text-center py-1.5 bg-amber-500/20 border border-amber-500/30 rounded-lg text-amber-300 text-[10px] font-bold">⏰ امروز ثبت نشده</div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
