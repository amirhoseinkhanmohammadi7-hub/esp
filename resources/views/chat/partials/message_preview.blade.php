@props(['message'])

@if($message->file_type === 'image')
    📷 {{ __('تصویر') }}
@elseif($message->file_type === 'video')
    🎥 {{ __('ویدیو') }}
@elseif($message->file_type === 'audio')
    🎵 {{ __('صوتی') }}
@elseif($message->file_type === 'file')
    📎 {{ __('فایل') }}
@else
    {{ Str::limit($message->message, 30) }}
@endif
