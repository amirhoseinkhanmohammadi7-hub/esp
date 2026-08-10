{{-- Stats Sidebar - Modal Version --}}
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-heading text-white flex items-center gap-2">
            <span class="text-pink-400">📊</span> آمار من
        </h2>
        <button onclick="closeModal(null, 'stats-modal')" class="text-white/50 hover:text-white transition-colors">
            <span class="text-2xl">×</span>
        </button>
    </div>
    
    <div class="space-y-4">
        {{-- Total Habits --}}
        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/20 to-purple-600/20 flex items-center justify-center">
                    <span class="text-xl">🎯</span>
                </div>
                <div>
                    <p class="text-xs text-white/60">عادت‌های فعال</p>
                    <p class="text-lg font-heading text-white">{{ $user->habits()->where('active', true)->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Completed Days --}}
        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 flex items-center justify-center">
                    <span class="text-xl">✅</span>
                </div>
                <div>
                    <p class="text-xs text-white/60">روزهای موفق</p>
                    @php
                        $totalLogs = $user->habits()->with('logs')->get()->sum(function($habit) {
                            return $habit->logs()->where('completed', true)->count();
                        });
                    @endphp
                    <p class="text-lg font-heading text-white">{{ number_format($totalLogs) }}</p>
                </div>
            </div>
        </div>

        {{-- Current Streak --}}
        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500/20 to-red-600/20 flex items-center justify-center">
                    <span class="text-xl">🔥</span>
                </div>
                <div>
                    <p class="text-xs text-white/60">زنجیره فعلی</p>
                    @php
                        $streak = 0;
                        $today = now()->startOfDay();
                        foreach($user->habits as $habit) {
                            $habitStreak = 0;
                            $checkDate = $today;
                            while(true) {
                                $log = $habit->logs()->whereDate('date', $checkDate)->where('completed', true)->first();
                                if ($log) {
                                    $habitStreak++;
                                    $checkDate = $checkDate->copy()->subDay();
                                } else {
                                    break;
                                }
                            }
                            $streak = max($streak, $habitStreak);
                        }
                    @endphp
                    <p class="text-lg font-heading streak-glow">{{ $streak }} روز</p>
                </div>
            </div>
        </div>

        {{-- Achievements Count --}}
        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500/20 to-amber-600/20 flex items-center justify-center">
                    <span class="text-xl">🏆</span>
                </div>
                <div>
                    <p class="text-xs text-white/60">دستاوردها</p>
                    <p class="text-lg font-heading text-white">{{ $user->achievements->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Member Since --}}
        <div class="pt-3 mt-3 border-t border-white/10">
            <p class="text-xs text-white/40 text-center">
                عضو از {{ $user->joined_at ? $user->joined_at->format('Y/m/d') : 'اخیراً' }}
            </p>
        </div>
    </div>
</div>
