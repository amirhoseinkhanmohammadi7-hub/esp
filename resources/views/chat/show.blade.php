@extends('layouts.app')
@section('title', 'چت با ' . $partner->name)
@section('content')
<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col">
    <div class="glass-card p-4 mb-4 flex items-center gap-3 relative">
        <img src="{{ $partner->profile_picture_url }}" alt="{{ $partner->name }}" class="w-10 h-10 rounded-full object-cover">
        <div class="flex-1">
            <h1 class="font-heading text-sm">{{ $partner->name }}</h1>
            <p id="statusText" class="text-xs text-white/50">آنلاین</p>
        </div>
        <div id="typingIndicator" class="hidden absolute left-4 top-14 bg-purple-500/20 border border-purple-500/30 rounded-lg px-3 py-1.5 flex items-center gap-2">
            <span class="text-xs text-purple-300">در حال تایپ</span>
            <div class="flex gap-1">
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.15s;"></span>
                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.3s;"></span>
            </div>
        </div>
        <div id="recordingIndicator" class="hidden absolute left-4 top-14 bg-red-500/20 border border-red-500/30 rounded-lg px-3 py-1.5 flex items-center gap-2">
            <span class="text-xs text-red-300">در حال ضبط صدا</span>
            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
        </div>
        <a href="{{ route('chat.index') }}" class="mr-auto text-xs text-white/60 hover:text-white flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            بازگشت
        </a>
    </div>
    <div id="messagesContainer" class="glass-card flex-1 overflow-y-auto p-4 space-y-3">
        @foreach($messages as $message)
            @php $isMe = $message->user_id === auth()->id(); @endphp
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] glass-card p-3 {{ $isMe ? 'bg-purple-500/20 border-purple-500/30' : '' }}">
                    @if($message->file_path)
                        @if($message->file_type === 'image')
                            <img src="{{ asset('storage/' . $message->file_path) }}" class="rounded-lg max-w-full mb-2">
                        @elseif($message->file_type === 'video')
                            <video controls class="rounded-lg max-w-full mb-2"><source src="{{ asset('storage/' . $message->file_path) }}" type="video/mp4"></video>
                        @elseif($message->file_type === 'audio')
                            <div class="voice-message flex items-center gap-2 bg-black/20 rounded-lg p-2 pr-3">
                                <button onclick="toggleVoicePlayback(this)" class="play-btn w-8 h-8 flex items-center justify-center bg-purple-500/30 hover:bg-purple-500/50 rounded-full transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4 text-white play-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg class="w-4 h-4 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                                </button>
                                <div class="flex-1 min-w-0">
                                    <div class="voice-waveform h-6 flex items-center gap-0.5">
                                        @for($i = 0; $i < 30; $i++)<div class="w-0.5 bg-purple-400/60 rounded-full" style="height: {{ rand(20, 100) }}%;"></div>@endfor
                                    </div>
                                </div>
                                <span class="text-xs text-white/50 ml-2">{{ $message->created_at->format('H:i') }}</span>
                                <audio class="hidden" src="{{ asset('storage/' . $message->file_path) }}"></audio>
                            </div>
                        @else
                            <a href="{{ asset('storage/' . $message->file_path) }}" target="_blank" class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>دانلود فایل
                            </a>
                        @endif
                    @endif
                    @if($message->message)<p class="text-sm whitespace-pre-wrap">{{ e($message->message) }}</p>@endif
                    <div class="text-[10px] text-white/40 mt-1 text-left message-time">{{ $message->created_at->format('H:i') }}</div>
                </div>
            </div>
        @endforeach
    </div>
    <div id="voicePreview" class="hidden glass-card p-3 mt-4 flex items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <button id="playPreviewBtn" class="w-8 h-8 flex items-center justify-center bg-purple-500/30 hover:bg-purple-500/50 rounded-full transition-colors">
                <svg class="w-4 h-4 text-white play-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                <svg class="w-4 h-4 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
            </button>
            <div class="flex-1 h-6 flex items-center gap-0.5" id="previewWaveform">
                @for($i = 0; $i < 30; $i++)<div class="w-0.5 bg-purple-400/60 rounded-full preview-bar" style="height: {{ rand(20, 100) }}%;"></div>@endfor
            </div>
            <span id="recordingDuration" class="text-xs text-white/50">00:00</span>
        </div>
        <div class="flex gap-2">
            <button type="button" id="cancelVoiceBtn" class="w-8 h-8 flex items-center justify-center bg-red-500/30 hover:bg-red-500/50 rounded-full transition-colors" title="لغو">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <button type="button" id="sendVoiceBtn" class="w-8 h-8 flex items-center justify-center bg-emerald-500/30 hover:bg-emerald-500/50 rounded-full transition-colors" title="ارسال">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
        <audio id="previewAudio" class="hidden"></audio>
    </div>
    <form id="chatForm" class="glass-card p-3 mt-4 flex items-center gap-3" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="userId" value="{{ $partner->id }}">
        <label for="fileInput" class="cursor-pointer text-white/60 hover:text-white p-2" title="پیوست فایل">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
        </label>
        <input type="file" id="fileInput" name="file" class="hidden" accept="image/*,video/*,audio/*,.pdf">
        <button type="button" id="voiceRecordBtn" class="text-white/60 hover:text-white p-2" title="ضبط صدا">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
        </button>
        <input type="text" name="message" id="messageInput" class="glass-input flex-1 text-sm py-2" placeholder="پیام خود را بنویسید...">
        <button type="submit" class="btn-primary p-2" title="ارسال پیام">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
    </form>
    <p class="text-[10px] text-white/40 mt-2 text-center">فرمت‌های مجاز: عکس، ویدیو، صوتی، PDF - حداکثر 10MB</p>
</div>
@push('scripts')
<script>
const messagesContainer=document.getElementById('messagesContainer'),chatForm=document.getElementById('chatForm'),messageInput=document.getElementById('messageInput'),fileInput=document.getElementById('fileInput'),voiceRecordBtn=document.getElementById('voiceRecordBtn'),typingIndicator=document.getElementById('typingIndicator'),recordingIndicator=document.getElementById('recordingIndicator'),voicePreview=document.getElementById('voicePreview'),previewAudio=document.getElementById('previewAudio'),playPreviewBtn=document.getElementById('playPreviewBtn'),cancelVoiceBtn=document.getElementById('cancelVoiceBtn'),sendVoiceBtn=document.getElementById('sendVoiceBtn'),recordingDuration=document.getElementById('recordingDuration');
messagesContainer.scrollTop=messagesContainer.scrollHeight;
function updateClock(){document.querySelectorAll('.message-time').forEach(el=>{el.textContent=new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})})}
setInterval(updateClock,1000);
function toggleVoicePlayback(btn){const audio=btn.parentElement.querySelector('audio'),playIcon=btn.querySelector('.play-icon'),pauseIcon=btn.querySelector('.pause-icon');if(audio.paused){document.querySelectorAll('audio').forEach(a=>{if(a!==audio){a.pause();a.parentElement.querySelector('.play-icon')?.classList.remove('hidden');a.parentElement.querySelector('.pause-icon')?.classList.add('hidden')}});audio.play();playIcon.classList.add('hidden');pauseIcon.classList.remove('hidden')}else{audio.pause();playIcon.classList.remove('hidden');pauseIcon.classList.add('hidden')}audio.onended=()=>{playIcon.classList.remove('hidden');pauseIcon.classList.add('hidden')}}
let typingTimeout,isTyping=false;messageInput.addEventListener('input',()=>{typingIndicator.classList.remove('hidden');if(!isTyping){isTyping=true;fetch(`/api/chat/{{ $partner->id }}/typing`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({is_typing:true})})}clearTimeout(typingTimeout);typingTimeout=setTimeout(()=>{typingIndicator.classList.add('hidden');isTyping=false},2000)});
let lastMessageId={{ $messages->last()?->id ?? 0 }};setInterval(async ()=>{try{const response=await fetch(`/api/chat/{{ $partner->id }}/messages?last_id=${lastMessageId}`),result=await response.json();if(result.success&&result.messages&&result.messages.length>0){result.messages.forEach(msg=>{const isMe=msg.user_id==={{ auth()->id() }};let fileHtml='';if(msg.file_path){const fileUrl='/storage/'+msg.file_path;if(msg.file_type==='image')fileHtml=`<img src="${fileUrl}" class="rounded-lg max-w-full mb-2">`;else if(msg.file_type==='video')fileHtml=`<video controls class="rounded-lg max-w-full mb-2"><source src="${fileUrl}" type="video/mp4"></video>`;else if(msg.file_type==='audio')fileHtml=`<div class="voice-message flex items-center gap-2 bg-black/20 rounded-lg p-2 pr-3"><button onclick="toggleVoicePlayback(this)" class="play-btn w-8 h-8 flex items-center justify-center bg-purple-500/30 hover:bg-purple-500/50 rounded-full transition-colors flex-shrink-0"><svg class="w-4 h-4 text-white play-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg><svg class="w-4 h-4 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg></button><div class="flex-1 min-w-0"><div class="voice-waveform h-6 flex items-center gap-0.5">${Array(30).fill(0).map(()=>`<div class="w-0.5 bg-purple-400/60 rounded-full" style="height:${Math.floor(Math.random()*80+20)}%"></div>`).join('')}</div></div><span class="text-xs text-white/50 ml-2">${new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})}</span><audio class="hidden" src="${fileUrl}"></audio></div>`}const messageHtml=`<div class="flex ${isMe?'justify-end':'justify-start'}"><div class="max-w-[70%] glass-card p-3 ${isMe?'bg-purple-500/20 border-purple-500/30':''}">${fileHtml}${msg.message?`<p class="text-sm whitespace-pre-wrap">${escapeHtml(msg.message)}</p>`:''}<div class="text-[10px] text-white/40 mt-1 text-left message-time">${new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})}</div></div></div>`;messagesContainer.insertAdjacentHTML('beforeend',messageHtml);lastMessageId=msg.id});messagesContainer.scrollTop=messagesContainer.scrollHeight}}catch(error){console.error('Error fetching new messages:',error)}},2000);
let mediaRecorder,audioChunks=[],isRecording=false,recordingStartTime,durationInterval;if(voiceRecordBtn){voiceRecordBtn.addEventListener('click',async ()=>{if(!isRecording){try{const stream=await navigator.mediaDevices.getUserMedia({audio:true});mediaRecorder=new MediaRecorder(stream);audioChunks=[];mediaRecorder.ondataavailable=(event)=>{audioChunks.push(event.data)};mediaRecorder.onstop=()=>{const audioBlob=new Blob(audioChunks,{type:'audio/webm'}),audioUrl=URL.createObjectURL(audioBlob);previewAudio.src=audioUrl;voicePreview.classList.remove('hidden');recordingIndicator.classList.add('hidden');isRecording=false;voiceRecordBtn.classList.remove('text-red-400');clearInterval(durationInterval)};mediaRecorder.start();isRecording=true;recordingStartTime=Date.now();recordingIndicator.classList.remove('hidden');typingIndicator.classList.add('hidden');voiceRecordBtn.classList.add('text-red-400');durationInterval=setInterval(()=>{const elapsed=Math.floor((Date.now()-recordingStartTime)/1000),minutes=Math.floor(elapsed/60).toString().padStart(2,'0'),seconds=(elapsed%60).toString().padStart(2,'0');recordingDuration.textContent=`${minutes}:${seconds}`},1000)}catch(error){alert('دسترسی به میکروفون نیاز است')}}else{mediaRecorder.stop()}})}
let isPlayingPreview=false;playPreviewBtn.addEventListener('click',()=>{if(isPlayingPreview){previewAudio.pause();playPreviewBtn.querySelector('.play-icon').classList.remove('hidden');playPreviewBtn.querySelector('.pause-icon').classList.add('hidden');isPlayingPreview=false}else{previewAudio.play();playPreviewBtn.querySelector('.play-icon').classList.add('hidden');playPreviewBtn.querySelector('.pause-icon').classList.remove('hidden');isPlayingPreview=true}previewAudio.onended=()=>{playPreviewBtn.querySelector('.play-icon').classList.remove('hidden');playPreviewBtn.querySelector('.pause-icon').classList.add('hidden');isPlayingPreview=false}});
cancelVoiceBtn.addEventListener('click',()=>{voicePreview.classList.add('hidden');previewAudio.src='';previewAudio.load();isPlayingPreview=false;playPreviewBtn.querySelector('.play-icon').classList.remove('hidden');playPreviewBtn.querySelector('.pause-icon').classList.add('hidden');recordingDuration.textContent='00:00'});
sendVoiceBtn.addEventListener('click',async ()=>{if(!previewAudio.src)return;const blob=await fetch(previewAudio.src).then(r=>r.blob()),formData=new FormData();formData.append('userId','{{ $partner->id }}');formData.append('file',blob,'voice_message.webm');try{const response=await fetch(`/chat/{{ $partner->id }}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:formData}),result=await response.json();if(result.success){const msg=result.message,fileUrl='/storage/'+msg.file_path,messageHtml=`<div class="flex justify-end"><div class="max-w-[70%] glass-card p-3 bg-purple-500/20 border-purple-500/30"><div class="voice-message flex items-center gap-2 bg-black/20 rounded-lg p-2 pr-3"><button onclick="toggleVoicePlayback(this)" class="play-btn w-8 h-8 flex items-center justify-center bg-purple-500/30 hover:bg-purple-500/50 rounded-full transition-colors flex-shrink-0"><svg class="w-4 h-4 text-white play-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg><svg class="w-4 h-4 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg></button><div class="flex-1 min-w-0"><div class="voice-waveform h-6 flex items-center gap-0.5">${Array(30).fill(0).map(()=>`<div class="w-0.5 bg-purple-400/60 rounded-full" style="height:${Math.floor(Math.random()*80+20)}%"></div>`).join('')}</div></div><span class="text-xs text-white/50 ml-2">${new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})}</span><audio class="hidden" src="${fileUrl}"></audio></div><div class="text-[10px] text-white/40 mt-1 text-left message-time">${new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})}</div></div></div>`;messagesContainer.insertAdjacentHTML('beforeend',messageHtml);messagesContainer.scrollTop=messagesContainer.scrollHeight;voicePreview.classList.add('hidden');previewAudio.src='';previewAudio.load();isPlayingPreview=false;playPreviewBtn.querySelector('.play-icon').classList.remove('hidden');playPreviewBtn.querySelector('.pause-icon').classList.add('hidden');recordingDuration.textContent='00:00';lastMessageId=msg.id}}catch(error){alert('خطا در ارسال ویس')}});
chatForm.addEventListener('submit',async (e)=>{e.preventDefault();const formData=new FormData(chatForm),userId=formData.get('userId');try{const response=await fetch(`/chat/${userId}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:formData}),result=await response.json();if(result.success){const msg=result.message;let fileHtml='';if(msg.file_path){const fileUrl='/storage/'+msg.file_path;if(msg.file_type==='image')fileHtml=`<img src="${fileUrl}" class="rounded-lg max-w-full mb-2">`;else if(msg.file_type==='video')fileHtml=`<video controls class="rounded-lg max-w-full mb-2"><source src="${fileUrl}" type="video/mp4"></video>`;else if(msg.file_type==='audio')fileHtml=`<audio controls class="mb-2"><source src="${fileUrl}" type="audio/mpeg"></audio>`;else fileHtml=`<a href="${fileUrl}" target="_blank" class="text-xs text-cyan-300 hover:underline inline-flex items-center gap-1 mb-2">📎 دانلود فایل</a>`}const messageHtml=`<div class="flex justify-end"><div class="max-w-[70%] glass-card p-3 bg-purple-500/20 border-purple-500/30">${fileHtml}${msg.message?`<p class="text-sm whitespace-pre-wrap">${escapeHtml(msg.message)}</p>`:''}<div class="text-[10px] text-white/40 mt-1 text-left">${new Date().toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'})}</div></div></div>`;messagesContainer.insertAdjacentHTML('beforeend',messageHtml);messagesContainer.scrollTop=messagesContainer.scrollHeight;messageInput.value='';fileInput.value='';lastMessageId=msg.id}else{alert('❌ '+result.message)}}catch(error){alert('خطا در ارسال پیام')}});
function escapeHtml(text){const div=document.createElement('div');div.textContent=text;return div.innerHTML}
</script>
@endpush
@endsection