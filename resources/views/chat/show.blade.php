@extends('layouts.app')
@section('title', 'چت با ' . $partner->name)
@section('content')
<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col">
    <div class="glass-card p-4 mb-4 flex items-center gap-3">
        <img src="{{ $partner->profile_picture_url }}" alt="{{ $partner->name }}" class="w-10 h-10 rounded-full object-cover">
        <div>
            <h1 class="font-heading text-sm">{{ $partner->name }}</h1>
            <p class="text-xs text-white/50">آنلاین</p>
        </div>
        <a href="{{ route('chat.index') }}" class="mr-auto text-xs text-white/60 hover:text-white">بازگشت به لیست</a>
    </div>

    <!-- پیام‌ها -->
    <div id="messagesContainer" class="glass-card flex-1 overflow-y-auto p-4 space-y-3">
        @foreach($messages as $message)
            @php
                $isMe = $message->user_id === auth()->id();
            @endphp
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] glass-card p-3 {{ $isMe ? 'bg-purple-500/20 border-purple-500/30' : '' }}">
                    @if($message->file_path)
                        @if($message->file_type === 'image')
                            <img src="{{ asset('storage/' . $message->file_path) }}" alt="تصویر" class="rounded-lg max-w-full mb-2">
                        @elseif($message->file_type === 'video')
                            <video controls class="rounded-lg max-w-full mb-2">
                                <source src="{{ asset('storage/' . $message->file_path) }}" type="video/mp4">
                            </video>
                        @elseif($message->file_type === 'audio')
                            <audio controls class="mb-2">
                                <source src="{{ asset('storage/' . $message->file_path) }}" type="audio/mpeg">
                            </audio>
                        @else
                            <a href="{{ asset('storage/' . $message->file_path) }}" target="_blank" class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">
                                📎 دانلود فایل
                            </a>
                        @endif
                    @endif
                    @if($message->message)
                        <p class="text-sm whitespace-pre-wrap">{{ e($message->message) }}</p>
                    @endif
                    <div class="text-[10px] text-white/40 mt-1 text-left">{{ $message->created_at->format('H:i') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- فرم ارسال پیام -->
    <form id="chatForm" class="glass-card p-3 mt-4 flex items-center gap-3" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="userId" value="{{ $partner->id }}">
        
        <label for="fileInput" class="cursor-pointer text-white/60 hover:text-white p-2">
            📎
        </label>
        <input type="file" id="fileInput" name="file" class="hidden" accept="image/*,video/*,audio/*,.pdf">
        
        <input type="text" name="message" id="messageInput" class="glass-input flex-1 text-sm py-2" placeholder="پیام خود را بنویسید...">
        
        <button type="submit" class="btn-primary p-2">
            📤
        </button>
    </form>
    
    <p class="text-[10px] text-white/40 mt-2 text-center">فرمت‌های مجاز: عکس، ویدیو، صوتی، PDF - حداکثر 10MB</p>
</div>

@push('scripts')
<script>
const messagesContainer = document.getElementById('messagesContainer');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const fileInput = document.getElementById('fileInput');

// Scroll to bottom on load
messagesContainer.scrollTop = messagesContainer.scrollHeight;

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(chatForm);
    const userId = formData.get('userId');
    
    try {
        const response = await fetch(`/chat/${userId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            // Add message to UI
            const msg = result.message;
            const isMe = true;
            let fileHtml = '';
            
            if (msg.file_path) {
                const fileUrl = '/storage/' + msg.file_path;
                if (msg.file_type === 'image') {
                    fileHtml = `<img src="${fileUrl}" alt="تصویر" class="rounded-lg max-w-full mb-2">`;
                } else if (msg.file_type === 'video') {
                    fileHtml = `<video controls class="rounded-lg max-w-full mb-2"><source src="${fileUrl}" type="video/mp4"></video>`;
                } else if (msg.file_type === 'audio') {
                    fileHtml = `<audio controls class="mb-2"><source src="${fileUrl}" type="audio/mpeg"></audio>`;
                } else {
                    fileHtml = `<a href="${fileUrl}" target="_blank" class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">📎 دانلود فایل</a>`;
                }
            }
            
            const messageHtml = `
                <div class="flex justify-end">
                    <div class="max-w-[70%] glass-card p-3 bg-purple-500/20 border-purple-500/30">
                        ${fileHtml}
                        ${msg.message ? `<p class="text-sm whitespace-pre-wrap">${escapeHtml(msg.message)}</p>` : ''}
                        <div class="text-[10px] text-white/40 mt-1 text-left">${new Date().toLocaleTimeString('fa-IR', {hour: '2-digit', minute:'2-digit'})}</div>
                    </div>
                </div>
            `;
            
            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Reset form
            messageInput.value = '';
            fileInput.value = '';
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('خطا در ارسال پیام');
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
