@extends('layouts.app')

@section('title', __('چت با ') . e($partner->name))

@section('content')
<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col" x-data="chatManager({{ $partner->id }}, {{ auth()->id() }})">
    {{-- Chat Header --}}
    <header class="glass-card p-4 mb-4 flex items-center gap-3 relative">
        <img src="{{ $partner->profile_picture_url }}" 
             alt="{{ e($partner->name) }}" 
             class="w-10 h-10 rounded-full object-cover">
        
        <div class="flex-1">
            <h1 class="font-heading text-sm">{{ e($partner->name) }}</h1>
            <p id="statusText" class="text-xs text-white/50">{{ __('آنلاین') }}</p>
        </div>
        
        {{-- Typing Indicator --}}
        <div id="typingIndicator" 
             x-show="isTyping" 
             x-transition
             class="hidden absolute left-4 top-14 bg-purple-500/20 border border-purple-500/30 rounded-lg px-3 py-1.5 flex items-center gap-2">
            <span class="text-xs text-purple-300">{{ __('در حال تایپ') }}</span>
            <div class="flex gap-1">
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.15s;"></span>
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.3s;"></span>
            </div>
        </div>
        
        {{-- Recording Indicator --}}
        <div id="recordingIndicator" 
             x-show="isRecording"
             x-transition
             class="hidden absolute left-4 top-14 bg-red-500/20 border border-red-500/30 rounded-lg px-3 py-1.5 flex items-center gap-2">
            <span class="text-xs text-red-300">{{ __('در حال ضبط صدا') }}</span>
            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
        </div>
        
        <a href="{{ route('chat.index') }}" 
           class="mr-auto text-xs text-white/60 hover:text-white flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('بازگشت') }}
        </a>
    </header>
    
    {{-- Messages Container --}}
    <main id="messagesContainer" 
          x-ref="messagesContainer"
          class="glass-card flex-1 overflow-y-auto p-4 space-y-3 scroll-smooth">
        @foreach($messages as $message)
            @php $isMe = $message->user_id === auth()->id(); @endphp
            @include('chat.partials.message_bubble', ['message' => $message, 'isMe' => $isMe])
        @endforeach
    </main>
    
    {{-- Voice Preview --}}
    <div id="voicePreview" 
         x-show="voicePreview.visible"
         x-transition
         class="hidden glass-card p-3 mt-4 flex items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <button id="playPreviewBtn" 
                    type="button"
                    @click="toggleVoicePreview()"
                    class="w-8 h-8 flex items-center justify-center bg-purple-500/30 hover:bg-purple-500/50 rounded-full transition-colors">
                <svg class="w-4 h-4 text-white play-icon" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <svg class="w-4 h-4 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                </svg>
            </button>
            <div class="flex-1 h-6 flex items-center gap-0.5" id="previewWaveform">
                @for($i = 0; $i < 30; $i++)
                    <div class="w-0.5 bg-purple-400/60 rounded-full preview-bar" style="height: {{ rand(20, 100) }}%;"></div>
                @endfor
            </div>
            <span id="recordingDuration" class="text-xs text-white/50">00:00</span>
        </div>
        <div class="flex gap-2">
            <button type="button" 
                    id="cancelVoiceBtn"
                    @click="cancelVoiceRecording()"
                    class="w-8 h-8 flex items-center justify-center bg-red-500/30 hover:bg-red-500/50 rounded-full transition-colors" 
                    title="{{ __('لغو') }}">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <button type="button" 
                    id="sendVoiceBtn"
                    @click="sendVoiceMessage()"
                    class="w-8 h-8 flex items-center justify-center bg-emerald-500/30 hover:bg-emerald-500/50 rounded-full transition-colors" 
                    title="{{ __('ارسال') }}">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
        <audio id="previewAudio" class="hidden"></audio>
    </div>
    
    {{-- Message Input Form --}}
    <form id="chatForm" 
          x-ref="chatForm"
          @submit.prevent="sendMessage"
          class="glass-card p-3 mt-4 flex items-center gap-3" 
          enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="userId" value="{{ $partner->id }}">
        
        {{-- File Attachment --}}
        <label for="fileInput" 
               class="cursor-pointer text-white/60 hover:text-white p-2 transition-colors" 
               title="{{ __('پیوست فایل') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
        </label>
        <input type="file" 
               id="fileInput" 
               name="file" 
               class="hidden" 
               accept="image/*,video/*,audio/*,.pdf"
               @change="handleFileSelect">
        
        {{-- Voice Record Button --}}
        <button type="button" 
                id="voiceRecordBtn"
                @click="toggleVoiceRecording"
                class="text-white/60 hover:text-white p-2 transition-colors" 
                title="{{ __('ضبط صدا') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
        </button>
        
        {{-- Text Input --}}
        <input type="text" 
               name="message" 
               id="messageInput" 
               x-model="messageText"
               @input="handleTyping"
               class="glass-input flex-1 text-sm py-2" 
               placeholder="{{ __('پیام خود را بنویسید...') }}">
        
        {{-- Send Button --}}
        <button type="submit" 
                class="btn-primary p-2" 
                title="{{ __('ارسال پیام') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>
    
    <p class="text-[10px] text-white/40 mt-2 text-center">
        {{ __('فرمت‌های مجاز: عکس، ویدیو، صوتی، PDF - حداکثر ۱۰MB') }}
    </p>
</div>

@push('scripts')
<script>
/**
 * Chat Manager - Handles all chat functionality
 */
function chatManager(partnerId, currentUserId) {
    return {
        partnerId: partnerId,
        currentUserId: currentUserId,
        lastMessageId: {{ $messages->last()?->id ?? 0 }},
        messageText: '',
        isTyping: false,
        isRecording: false,
        voicePreview: {
            visible: false,
            audioBlob: null,
            audioUrl: null,
            isPlaying: false
        },
        mediaRecorder: null,
        audioChunks: [],
        recordingStartTime: null,
        durationInterval: null,
        messagesPollingInterval: null,
        typingTimeout: null,
        
        init() {
            this.scrollToBottom();
            this.startMessagesPolling();
            this.setupEventListeners();
        },
        
        setupEventListeners() {
            const playPreviewBtn = document.getElementById('playPreviewBtn');
            if (playPreviewBtn) {
                playPreviewBtn.addEventListener('click', () => this.toggleVoicePreview());
            }
            
            const cancelBtn = document.getElementById('cancelVoiceBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => this.cancelVoiceRecording());
            }
            
            const sendBtn = document.getElementById('sendVoiceBtn');
            if (sendBtn) {
                sendBtn.addEventListener('click', () => this.sendVoiceMessage());
            }
        },
        
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        handleTyping() {
            // ارسال وضعیت تایپ به سرور فقط وقتی کاربر واقعاً در حال تایپ است
            // و فقط برای طرف مقابل، نه برای خود کاربر
            if (!this.typingTimeout) {
                this.sendTypingStatus(true);
            }
            
            clearTimeout(this.typingTimeout);
            this.typingTimeout = setTimeout(() => {
                this.sendTypingStatus(false);
                this.typingTimeout = null;
            }, 2000);
        },
        
        async sendTypingStatus(isTyping) {
            // فقط برای طرف مقابل ارسال شود، نه برای خود کاربر
            if (!isTyping && !this.typingTimeout) return;
            
            try {
                await fetch(`/api/chat/${this.partnerId}/typing`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken()
                    },
                    body: JSON.stringify({ is_typing: isTyping })
                });
            } catch (error) {
                console.error('Error sending typing status:', error);
            }
        },
        
        startMessagesPolling() {
            this.messagesPollingInterval = setInterval(async () => {
                try {
                    // دریافت پیام‌های جدید
                    const response = await fetch(`/api/chat/${this.partnerId}/messages?last_id=${this.lastMessageId}`);
                    const result = await response.json();
                    
                    if (result.success && result.messages && result.messages.length > 0) {
                        result.messages.forEach(msg => {
                            this.appendMessage(msg);
                            this.lastMessageId = msg.id;
                        });
                        this.scrollToBottom();
                    }
                    
                    // بررسی وضعیت تایپ طرف مقابل
                    this.checkTypingStatus();
                    
                } catch (error) {
                    console.error('Error fetching new messages:', error);
                }
            }, 2000);
        },
        
        async checkTypingStatus() {
            try {
                const response = await fetch(`/api/chat/${this.partnerId}/typing-status`);
                const result = await response.json();
                
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    if (result.is_typing) {
                        typingIndicator.classList.remove('hidden');
                    } else {
                        typingIndicator.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Error checking typing status:', error);
            }
        },
        
        appendMessage(message) {
            const container = this.$refs.messagesContainer;
            if (!container) return;
            
            const isMe = message.user_id === this.currentUserId;
            const messageHtml = this.renderMessageBubble(message, isMe);
            container.insertAdjacentHTML('beforeend', messageHtml);
        },
        
        renderMessageBubble(message, isMe) {
            const timeStr = new Date(message.created_at).toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            let fileHtml = '';
            if (message.file_path) {
                const fileUrl = '/storage/' + message.file_path;
                fileHtml = this.renderFileContent(message.file_type, fileUrl, timeStr);
            }
            
            const messageText = message.message ? `<p class="text-sm whitespace-pre-wrap">${this.escapeHtml(message.message)}</p>` : '';
            
            return `
                <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[70%] glass-card p-3 ${isMe ? 'bg-purple-500/20 border-purple-500/30' : ''}">
                        ${fileHtml}
                        ${messageText}
                        <div class="text-[10px] text-white/40 mt-1 text-left message-time">${timeStr}</div>
                    </div>
                </div>
            `;
        },
        
        renderFileContent(fileType, fileUrl, timeStr) {
            switch (fileType) {
                case 'image':
                    return `<img src="${fileUrl}" class="rounded-lg max-w-full mb-2">`;
                case 'video':
                    return `<video controls class="rounded-lg max-w-full mb-2"><source src="${fileUrl}" type="video/mp4"></video>`;
                case 'audio':
                    return this.renderVoiceMessage(fileUrl, timeStr);
                case 'file':
                    return `<a href="${fileUrl}" target="_blank" rel="noopener noreferrer" class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('دانلود فایل') }}
                    </a>`;
                default:
                    return '';
            }
        },
        
        renderVoiceMessage(fileUrl, timeStr) {
            const uniqueId = 'voice_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const waveformBars = Array(30).fill(0).map((_, i) => 
                `<div class="waveform-bar" style="height:${Math.floor(Math.random() * 80 + 20)}%; transition: height 0.1s;"></div>`
            ).join('');
            
            return `
                <div class="voice-message" id="${uniqueId}">
                    <button onclick="toggleVoicePlayback('${uniqueId}')" class="play-btn" type="button">
                        <svg class="w-5 h-5 text-white play-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg class="w-5 h-5 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                        </svg>
                    </button>
                    <div class="flex-1 min-w-0 mx-3">
                        <div class="voice-waveform flex items-center gap-0.5 h-8">${waveformBars}</div>
                    </div>
                    <span class="text-xs text-white/60 font-medium ml-3 bg-black/20 px-2 py-1 rounded">${timeStr}</span>
                    <audio class="hidden" src="${fileUrl}" data-voice-id="${uniqueId}"></audio>
                </div>
            `;
        },
        
        async sendMessage() {
            const form = this.$refs.chatForm;
            if (!form) return;
            
            const formData = new FormData(form);
            const messageText = this.messageText.trim();
            const hasFile = form.querySelector('#fileInput').files.length > 0;
            
            // اگر نه پیامی هست نه فایلی، خارج شو
            if (!messageText && !hasFile) return;
            
            try {
                const response = await fetch(`/chat/${this.partnerId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken()
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.appendMessage(result.message);
                    this.scrollToBottom();
                    this.messageText = '';
                    form.querySelector('#fileInput').value = '';
                    this.lastMessageId = result.message.id;
                    
                    // ارسال وضعیت توقف تایپ بعد از ارسال پیام
                    this.sendTypingStatus(false);
                    clearTimeout(this.typingTimeout);
                    this.typingTimeout = null;
                } else {
                    this.showAlert('❌ ' + result.message);
                }
            } catch (error) {
                console.error('Error sending message:', error);
                this.showAlert('{{ __('خطا در ارسال پیام') }}');
            }
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.sendMessage();
            }
        },
        
        async toggleVoiceRecording() {
            if (!this.isRecording) {
                await this.startRecording();
            } else {
                this.stopRecording();
            }
        },
        
        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100
                    } 
                });
                
                // Use better audio format for webm
                const options = { mimeType: 'audio/webm;codecs=opus' };
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'audio/webm';
                }
                
                this.mediaRecorder = new MediaRecorder(stream, options);
                this.audioChunks = [];
                
                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        this.audioChunks.push(event.data);
                    }
                };
                
                this.mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    this.voicePreview.audioBlob = audioBlob;
                    this.voicePreview.audioUrl = audioUrl;
                    this.voicePreview.visible = true;
                    
                    const previewAudio = document.getElementById('previewAudio');
                    if (previewAudio) {
                        previewAudio.src = audioUrl;
                    }
                    
                    // Stop all tracks to release microphone
                    stream.getTracks().forEach(track => track.stop());
                    
                    this.isRecording = false;
                    this.clearRecordingTimer();
                };
                
                this.mediaRecorder.onerror = (event) => {
                    console.error('MediaRecorder error:', event.error);
                    this.showAlert('خطا در ضبط صدا: ' + event.error.name);
                    this.isRecording = false;
                    stream.getTracks().forEach(track => track.stop());
                };
                
                this.mediaRecorder.start(100); // Collect data every 100ms
                this.isRecording = true;
                this.recordingStartTime = Date.now();
                this.startRecordingTimer();
                
                this.isTyping = false;
                document.getElementById('typingIndicator')?.classList.add('hidden');
                
            } catch (error) {
                console.error('Error accessing microphone:', error);
                let errorMsg = 'دسترسی به میکروفون نیاز است';
                if (error.name === 'NotAllowedError') {
                    errorMsg = 'اجازه دسترسی به میکروفون داده نشده است';
                } else if (error.name === 'NotFoundError') {
                    errorMsg = 'میکروفونی یافت نشد';
                } else if (error.name === 'NotReadableError') {
                    errorMsg = 'میکروفون در حال استفاده توسط برنامه دیگری است';
                }
                this.showAlert(errorMsg);
            }
        },
        
        stopRecording() {
            if (this.mediaRecorder && this.isRecording) {
                this.mediaRecorder.stop();
            }
        },
        
        startRecordingTimer() {
            const durationDisplay = document.getElementById('recordingDuration');
            
            this.durationInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - this.recordingStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                
                if (durationDisplay) {
                    durationDisplay.textContent = `${minutes}:${seconds}`;
                }
            }, 1000);
        },
        
        clearRecordingTimer() {
            if (this.durationInterval) {
                clearInterval(this.durationInterval);
                this.durationInterval = null;
            }
        },
        
        toggleVoicePreview() {
            const previewAudio = document.getElementById('previewAudio');
            const playBtn = document.getElementById('playPreviewBtn');
            
            if (!previewAudio || !playBtn) return;
            
            const playIcon = playBtn.querySelector('.play-icon');
            const pauseIcon = playBtn.querySelector('.pause-icon');
            
            if (this.voicePreview.isPlaying) {
                previewAudio.pause();
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
                this.voicePreview.isPlaying = false;
            } else {
                previewAudio.play();
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
                this.voicePreview.isPlaying = true;
            }
            
            previewAudio.onended = () => {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
                this.voicePreview.isPlaying = false;
            };
        },
        
        cancelVoiceRecording() {
            this.voicePreview.visible = false;
            this.voicePreview.audioBlob = null;
            this.voicePreview.audioUrl = null;
            this.voicePreview.isPlaying = false;
            
            const previewAudio = document.getElementById('previewAudio');
            if (previewAudio) {
                previewAudio.src = '';
                previewAudio.load();
            }
            
            const playBtn = document.getElementById('playPreviewBtn');
            if (playBtn) {
                const playIcon = playBtn.querySelector('.play-icon');
                const pauseIcon = playBtn.querySelector('.pause-icon');
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            }
            
            const durationDisplay = document.getElementById('recordingDuration');
            if (durationDisplay) {
                durationDisplay.textContent = '00:00';
            }
        },
        
        async sendVoiceMessage() {
            if (!this.voicePreview.audioBlob) return;
            
            const formData = new FormData();
            formData.append('userId', this.partnerId);
            formData.append('file', this.voicePreview.audioBlob, 'voice_message.webm');
            
            try {
                const response = await fetch(`/chat/${this.partnerId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken()
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.appendMessage(result.message);
                    this.scrollToBottom();
                    this.cancelVoiceRecording();
                    this.lastMessageId = result.message.id;
                } else {
                    this.showAlert('{{ __('خطا در ارسال ویس') }}');
                }
            } catch (error) {
                console.error('Error sending voice message:', error);
                this.showAlert('{{ __('خطا در ارسال ویس') }}');
            }
        },
        
        getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        showAlert(message) {
            alert(message);
        },
        
        destroy() {
            if (this.messagesPollingInterval) {
                clearInterval(this.messagesPollingInterval);
            }
            if (this.typingTimeout) {
                clearTimeout(this.typingTimeout);
            }
            if (this.durationInterval) {
                clearInterval(this.durationInterval);
            }
            if (this.mediaRecorder && this.isRecording) {
                this.mediaRecorder.stop();
            }
        }
    };
}

/**
 * Global voice playback toggle function for dynamically added voice messages
 */
function toggleVoicePlayback(voiceId) {
    const voiceElement = document.getElementById(voiceId);
    if (!voiceElement) return;
    
    const audio = voiceElement.querySelector('audio');
    const btn = voiceElement.querySelector('.play-btn');
    const playIcon = btn?.querySelector('.play-icon');
    const pauseIcon = btn?.querySelector('.pause-icon');
    const waveformBars = voiceElement.querySelectorAll('.waveform-bar');
    
    if (!audio || !btn) return;
    
    // Stop all other playing audios
    document.querySelectorAll('audio').forEach(a => {
        if (a !== audio && !a.paused) {
            a.pause();
            const otherVoiceEl = a.closest('.voice-message');
            const otherBtn = otherVoiceEl?.querySelector('.play-btn');
            if (otherBtn) {
                otherBtn.querySelector('.play-icon')?.classList.remove('hidden');
                otherBtn.querySelector('.pause-icon')?.classList.add('hidden');
            }
            // Reset other waveform animation
            const otherWaveform = otherVoiceEl?.querySelectorAll('.waveform-bar');
            otherWaveform?.forEach(bar => bar.style.height = Math.floor(Math.random() * 80 + 20) + '%');
        }
    });
    
    if (audio.paused) {
        audio.play();
        playIcon?.classList.add('hidden');
        pauseIcon?.classList.remove('hidden');
        
        // Animate waveform while playing
        audio.waveformInterval = setInterval(() => {
            waveformBars.forEach((bar, i) => {
                bar.style.height = Math.floor(Math.random() * 80 + 20) + '%';
            });
        }, 100);
    } else {
        audio.pause();
        playIcon?.classList.remove('hidden');
        pauseIcon?.classList.add('hidden');
        
        // Stop waveform animation
        if (audio.waveformInterval) {
            clearInterval(audio.waveformInterval);
        }
        waveformBars.forEach(bar => bar.style.height = Math.floor(Math.random() * 80 + 20) + '%');
    }
    
    audio.onended = () => {
        playIcon?.classList.remove('hidden');
        pauseIcon?.classList.add('hidden');
        if (audio.waveformInterval) {
            clearInterval(audio.waveformInterval);
        }
        waveformBars.forEach(bar => bar.style.height = Math.floor(Math.random() * 80 + 20) + '%');
    };
}
</script>
@endpush

@endsection
