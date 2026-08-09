@extends('layouts.app')
@section('title', 'مدیریت پیام‌ها')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- هدر -->
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-heading"> مدیریت پیام‌ها</h1>
            <a href="{{ route('profile.edit') }}" class="btn-secondary text-xs">بازگشت به پروفایل</a>
        </div>
        <p class="text-sm text-white/60">پیام‌های دریافتی از صفحه عمومی چارت شما</p>
    </div>

    @if(session('success'))
        <div class="glass-card p-3 border-emerald-500/30 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- پیام‌های در انتظار تایید -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-4 flex items-center gap-2">
            <span class="text-yellow-400">⏳</span>
            پیام‌های در انتظار تایید ({{ $pendingMessages->count() }})
        </h2>
        
        @if($pendingMessages->isEmpty())
            <p class="text-white/50 text-sm text-center py-6">هیچ پیام در انتظار تایید نیست</p>
        @else
            <div class="space-y-3">
                @foreach($pendingMessages as $message)
                    <div class="glass-card p-4 bg-yellow-500/5 border-yellow-500/20">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-grow">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-heading text-sm text-cyan-300">{{ $message->sender_name }}</span>
                                    <span class="text-xs text-white/40">{{ $message->created_at->format('Y/m/d H:i') }}</span>
                                </div>
                                <p class="text-sm text-white/80 font-quote">"{{ $message->message }}"</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('messages.approve', $message) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-full text-xs px-4 py-2">
                                        ✅ تایید
                                    </button>
                                </form>
                                <form action="{{ route('messages.delete', $message) }}" method="POST" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary text-xs px-4 py-2 text-red-300 border-red-500/30 hover:bg-red-500/20">
                                        ️ حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- پیام‌های تایید شده -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-4 flex items-center gap-2">
            <span class="text-emerald-400">✅</span>
            پیام‌های تایید شده ({{ $approvedMessages->count() }})
        </h2>
        
        @if($approvedMessages->isEmpty())
            <p class="text-white/50 text-sm text-center py-6">هنوز پیامی تایید نشده است</p>
        @else
            <div class="space-y-3">
                @foreach($approvedMessages as $message)
                    <div class="glass-card p-4 bg-emerald-500/5 border-emerald-500/20">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-grow">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-heading text-sm text-cyan-300">{{ $message->sender_name }}</span>
                                    <span class="text-xs text-white/40">{{ $message->created_at->format('Y/m/d H:i') }}</span>
                                    <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded">تایید شده</span>
                                </div>
                                <p class="text-sm text-white/80 font-quote">"{{ $message->message }}"</p>
                            </div>
                            <div>
                                <form action="{{ route('messages.delete', $message) }}" method="POST" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary text-xs px-4 py-2 text-red-300 border-red-500/30 hover:bg-red-500/20">
                                        🗑️ حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
