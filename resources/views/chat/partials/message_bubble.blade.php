@props(['message', 'isMe'])

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-[70%] glass-card p-3 {{ $isMe ? 'bg-purple-500/20 border-purple-500/30' : '' }}">
        @if($message->file_path)
            @if($message->file_type === 'image')
                <img src="{{ url('storage/' . $message->file_path) }}" 
                     alt="{{ __('تصویر ارسالی') }}" 
                     class="rounded-lg max-w-full mb-2">
            @elseif($message->file_type === 'video')
                <video controls class="rounded-lg max-w-full mb-2">
                    <source src="{{ url('storage/' . $message->file_path) }}" type="video/mp4">
                </video>
            @elseif($message->file_type === 'audio')
                @include('chat.partials.voice_message', ['message' => $message])
            @else
                <a href="{{ url('storage/' . $message->file_path) }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    {{ __('دانلود فایل') }}
                </a>
            @endif
        @endif
        
        @if($message->message)
            <p class="text-sm whitespace-pre-wrap">{{ e($message->message) }}</p>
        @endif
        
        <div class="text-[10px] text-white/40 mt-1 text-left message-time">
            {{ $message->created_at->format('H:i') }}
        </div>
    </div>
</div>
