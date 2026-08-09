@extends('layouts.app')
@section('title', 'مدیریت پیام‌ها')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="glass-card p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                <span class="text-xl">📬</span>
            </div>
            <div>
                <h1 class="text-xl font-heading">مدیریت پیام‌ها</h1>
                <p class="text-xs text-white/50">پیام‌های ارسالی کاربران را مدیریت کنید</p>
            </div>
        </div>
        
        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                <span>✨</span> {{ session('success') }}
            </div>
        @endif
        
        <!-- Tabs -->
        <div class="flex gap-2 mb-6 border-b border-white/10 pb-2">
            <button onclick="showTab('pending')" id="tab-pending" class="px-4 py-2 rounded-lg text-sm bg-white/10 text-white font-medium transition">
                ⏳ در انتظار ({{ $pendingMessages->count() }})
            </button>
            <button onclick="showTab('approved')" id="tab-approved" class="px-4 py-2 rounded-lg text-sm text-white/60 hover:text-white transition">
                ✅ تایید شده ({{ $approvedMessages->count() }})
            </button>
        </div>
        
        <!-- پیام‌های در انتظار تایید -->
        <div id="tab-pending-content" class="tab-content">
            <h2 class="text-lg font-heading mb-4 text-cyan-300">پیام‌های در انتظار بررسی</h2>
            @if($pendingMessages->isEmpty())
                <div class="text-center py-12">
                    <div class="text-6xl mb-4 opacity-50">📭</div>
                    <p class="text-white/50 text-sm">هیچ پیامی در انتظار نیست</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pendingMessages as $message)
                        <div class="glass-card p-4 hover:bg-white/5 transition-all duration-300 group">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-cyan-500 to-blue-500 flex items-center justify-center text-xs font-bold">
                                        {{ substr(e($message->sender_name), 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="text-sm text-cyan-300 font-medium">{{ e($message->sender_name) }}</span>
                                        <div class="text-[10px] text-white/40">{{ $message->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <span class="text-xs bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded">در انتظار</span>
                            </div>
                            <div class="bg-white/5 rounded-lg p-3 mb-3 border-r-2 border-cyan-500/50">
                                <p class="text-sm text-white/80 font-quote">{{ e($message->message) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('messages.approve', $message) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 px-4 py-2 rounded-lg text-xs transition-all duration-300 flex items-center gap-1 hover:scale-105">
                                        <span>✅</span> تایید و انتشار
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('messages.delete', $message) }}" class="inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-4 py-2 rounded-lg text-xs transition-all duration-300 flex items-center gap-1 hover:scale-105">
                                        <span>❌</span> رد کردن
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- پیام‌های تایید شده -->
        <div id="tab-approved-content" class="tab-content hidden">
            <h2 class="text-lg font-heading mb-4 text-emerald-300">پیام‌های منتشر شده</h2>
            @if($approvedMessages->isEmpty())
                <div class="text-center py-12">
                    <div class="text-6xl mb-4 opacity-50">📝</div>
                    <p class="text-white/50 text-sm">هنوز پیامی تایید نشده</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($approvedMessages as $message)
                        <div class="glass-card p-4 border-emerald-500/20 hover:bg-white/5 transition-all duration-300 group">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-500 to-green-500 flex items-center justify-center text-xs font-bold">
                                        {{ substr(e($message->sender_name), 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="text-sm text-emerald-300 font-medium">{{ e($message->sender_name) }}</span>
                                        <div class="text-[10px] text-white/40">{{ $message->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded flex items-center gap-1">
                                    <span>✅</span> منتشر شده
                                </span>
                            </div>
                            <div class="bg-emerald-500/5 rounded-lg p-3 mb-3 border-r-2 border-emerald-500/50">
                                <p class="text-sm text-white/80 font-quote">{{ e($message->message) }}</p>
                            </div>
                            <form method="POST" action="{{ route('messages.delete', $message) }}" class="inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-4 py-2 rounded-lg text-xs transition-all duration-300 flex items-center gap-1 hover:scale-105">
                                    <span>🗑️</span> حذف از لیست
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    // Reset tab buttons
    document.getElementById('tab-pending').classList.remove('bg-white/10', 'text-white', 'font-medium');
    document.getElementById('tab-pending').classList.add('text-white/60');
    document.getElementById('tab-approved').classList.remove('bg-white/10', 'text-white', 'font-medium');
    document.getElementById('tab-approved').classList.add('text-white/60');
    
    // Show selected tab
    document.getElementById('tab-' + tabName + '-content').classList.remove('hidden');
    document.getElementById('tab-' + tabName).classList.add('bg-white/10', 'text-white', 'font-medium');
    document.getElementById('tab-' + tabName).classList.remove('text-white/60');
}

// Initialize with pending tab active
showTab('pending');
</script>
@endpush
@endsection