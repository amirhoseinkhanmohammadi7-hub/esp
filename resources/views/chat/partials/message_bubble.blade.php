@props(['message', 'isMe' => false])

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} mb-3">
    <div 
        class="max-w-[70%] rounded-2xl px-4 py-2 shadow-sm
        {{ $isMe 
            ? 'bg-blue-600 text-white rounded-br-none' 
            : 'bg-gray-100 text-gray-900 rounded-bl-none' 
        }}"
    >
        @if($message->file_path)
            @if($message->file_type === 'image')
                <div class="mb-2">
                    <img 
                        src="{{ $message->file_url }}" 
                        alt="{{ __('Attached image') }}" 
                        class="rounded-lg max-w-full h-auto"
                        loading="lazy"
                    >
                </div>
            @elseif($message->file_type === 'video')
                <div class="mb-2">
                    <video controls class="rounded-lg max-w-full">
                        <source src="{{ $message->file_url }}" type="video/mp4">
                    </video>
                </div>
            @elseif($message->file_type === 'audio')
                <div class="mb-2">
                    @include('chat.partials.voice_message', ['message' => $message, 'isMe' => $isMe])
                </div>
            @else
                <div class="mb-2">
                    <a 
                        href="{{ $message->file_url }}" 
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm">{{ __('Download File') }}</span>
                    </a>
                </div>
            @endif
        @endif
        
        @if($message->message)
            <p class="text-sm whitespace-pre-wrap break-words">{{ e($message->message) }}</p>
        @endif
        
        <div class="text-xs mt-1 {{ $isMe ? 'text-blue-100' : 'text-gray-500' }}">
            {{ $message->created_at->format('H:i') }}
            @if($isMe)
                @if($message->read_at)
                    <span title="{{ __('Read at') }} {{ $message->read_at->format('Y-m-d H:i:s') }}">✓✓</span>
                @else
                    <span title="{{ __('Sent') }}">✓</span>
                @endif
            @endif
        </div>
    </div>
</div>
