@extends('layouts.app')
@section('title', 'مدیریت پیام‌ها')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="glass-card p-6">
        <h1 class="text-xl font-heading mb-6">📬 مدیریت پیام‌ها</h1>
        
        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- پیام‌های در انتظار تایید -->
        <div class="mb-8">
            <h2 class="text-lg font-heading mb-3">⏳ پیام‌های در انتظار تایید ({{ $pendingMessages->count() }})</h2>
            @if($pendingMessages->isEmpty())
                <p class="text-white/50 text-sm text-center py-4">هیچ پیامی در انتظار نیست</p>
            @else
                <div class="space-y-3">
                    @foreach($pendingMessages as $message)
                        <div class="glass-card p-4">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <span class="text-xs text-cyan-300">{{ e($message->sender_name) }}</span>
                                    <span class="text-[10px] text-white/40 mr-2">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-white/80 font-quote mb-3">"{{ e($message->message) }}"</p>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('messages.approve', $message) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 px-3 py-1.5 rounded-lg text-xs transition-colors">✅ تایید</button>
                                </form>
                                <form method="POST" action="{{ route('messages.delete', $message) }}" class="inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1.5 rounded-lg text-xs transition-colors">❌ حذف</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- پیام‌های تایید شده -->
        <div>
            <h2 class="text-lg font-heading mb-3">✅ پیام‌های تایید شده ({{ $approvedMessages->count() }})</h2>
            @if($approvedMessages->isEmpty())
                <p class="text-white/50 text-sm text-center py-4">هنوز پیامی تایید نشده</p>
            @else
                <div class="space-y-3">
                    @foreach($approvedMessages as $message)
                        <div class="glass-card p-4 border-emerald-500/20">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <span class="text-xs text-cyan-300">{{ e($message->sender_name) }}</span>
                                    <span class="text-[10px] text-white/40 mr-2">{{ $message->created_at->diffForHumans() }}</span>
                                    <span class="text-[10px] text-emerald-400 mr-1">✅ تایید شده</span>
                                </div>
                            </div>
                            <p class="text-sm text-white/80 font-quote mb-3">"{{ e($message->message) }}"</p>
                            <form method="POST" action="{{ route('messages.delete', $message) }}" class="inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1.5 rounded-lg text-xs transition-colors">🗑️ حذف</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
