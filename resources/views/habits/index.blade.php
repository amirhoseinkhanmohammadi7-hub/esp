@extends('layouts.app')
@section('title', 'عادت‌های من')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-heading bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 bg-clip-text text-transparent">مسیر پیشرفت تو</h1>
            <p class="text-white/50 mt-1 text-sm">هر روز یک قدم کوچک</p>
        </div>
        <a href="{{ route('habits.create') }}" class="btn-primary">+ عادت جدید</a>
    </div>

    @if($habits->isEmpty())
        <div class="glass-card p-10 text-center">
            <div class="text-5xl mb-3">🌱</div>
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
