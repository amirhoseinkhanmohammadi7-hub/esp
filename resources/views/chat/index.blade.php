@extends('layouts.app')

@section('title', __('چت‌ها'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="chatRequestsManager()">
    <div class="glass-card p-6">
        <h1 class="text-xl font-heading mb-6">💬 {{ __('چت‌های من') }}</h1>

        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        {{-- Pending Requests Section --}}
        <section id="pendingRequests" class="mb-6">
            <h2 class="text-lg font-heading mb-3">📩 {{ __('درخواست‌های در انتظار') }}</h2>
            <div id="requestsList" class="space-y-2" x-html="loadingMessage()">
                <p class="text-white/50 text-sm">{{ __('در حال بارگذاری...') }}</p>
            </div>
        </section>

        {{-- Chat List Section --}}
        <section>
            <h2 class="text-lg font-heading mb-3">💬 {{ __('گفتگوها') }}</h2>
            
            @if(count($chatPartners) === 0)
                <x-empty-state 
                    icon="💬" 
                    message="{{ __('هنوز گفتگویی ندارید') }}" 
                />
            @else
                <div class="space-y-2">
                    @foreach($chatPartners as $partner)
                        <a href="{{ route('chat.show', $partner['user']->id) }}" 
                           class="block glass-card p-4 hover:bg-white/5 transition flex items-center gap-4 group">
                            <img src="{{ $partner['user']->profile_picture_url }}" 
                                 alt="{{ e($partner['user']->name) }}" 
                                 class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent group-hover:ring-purple-500/30 transition">
                            
                            <div class="flex-1 min-w-0">
                                <div class="font-heading text-sm">{{ e($partner['user']->name) }}</div>
                                
                                @if($partner['last_message'])
                                    <div class="text-xs text-white/50 truncate">
                                        @include('chat.partials.message_preview', ['message' => $partner['last_message']])
                                    </div>
                                @else
                                    <div class="text-xs text-white/40">{{ __('شروع گفتگو') }}</div>
                                @endif
                            </div>
                            
                            <div class="text-[10px] text-white/40">
                                @if($partner['last_message'])
                                    {{ $partner['last_message']->created_at->diffForHumans() }}
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>

@push('scripts')
<script>
/**
 * Chat Requests Manager using Alpine.js
 */
function chatRequestsManager() {
    return {
        isLoading: true,
        requests: [],
        
        async init() {
            await this.loadPendingRequests();
        },
        
        loadingMessage() {
            return `<p class="text-white/50 text-sm">${this.isLoading ? '{{ __('در حال بارگذاری...') }}' : '${this.requests.length === 0 ? '{{ __('هیچ درخواستی ندارید') }}' : ''}</p>`;
        },
        
        async loadPendingRequests() {
            try {
                const response = await fetch('{{ route('chat.requests') }}');
                const result = await response.json();
                
                this.requests = result.requests || [];
                this.renderRequests();
            } catch (error) {
                console.error('Error loading requests:', error);
                this.showError('{{ __('خطا در بارگذاری درخواست‌ها') }}');
            } finally {
                this.isLoading = false;
            }
        },
        
        renderRequests() {
            const container = document.getElementById('requestsList');
            
            if (this.requests.length === 0) {
                container.innerHTML = '<p class="text-white/50 text-sm">{{ __('هیچ درخواستی ندارید') }}</p>';
                return;
            }
            
            container.innerHTML = this.requests.map(req => `
                <div class="glass-card p-3 flex items-center justify-between gap-3 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <img src="${this.escapeHtml(req.sender.profile_picture_url)}" 
                             alt="${this.escapeHtml(req.sender.name)}" 
                             class="w-8 h-8 rounded-full object-cover">
                        <span class="text-sm">${this.escapeHtml(req.sender.name)}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="Alpine.store('chatRequests').respondToRequest(${req.id}, 'accept')" 
                                class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 px-3 py-1.5 rounded-lg text-xs transition-colors">
                            ✅ {{ __('قبول') }}
                        </button>
                        <button onclick="Alpine.store('chatRequests').respondToRequest(${req.id}, 'reject')" 
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1.5 rounded-lg text-xs transition-colors">
                            ❌ {{ __('رد') }}
                        </button>
                    </div>
                </div>
            `).join('');
        },
        
        async respondToRequest(requestId, action) {
            try {
                const response = await fetch(`/chat-request/${requestId}/respond`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken()
                    },
                    body: JSON.stringify({ action })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await this.loadPendingRequests();
                    this.updateBadge();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error responding to request:', error);
                this.showError('{{ __('خطا در پاسخ به درخواست') }}');
            }
        },
        
        updateBadge() {
            const badge = document.getElementById('chatRequestBadge');
            if (badge && this.requests.length === 0) {
                badge.classList.add('hidden');
            }
        },
        
        showError(message) {
            alert(message);
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        }
    };
}

// Initialize Alpine store for global access
document.addEventListener('alpine:init', () => {
    Alpine.store('chatRequests', {
        async respondToRequest(requestId, action) {
            const component = document.querySelector('[x-data="chatRequestsManager()"]').__x.$data;
            await component.respondToRequest(requestId, action);
        }
    });
});
</script>
@endpush

@endsection
