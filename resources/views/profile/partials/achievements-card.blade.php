{{-- Achievements Card - Modal Version --}}
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-heading text-white flex items-center gap-2">
            <span class="text-yellow-400">🏆</span> دستاوردها
        </h2>
        <button onclick="closeModal(null, 'achievements-modal')" class="text-white/50 hover:text-white transition-colors">
            <span class="text-2xl">×</span>
        </button>
    </div>
    
    @if($user->achievements->isEmpty())
        <div class="text-center py-8">
            <div class="text-5xl mb-3 opacity-50">🏅</div>
            <p class="text-white/50 text-sm font-quote">هنوز دستاوردی کسب نکردی</p>
            <p class="text-white/30 text-xs mt-1">به مسیرت ادامه بده!</p>
        </div>
    @else
        <div class="space-y-3 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
            @foreach($user->achievements->sortByDesc('created_at') as $achievement)
                <div class="group p-4 bg-white/5 hover:bg-white/10 rounded-xl border border-white/10 hover:border-purple-500/30 transition-all duration-300">
                    <div class="flex items-start gap-3">
                        <div class="text-3xl group-hover:scale-110 transition-transform duration-300">
                            {{ $achievement->icon ?? '🏆' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading text-sm text-white group-hover:text-purple-300 transition-colors">
                                {{ $achievement->title }}
                            </h3>
                            @if($achievement->description)
                                <p class="text-xs text-white/60 mt-1 font-quote line-clamp-2">
                                    "{{ $achievement->description }}"
                                </p>
                            @endif
                            <p class="text-[10px] text-white/40 mt-2 flex items-center gap-1">
                                <span>📅</span>
                                {{ $achievement->created_at ? $achievement->created_at->format('Y/m/d') : 'نامشخص' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($user->achievements->count() > 5)
            <button onclick="toggleAllAchievements()" class="w-full mt-4 btn-secondary text-xs">
                <span id="toggle_text">نمایش همه ({{ $user->achievements->count() }})</span>
            </button>
        @endif
    @endif
</div>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(168, 85, 247, 0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(168, 85, 247, 0.5);
}
</style>

<script>
function toggleAllAchievements() {
    const container = document.querySelector('.custom-scrollbar');
    const toggleText = document.getElementById('toggle_text');
    
    if (container.style.maxHeight === 'none') {
        container.style.maxHeight = '24rem';
        toggleText.textContent = 'نمایش همه (' + {{ $user->achievements->count() }} + ')';
    } else {
        container.style.maxHeight = 'none';
        toggleText.textContent = 'نمایش کمتر';
    }
}

// Initialize with limited height
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.custom-scrollbar');
    if (container && {{ $user->achievements->count() }} > 5) {
        container.style.maxHeight = '24rem';
    }
});
</script>
