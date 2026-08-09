@extends('layouts.app')
@section('title', 'ساخت عادت جدید')
@section('content')
<div class="max-w-xl mx-auto">
    <div class="glass-card p-6 md:p-8">
        <h1 class="text-xl font-heading mb-1">🌱 ساخت عادت جدید</h1>
        <p class="text-white/50 text-sm mb-6">یک عادت ساده انتخاب کن.</p>

        <form action="{{ route('habits.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-2 text-white/70">ایموجی</label>
                <div class="flex gap-1.5 flex-wrap mb-2">
                    @foreach(['💪', '', '📚', '💧', '🧘', '✍️', '', '💻', '🏋️', '🧠', '🌱', ''] as $emoji)
                        <button type="button" class="emoji-btn text-xl p-2 rounded-lg hover:bg-white/10 transition border border-transparent hover:border-white/20" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                    @endforeach
                </div>
                <input type="text" name="emoji" id="emoji-input" value="💪" class="glass-input text-center text-xl" required maxlength="10">
            </div>

            <div>
                <label class="block text-xs font-medium mb-2 text-white/70">عنوان عادت</label>
                <input type="text" name="title" class="glass-input" placeholder="مثلاً: تمرین بدنسازی" required value="{{ old('title') }}">
            </div>

            <div>
                <label class="block text-xs font-medium mb-2 text-white/70">چرا این عادت برات مهمه؟</label>
                <textarea name="description" class="glass-input" rows="2" placeholder="انگیزه‌ات چیه؟">{{ old('description') }}</textarea>
            </div>

            <div class="glass-card p-4 bg-gradient-to-br from-purple-500/10 to-pink-500/10 border-purple-500/30">
                <h3 class="font-heading text-sm mb-2 flex items-center gap-2"><span>✨</span> قانون دو حالته</h3>
                <p class="text-xs text-white/60 leading-relaxed">
                    ⚡ <strong class="text-cyan-300">میکرو ( دقیقه):</strong> حفظ ارتباط در روزهای شلوغ.
                    <br>💪 <strong class="text-emerald-300">کامل:</strong> روزهای عالی با تمرکز.
                    <br><span class="text-yellow-300">🎯 یک روز استراحت مجازه، دو روز پشت سر هم نه!</span>
                </p>
            </div>

            <div class="flex gap-3 pt-3">
                <button type="submit" class="btn-primary flex-1">شروع مسیر</button>
                <a href="{{ route('habits.index') }}" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', () => { document.getElementById('emoji-input').value = btn.dataset.emoji; });
    });
</script>
@endpush
@endsection
