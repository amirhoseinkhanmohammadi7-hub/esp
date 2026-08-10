@extends('layouts.app')

@section('title', 'ردیاب خواب')

@section('content')
<div class="space-y-6" x-data="{ 
    showTodayModal: {{ $todayLog ? 'true' : 'false' }},
    editingLog: null
}">
    
    <!-- هدر صفحه -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-heading bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                ردیاب خواب 😴
            </h1>
            <p class="text-white/50 mt-1 text-sm">کیفیت خوابت رو دنبال کن</p>
        </div>
        <button @click="showTodayModal = true" class="btn-primary">
            🌙 ثبت خواب امروز
        </button>
    </div>

    <!-- کارت‌های آمار -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- میانگین مدت خواب -->
        <div class="glass-card p-5 text-center">
            <div class="text-3xl mb-2">⏱️</div>
            <div class="text-2xl font-heading streak-glow">
                {{ $avgDuration ?? '-' }}
            </div>
            <div class="text-xs text-white/50 mt-1">میانگین ساعت خواب (۷ روز)</div>
        </div>

        <!-- میانگین کیفیت خواب -->
        <div class="glass-card p-5 text-center">
            <div class="text-3xl mb-2">⭐</div>
            <div class="text-2xl font-heading text-yellow-300">
                {{ $avgQuality ?? '-' }}
            </div>
            <div class="text-xs text-white/50 mt-1">میانگین کیفیت (۷ روز)</div>
        </div>

        <!-- وضعیت امروز -->
        <div class="glass-card p-5 text-center">
            <div class="text-3xl mb-2">{{ $todayLog ? '✅' : '⏰' }}</div>
            <div class="text-sm font-heading text-white/80">
                {{ $todayLog ? 'ثبت شده' : 'ثبت نشده' }}
            </div>
            <div class="text-xs text-white/50 mt-1">خواب امروز</div>
        </div>
    </div>

    <!-- جدول لاگ‌های ۷ روز اخیر -->
    <div class="glass-card overflow-hidden">
        <div class="p-4 border-b border-white/10">
            <h2 class="text-sm font-heading text-white/80">📊 گزارش ۷ روز اخیر</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-white/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-normal">تاریخ</th>
                        <th class="px-4 py-3 text-right font-normal">زمان خواب</th>
                        <th class="px-4 py-3 text-right font-normal">زمان بیداری</th>
                        <th class="px-4 py-3 text-right font-normal">مدت</th>
                        <th class="px-4 py-3 text-right font-normal">کیفیت</th>
                        <th class="px-4 py-3 text-left font-normal">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($sleepLogs as $log)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-4 py-3 text-white/80">
                                {{ $log->log_date->format('Y/m/d') }}
                                @if($log->log_date->isToday())
                                    <span class="text-[10px] bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full mr-2">امروز</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-white/70">
                                {{ $log->bedtime ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-white/70">
                                {{ $log->wake_time ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-white/80 font-heading">
                                {{ $log->getFormattedDuration() }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs">{{ $log->getQualityStars() }}</span>
                            </td>
                            <td class="px-4 py-3 text-left">
                                <button @click="editingLog = {{ $log->id }}; showTodayModal = true" 
                                        class="text-white/50 hover:text-white transition text-xs">
                                    ✏️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-white/40 text-sm">
                                هنوز اطلاعاتی ثبت نکردی. اولین رکوردت رو اضافه کن!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال ثبت/ویرایش خواب -->
    <div x-show="showTodayModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @click.self="showTodayModal = false; editingLog = null">
        
        <div class="glass-card w-full max-w-md p-6 relative animate-fade-in">
            <!-- دکمه بستن -->
            <button @click="showTodayModal = false; editingLog = null"
                    class="absolute top-4 left-4 text-white/40 hover:text-white transition">
                ✕
            </button>

            <h3 class="text-lg font-heading text-white mb-4">
                <span x-text="editingLog ? '✏️ ویرایش خواب' : '🌙 ثبت خواب جدید'"></span>
            </h3>

            <form :action="editingLog ? '{{ route('sleep.update', '__ID__') }}'.replace('__ID__', editingLog) : '{{ route('sleep.store') }}'" 
                  method="POST" 
                  class="space-y-4">
                
                @csrf
                <input type="hidden" name="_method" :value="editingLog ? 'PUT' : 'POST">

                <!-- زمان خواب -->
                <div>
                    <label class="block text-xs text-white/60 mb-2">⏰ زمان خواب</label>
                    <input type="time" 
                           name="bedtime" 
                           value="{{ old('bedtime', $todayLog?->bedtime) }}"
                           class="glass-input"
                           placeholder="مثلاً 23:00">
                </div>

                <!-- زمان بیداری -->
                <div>
                    <label class="block text-xs text-white/60 mb-2">☀️ زمان بیداری</label>
                    <input type="time" 
                           name="wake_time" 
                           value="{{ old('wake_time', $todayLog?->wake_time) }}"
                           class="glass-input"
                           placeholder="مثلاً 07:00">
                </div>

                <!-- کیفیت خواب -->
                <div>
                    <label class="block text-xs text-white/60 mb-2">⭐ کیفیت خواب</label>
                    <div class="flex gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" 
                                       name="sleep_quality" 
                                       value="{{ $i }}"
                                       {{ old('sleep_quality', $todayLog?->sleep_quality) == $i ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="glass-input text-center peer-checked:bg-purple-500/20 peer-checked:border-purple-400/50 transition">
                                    {{ str_repeat('⭐', $i) }}
                                </div>
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- یادداشت -->
                <div>
                    <label class="block text-xs text-white/60 mb-2">📝 یادداشت (اختیاری)</label>
                    <textarea name="note" 
                              rows="2" 
                              class="glass-input resize-none"
                              placeholder="چطور خوابیدی؟">{{ old('note', $todayLog?->note) }}</textarea>
                </div>

                <!-- دکمه‌ها -->
                <div class="flex gap-3 pt-2">
                    <button type="button" 
                            @click="showTodayModal = false; editingLog = null"
                            class="flex-1 btn-secondary">
                        انصراف
                    </button>
                    <button type="submit" class="flex-1 btn-primary">
                        ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
// وقتی مودال باز میشه، اگر در حال ویرایش هستیم مقادیر را پر کن
document.addEventListener('alpine:init', () => {
    // Alpine.js handles the modal state
});
</script>
@endpush

@endsection
