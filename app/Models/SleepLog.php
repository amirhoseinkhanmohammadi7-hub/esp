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
     * بررسی اینکه آیا کاربر خواب دیشب را ثبت کرده است
     * (برای روز جدید - یعنی کاربر بیدار شده و باید خواب دیشب را ثبت کند)
     */
    public static function shouldRemindToLogSleep(int $userId): bool {
        // بررسی می‌کنیم که آیا امروز لاگ خواب وجود دارد یا نه
        $todayLog = self::getTodayLog($userId);
        
        // اگر لاگ امروز وجود ندارد، باید یادآوری شود
        if (!$todayLog) {
            return true;
        }
        
        // اگر لاگ وجود دارد اما زمان خواب و بیداری ثبت نشده، باز هم یادآوری شود
        if (!$todayLog->bedtime || !$todayLog->wake_time) {
            return true;
        }
        
        return false;
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

    /**
     * تحلیل کیفیت خواب بر اساس چرخه‌های خواب
     * هر چرخه خواب حدود 90 دقیقه است
     * خواب ایده‌آل: 5-6 چرخه (7.5-9 ساعت)
     */
    public function analyzeSleepCycles(): array {
        if (!$this->sleep_duration_minutes) {
            return [
                'cycles' => 0,
                'quality_score' => 0,
                'quality_label' => 'نامشخص',
                'quality_description' => 'زمان خواب و بیداری ثبت نشده است',
                'recommendation' => 'لطفاً زمان خواب و بیداری خود را وارد کنید',
                'color' => 'gray'
            ];
        }

        // هر چرخه خواب حدود 90 دقیقه
        $cycleMinutes = 90;
        $cycles = floor($this->sleep_duration_minutes / $cycleMinutes);
        
        // تعیین کیفیت بر اساس تعداد چرخه‌ها
        if ($cycles >= 5 && $cycles <= 6) {
            // خواب عالی (7.5 تا 9 ساعت)
            $qualityScore = 100;
            $qualityLabel = 'عالی';
            $qualityDescription = 'خواب شما در محدوده ایده‌آل قرار دارد!';
            $recommendation = 'همین روند را ادامه دهید 👏';
            $color = 'green';
        } elseif ($cycles == 4) {
            // خواب خوب (6 ساعت)
            $qualityScore = 75;
            $qualityLabel = 'خوب';
            $qualityDescription = 'خواب قابل قبولی داشتید';
            $recommendation = 'سعی کنید 1-2 چرخه دیگر هم بخوابید';
            $color = 'blue';
        } elseif ($cycles == 3) {
            // خواب متوسط (4.5 ساعت)
            $qualityScore = 50;
            $qualityLabel = 'متوسط';
            $qualityDescription = 'خواب شما کمتر از حد توصیه شده است';
            $recommendation = 'سعی کنید زودتر بخوابید تا حداقل 5 چرخه کامل شود';
            $color = 'yellow';
        } elseif ($cycles < 3) {
            // خواب کم
            $qualityScore = 25;
            $qualityLabel = 'ضعیف';
            $qualityDescription = 'خواب شما بسیار کم است';
            $recommendation = 'برای سلامتی بیشتر بخوابید (حداقل 6-7 ساعت)';
            $color = 'red';
        } else {
            // خواب زیاد (بیش از 6 چرخه)
            $qualityScore = 60;
            $qualityLabel = 'زیاد';
            $qualityDescription = 'خواب شما بیشتر از حد معمول است';
            $recommendation = 'خواب بیش از حد هم می‌تواند باعث خستگی شود';
            $color = 'orange';
        }

        return [
            'cycles' => $cycles,
            'quality_score' => $qualityScore,
            'quality_label' => $qualityLabel,
            'quality_description' => $qualityDescription,
            'recommendation' => $recommendation,
            'color' => $color
        ];
    }

    /**
     * دریافت درصد تکمیل چرخه فعلی
     */
    public function getCurrentCycleProgress(): float {
        if (!$this->sleep_duration_minutes) {
            return 0;
        }
        
        $cycleMinutes = 90;
        $progress = ($this->sleep_duration_minutes % $cycleMinutes) / $cycleMinutes * 100;
        
        return round($progress, 0);
    }
}
