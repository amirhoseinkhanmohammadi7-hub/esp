<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Habit extends Model {
    protected $fillable = ['user_id', 'title', 'description', 'emoji', 'share_token', 'is_public', 'is_completed', 'completion_story', 'completed_at'];
    protected $casts = ['completed_at' => 'date'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($habit) {
            if (empty($habit->share_token)) $habit->share_token = Str::random(32);
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function logs(): HasMany { return $this->hasMany(HabitLog::class)->orderBy('log_date', 'desc'); }
    public function signatures(): HasMany { return $this->hasMany(Signature::class)->latest(); }
    public function achievements(): HasMany { return $this->hasMany(Achievement::class); }

    public function getShareUrlAttribute(): string { return route('habits.share', $this->share_token); }

    public function getCurrentStreak(): int {
        $logs = $this->logs()->where('type', '!=', 'missed')->pluck('log_date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        if (empty($logs)) return 0;
        $streak = 0;
        $currentDate = Carbon::today();
        $missedDays = 0;
        while ($missedDays < 2) {
            $dateStr = $currentDate->toDateString();
            if (in_array($dateStr, $logs)) {
                $streak++;
                $missedDays = 0;
            } else {
                $missedDays++;
                if ($missedDays >= 2) break;
            }
            $currentDate = $currentDate->subDay();
            if ($streak > 3650) break;
        }
        return $streak;
    }

    public function getFullCount(): int { return $this->logs()->where('type', 'full')->count(); }
    public function getMicroCount(): int { return $this->logs()->where('type', 'micro')->count(); }
    public function getMissedCount(): int { return $this->logs()->where('type', 'missed')->count(); }
    public function hasLoggedToday(): bool { return $this->logs()->where('log_date', Carbon::today())->exists(); }
    public function getTodayLog() { return $this->logs()->where('log_date', Carbon::today())->first(); }

    public function getSuccessRate(int $days = 30): float {
        $startDate = Carbon::today()->subDays($days - 1);
        $total = $this->logs()->where('log_date', '>=', $startDate)->count();
        $success = $this->logs()->where('log_date', '>=', $startDate)->where('type', '!=', 'missed')->count();
        return $total > 0 ? round(($success / $total) * 100, 1) : 0;
    }

    public function getChartData(string $period = 'month'): array {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'six_months' => 180,
            'year' => 365,
            default => 30,
        };
        $logs = $this->logs()->where('log_date', '>=', Carbon::today()->subDays($days - 1))->orderBy('log_date')->get();
        $data = [];
        $currentDate = Carbon::today()->subDays($days - 1);
        $logsByDate = $logs->keyBy(fn($l) => $l->log_date->format('Y-m-d'));
        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->toDateString();
            $log = $logsByDate[$dateStr] ?? null;
            $data[] = [
                'date' => $dateStr,
                'label' => $currentDate->format('m/d'),
                'type' => $log ? $log->type : 'none',
                'value' => $log ? ($log->type === 'full' ? 100 : ($log->type === 'micro' ? 50 : 0)) : 0,
            ];
            $currentDate->addDay();
        }
        return $data;
    }

    public function markAsCompleted(string $story): void {
        $this->update([
            'is_completed' => true,
            'completion_story' => $story,
            'completed_at' => Carbon::today(),
        ]);
        Achievement::create([
            'user_id' => $this->user_id,
            'habit_id' => $this->id,
            'title' => 'تکمیل عادت',
            'icon' => '🏆',
            'description' => $story,
            'type' => 'completion',
        ]);
    }
}
