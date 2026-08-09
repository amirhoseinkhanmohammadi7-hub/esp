@extends('layouts.app')
@section('title', $habit->title)
@section('content')
@php
$quotes = [
    ['text' => 'آینده متعلق به کسانی است که زیبایی رویاهایشان را باور دارند.', 'author' => 'النور روزولت'],
    ['text' => 'موفقیت نهایی نیست، شکست کشنده نیست: شجاعت ادامه دادن مهم است.', 'author' => 'وینستون چرچیل'],
    ['text' => 'خیال‌پردازی مهم‌تر از دانش است.', 'author' => 'آلبرت انیشتین'],
    ['text' => 'بهترین راه پیش‌بینی آینده، ساختن آن است.', 'author' => 'پیتر دراکر'],
    ['text' => 'اگر می‌خواهی به قله برسی، از دامنه شروع کن.', 'author' => 'سقراط'],
    ['text' => 'نبوغ یک درصد الهام و نود و نه درصد تلاش است.', 'author' => 'توماس ادیسون'],
    ['text' => 'دنیا به کسانی که حرکت می‌کنند پاداش می‌دهد.', 'author' => 'مارک تواین'],
    ['text' => 'تنها راه انجام کار بزرگ، این است که عاشق کارت باشی.', 'author' => 'استیو جابز'],
    ['text' => 'موفقیت یعنی رفتن از شکستی به شکست دیگر بدون از دست دادن اشتیاق.', 'author' => 'وینستون چرچیل'],
    ['text' => 'باور داشته باش که می‌توانی، آنگاه نیمی از راه را رفته‌ای.', 'author' => 'تئودور روزولت'],
    ['text' => 'آنچه امروز انجام می‌دهی، می‌تواند فردای تو را بسازد.', 'author' => 'ناشناس'],
    ['text' => 'شروع کردن سخت‌ترین قسمت است؛ بقیه فقط پشتکار است.', 'author' => 'سیسیل بی. دمیل'],
    ['text' => 'هر سفر هزار مایلی با یک قدم شروع می‌شود.', 'author' => 'لائو تزو'],
    ['text' => 'تغییر را دوست داشته باش، از آن نترس.', 'author' => 'تونی رابینز'],
    ['text' => 'محدودیت‌ها فقط در ذهن ما وجود دارند.', 'author' => 'جیمی پائولی'],
    ['text' => 'اگر نمی‌توانی پرواز کنی، بدو. اگر نمی‌توانی بدوی، راه برو.', 'author' => 'مایا آنجلو'],
    ['text' => 'رویاهایت را زندگی کن، نه زندگی دیگران.', 'author' => 'اپرا وینفری'],
    ['text' => 'پیروزی یعنی آماده بودن برای فرصت‌ها.', 'author' => 'بنجامین دیزرائیلی'],
    ['text' => 'شکست مقدمه پیروزی است.', 'author' => 'ارسطو'],
    ['text' => 'خودت باش، همه نقش‌های دیگر گرفته شده‌اند.', 'author' => 'اسکار وایلد'],
    ['text' => 'قدرت درونی تو از هر مانعی قوی‌تر است.', 'author' => 'ناشناس'],
    ['text' => 'امروز همان دیروز است که منتظرش بودی.', 'author' => 'ناشناس'],
    ['text' => 'کوچک فکر نکن، بزرگ عمل کن.', 'author' => 'ناشناس'],
    ['text' => 'صبر کلید موفقیت است.', 'author' => 'بلز پاسکال'],
    ['text' => 'هر لحظه فرصتی جدید برای تغییر زندگی توست.', 'author' => 'ناشناس'],
];
$randomQuote = $quotes[array_rand($quotes)];
@endphp
<div class="max-w-4xl mx-auto space-y-6">
    <!-- هدر -->
    <div class="glass-card p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="text-5xl">{{ $habit->emoji }}</div>
                <div>
                    <h1 class="text-xl font-heading mb-1">{{ $habit->title }}</h1>
                    @if($habit->description) <p class="text-white/50 text-sm">{{ $habit->description }}</p> @endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('habits.share', $habit->share_token) }}" target="_blank" class="btn-secondary text-xs">🔗 اشتراک گذاری زنده</a>
                <form action="{{ route('habits.destroy', $habit) }}" method="POST" onsubmit="return confirm('مطمئنی؟');">
                    @csrf @method('DELETE')
                    <button class="btn-secondary text-xs text-red-300 border-red-500/30 hover:bg-red-500/20">حذف</button>
                </form>
            </div>
        </div>
    </div>

    <!-- جمله انگیزشی روز -->
    <div class="glass-card p-6 bg-gradient-to-br from-amber-500/10 to-orange-500/10 border-amber-500/30">
        <div class="flex items-start gap-4">
            <div class="text-4xl">💡</div>
            <div>
                <p class="text-white/80 text-sm font-quote italic mb-2">"{{ $randomQuote['text'] }}"</p>
                <p class="text-white/50 text-xs">— {{ $randomQuote['author'] }}</p>
            </div>
        </div>
    </div>

    @if($habit->is_completed)
        <div class="glass-card p-6 bg-gradient-to-br from-yellow-500/10 to-orange-500/10 border-yellow-500/30">
            <div class="flex items-center gap-4">
                <div class="text-5xl">🏆</div>
                <div>
                    <h2 class="text-lg font-heading mb-1">این عادت تکمیل شد!</h2>
                    <p class="text-white/70 text-sm font-quote italic">"{{ $habit->completion_story }}"</p>
                    <p class="text-white/40 text-xs mt-2">تاریخ تکمیل: {{ $habit->completed_at->format('Y/m/d') }}</p>
                </div>
            </div>
        </div>
    @elseif(!$habit->hasLoggedToday())
        <div class="glass-card p-6 bg-gradient-to-br from-purple-500/10 to-pink-500/10 border-purple-500/30">
            <h2 class="text-lg font-heading mb-1"> امروز هنوز ثبت نکردی</h2>
            <p class="text-white/60 text-sm mb-4">یکی از دو حالت رو انتخاب کن:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <form action="{{ route('habits.log', [$habit, 'micro']) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-micro w-full text-right p-4">
                        <div class="text-2xl mb-1">⚡</div>
                        <div class="font-heading text-sm mb-0.5">حالت میکرو (۲ دقیقه)</div>
                        <div class="text-[10px] text-white/70">حفظ ارتباط در روزهای شلوغ</div>
                    </button>
                </form>
                <form action="{{ route('habits.log', [$habit, 'full']) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-full w-full text-right p-4">
                        <div class="text-2xl mb-1"></div>
                        <div class="font-heading text-sm mb-0.5">حالت کامل</div>
                        <div class="text-[10px] text-white/70">انجام کامل و باکیفیت</div>
                    </button>
                </form>
            </div>
        </div>
    @else
        @php $today = $habit->getTodayLog(); @endphp
        <div class="glass-card p-6 bg-gradient-to-br from-emerald-500/10 to-green-500/10 border-emerald-500/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="text-4xl">{{ $today->isMicro() ? '⚡' : '💪' }}</div>
                <div>
                    <h2 class="text-lg font-heading">امروز انجام دادی! </h2>
                    <p class="text-white/60 text-sm">حالت: <strong>{{ $today->isMicro() ? 'میکرو (۲ دقیقه)' : 'کامل' }}</strong></p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($today->isMicro())
                    <form action="{{ route('habits.log', [$habit, 'full']) }}" method="POST">
                        @csrf <button class="btn-full text-xs">⬆️ ارتقا به کامل</button>
                    </form>
                @else
                    <form action="{{ route('habits.log', [$habit, 'micro']) }}" method="POST">
                        @csrf <button class="btn-micro text-xs">تغییر به میکرو</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <!-- فرم تکمیل عادت -->
    @if(!$habit->is_completed)
        <div class="glass-card p-5 bg-gradient-to-br from-yellow-500/5 to-orange-500/5 border-yellow-500/20">
            <h3 class="font-heading text-sm mb-2 flex items-center gap-2"> تکمیل عادت</h3>
            <p class="text-xs text-white/60 mb-3">وقتی به هدفت رسیدی، اینجا ثبت کن تا داستان موفقیتت برای همیشه بمونه.</p>
            <form action="{{ route('habits.complete', $habit) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="story" class="glass-input" rows="2" placeholder="مثلاً: بعد از 45 روز تونستم 10 کیلو وزن کم کنم و به هدفم رسیدم!" required></textarea>
                <button type="submit" class="btn-primary text-xs">ثبت تکمیل عادت 🏆</button>
            </form>
        </div>
    @endif

    <!-- آمار -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="stat-badge">
            <div class="text-2xl font-heading streak-glow">{{ $habit->getCurrentStreak() }}</div>
            <div class="text-[10px] text-white/50 mt-1">استریک 🔥</div>
        </div>
        <div class="stat-badge">
            <div class="text-2xl font-heading text-emerald-400">{{ $habit->getFullCount() }}</div>
            <div class="text-[10px] text-white/50 mt-1">کامل</div>
        </div>
        <div class="stat-badge">
            <div class="text-2xl font-heading text-cyan-400">{{ $habit->getMicroCount() }}</div>
            <div class="text-[10px] text-white/50 mt-1">میکرو</div>
        </div>
        <div class="stat-badge">
            <div class="text-2xl font-heading text-pink-400">{{ $habit->signatures->count() }}</div>
            <div class="text-[10px] text-white/50 mt-1">حامی ✍️</div>
        </div>
        <div class="stat-badge">
            <div class="text-2xl font-heading text-purple-400">{{ $habit->getSuccessRate() }}%</div>
            <div class="text-[10px] text-white/50 mt-1">موفقیت ۳۰ روزه</div>
        </div>
    </div>

    <!-- گرید ۹۰ روزه -->
    <div class="glass-card p-5">
        <h2 class="text-sm font-heading mb-4">📅 ۹۰ روز اخیر</h2>
        @php $logsByDate = $habit->logs->keyBy(fn($l) => $l->log_date->format('Y-m-d')); @endphp
        <div class="grid grid-cols-10 sm:grid-cols-15 gap-1.5">
            @for($i = 89; $i >= 0; $i--)
                @php
                    $date = now()->subDays($i)->format('Y-m-d');
                    $log = $logsByDate[$date] ?? null;
                @endphp
                <div class="aspect-square rounded-lg flex items-center justify-center text-[10px] transition-all hover:scale-110 cursor-default
                    @if($log && !$log->isMicro() && $log->type !== 'missed') bg-emerald-500 shadow-lg shadow-emerald-500/40
                    @elseif($log && $log->isMicro()) bg-cyan-500 shadow-lg shadow-cyan-500/40
                    @elseif($log && $log->type === 'missed') bg-red-500/30
                    @else bg-white/5 @endif" 
                    title="{{ $date }}: {{ $log ? ($log->type === 'full' ? 'کامل' : ($log->type === 'micro' ? 'میکرو' : 'انجام نشده')) : 'انجام نشده' }}">
                    @if($log && !$log->isMicro() && $log->type !== 'missed') 💪 @elseif($log && $log->isMicro()) ⚡ @elseif($log && $log->type === 'missed') ✗ @endif
                </div>
            @endfor
        </div>
        <div class="flex items-center gap-4 mt-4 text-[10px] text-white/50 font-medium">
            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-emerald-500 rounded"></div>کامل</div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-cyan-500 rounded"></div>میکرو</div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-red-500/30 rounded"></div>انجام نشده</div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-white/10 rounded"></div>خالی</div>
        </div>
    </div>

    <!-- حامیان -->
    <div class="glass-card p-5">
        <h2 class="text-sm font-heading mb-3">✍️ حامیان مسیر تو ({{ $habit->signatures->count() }} نفر)</h2>
        @if($habit->signatures->isEmpty())
            <p class="text-white/50 text-xs text-center py-4">هنوز کسی امضا نکرده. لینکت رو به اشتراک بگذار!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($habit->signatures as $sig)
                    <div class="glass-card p-3">
                        <div class="text-xs font-heading text-cyan-300">{{ $sig->name }}</div>
                        @if($sig->message)
                            <p class="text-[10px] text-white/60 mt-1 font-quote">"{{ $sig->message }}"</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
