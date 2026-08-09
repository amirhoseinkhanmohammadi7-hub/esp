@extends('layouts.app')
@section('title', 'چت‌ها')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="glass-card p-6">
        <h1 class="text-xl font-heading mb-6">💬 چت‌های من</h1>
        
        @if(session('error'))
            <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- درخواست‌های در انتظار -->
        <div id="pendingRequests" class="mb-6">
            <h2 class="text-lg font-heading mb-3">📩 درخواست‌های در انتظار</h2>
            <div id="requestsList" class="space-y-2">
                <p class="text-white/50 text-sm">در حال بارگذاری...</p>
            </div>
        </div>
        
        <!-- لیست چت‌ها -->
        <div>
            <h2 class="text-lg font-heading mb-3">💬 گفتگوها</h2>
            @if(count($chatPartners) === 0)
                <p class="text-white/50 text-sm text-center py-8">هنوز گفتگویی ندارید</p>
            @else
                <div class="space-y-2">
                    @foreach($chatPartners as $partner)
                        <a href="{{ route('chat.show', $partner['user']->id) }}" class="block glass-card p-4 hover:bg-white/5 transition flex items-center gap-4">
                            <img src="{{ $partner['user']->profile_picture_url }}" alt="{{ $partner['user']->name }}" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <div class="font-heading text-sm">{{ $partner['user']->name }}</div>
                                @if($partner['last_message'])
                                    <div class="text-xs text-white/50 truncate">
                                        @if($partner['last_message']->file_type === 'image')
                                            📷 تصویر
                                        @elseif($partner['last_message']->file_type === 'video')
                                            🎥 ویدیو
                                        @elseif($partner['last_message']->file_type === 'audio')
                                            🎵 صوتی
                                        @elseif($partner['last_message']->file_type === 'file')
                                            📎 فایل
                                        @else
                                            {{ Str::limit($partner['last_message']->message, 30) }}
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-white/40">شروع گفتگو</div>
                                @endif
                            </div>
                            <div class="text-[10px] text-white/40">
                                {{ $partner['last_message'] ? $partner['last_message']->created_at->diffForHumans() : '' }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
async function loadPendingRequests() {
    try {
        const response = await fetch('{{ route('chat.requests') }}');
        const result = await response.json();
        const container = document.getElementById('requestsList');
        
        if (result.requests && result.requests.length === 0) {
            container.innerHTML = '<p class="text-white/50 text-sm">هیچ درخواستی ندارید</p>';
            return;
        }
        
        container.innerHTML = result.requests.map(req => `
            <div class="glass-card p-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <img src="${req.sender.profile_picture_url}" alt="${req.sender.name}" class="w-8 h-8 rounded-full object-cover">
                    <span class="text-sm">${req.sender.name}</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="respondToRequest(${req.id}, 'accept')" class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 px-3 py-1.5 rounded-lg text-xs transition-colors">✅ قبول</button>
                    <button onclick="respondToRequest(${req.id}, 'reject')" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1.5 rounded-lg text-xs transition-colors">❌ رد</button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading requests:', error);
    }
}

async function respondToRequest(requestId, action) {
    try {
        const response = await fetch(`/chat-request/${requestId}/respond`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ action })
        });
        
        const result = await response.json();
        if (result.success) {
            loadPendingRequests();
            // Update badge
            const badge = document.getElementById('chatRequestBadge');
            if (badge) badge.classList.add('hidden');
        }
    } catch (error) {
        alert('خطا در پاسخ به درخواست');
    }
}

document.addEventListener('DOMContentLoaded', loadPendingRequests);
</script>
@endpush
@endsection
