<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SleepLog extends Model {
    protected $fillable = [
        'user_id', 
        'log_date', 
        'bedtime', 
        'wake_time', 
        'sleep_duration_minutes', 
        'sleep_quality',
        'note'
    ];
    
    protected $casts = [
        'log_date' => 'date',
        'sleep_quality' => 'integer',
        'sleep_duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * محاسبه مدت خواب بر اساس زمان خواب و بیداری
     */
    public function calculateDuration(): ?int {
        if (!$this->bedtime || !$this->wake_time) {
            return null;
        }

        $bedDateTime = Carbon::parse($this->log_date . ' ' . $this->bedtime);
        $wakeDateTime = Carbon::parse($this->log_date . ' ' . $this->wake_time);

        // اگر زمان بیداری قبل از زمان خواب است، یعنی بیداری روز بعد بوده
        if ($wakeDateTime < $bedDateTime) {
            $wakeDateTime->addDay();
        }

        $duration = $bedDateTime->diffInMinutes($wakeDateTime);
        $this->sleep_duration_minutes = $duration;
        
        return $duration;
    }

    /**
     * دریافت فرمت شده مدت خواب (ساعت و دقیقه)
     */
    public function getFormattedDuration(): string {
        if (!$this->sleep_duration_minutes) {
            return '-';
        }

        $hours = floor($this->sleep_duration_minutes / 60);
        $minutes = $this->sleep_duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}س و {$minutes}د";
        }
        return "{$minutes}د";
    }

    /**
     * دریافت کیفیت خواب به صورت ستاره
     */
    public function getQualityStars(): string {
        if (!$this->sleep_quality) {
            return '';
        }
        
        return str_repeat('⭐', $this->sleep_quality);
    }

    /**
     * بررسی اینکه آیا امروز ثبت شده یا نه
     */
    public static function hasLoggedToday(int $userId): bool {
        return self::where('user_id', $userId)
            ->where('log_date', Carbon::today())
            ->exists();
    }

    /**
     * دریافت لاگ خواب امروز
     */
    public static function getTodayLog(int $userId): ?SleepLog {
        return self::where('user_id', $userId)
            ->where('log_date', Carbon::today())
            ->first();
    }

    /**
     * میانگین ساعت خواب در X روز اخیر
     */
    public static function getAverageSleepDuration(int $userId, int $days = 7): ?float {
        $avg = self::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->whereNotNull('sleep_duration_minutes')
            ->avg('sleep_duration_minutes');
        
        return $avg ? round($avg / 60, 1) : null; // تبدیل به ساعت
    }

    /**
     * میانگین کیفیت خواب در X روز اخیر
     */
    public static function getAverageSleepQuality(int $userId, int $days = 7): ?float {
        $avg = self::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->whereNotNull('sleep_quality')
            ->avg('sleep_quality');
        
        return $avg ? round($avg, 1) : null;
    }
}
