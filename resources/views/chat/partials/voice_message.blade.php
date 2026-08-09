@props(['message'])

<div class="voice-message">
    <button onclick="toggleVoicePlayback(this)" 
            class="play-btn"
            type="button"
            aria-label="{{ __('پخش صدا') }}">
        <svg class="w-5 h-5 text-white play-icon" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
        </svg>
        <svg class="w-5 h-5 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24">
            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
        </svg>
    </button>
    <div class="flex-1 min-w-0 mx-3">
        <div class="voice-waveform flex items-center gap-0.5 h-8">
            @for($i = 0; $i < 30; $i++)
                <div class="w-0.5 bg-purple-400/60 rounded-full" 
                     style="height: {{ rand(20, 100) }}%;"></div>
            @endfor
        </div>
    </div>
    <span class="text-xs text-white/60 font-medium ml-3 bg-black/20 px-2 py-1 rounded">
        {{ $message->created_at->format('H:i') }}
    </span>
    <audio class="hidden" src="{{ url('storage/' . $message->file_path) }}"></audio>
</div>
