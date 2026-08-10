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
        'sleep_duration_minutes' => 'integer',
    ];

    /**
     * مقادیر مجاز کیفیت خواب
     */
    public static function qualityOptions(): array {
        return [
            'very_poor' => 'خیلی بد 😫',
            'poor' => 'بد 😴',
            'fair' => 'متوسط 😐',
            'good' => 'خوب 🙂',
            'excellent' => 'خیلی خوب 🌟'
        ];
    }

    /**
     * امتیاز عددی برای هر کیفیت خواب
     */
    public static function qualityScore(string $quality): int {
        return match($quality) {
            'very_poor' => 1,
            'poor' => 2,
            'fair' => 3,
            'good' => 4,
            'excellent' => 5,
            default => 0
        };
    }

    /**
     * دریافت لیبل فارسی کیفیت خواب
     */
    public function getQualityLabel(): string {
        if (!$this->sleep_quality) {
            return 'ثبت نشده';
        }
        return self::qualityOptions()[$this->sleep_quality] ?? 'نامشخص';
    }

    /**
     * رنگ مربوط به هر کیفیت خواب
     */
    public function getQualityColor(): string {
        return match($this->sleep_quality) {
            'excellent' => 'green',
            'good' => 'blue',
            'fair' => 'yellow',
            'poor' => 'orange',
            'very_poor' => 'red',
            default => 'gray'
        };
    }

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
     * میانگین کیفیت خواب در X روز اخیر (به صورت امتیاز عددی)
     */
    public static function getAverageSleepQualityScore(int $userId, int $days = 7): ?float {
        $logs = self::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->whereNotNull('sleep_quality')
            ->get();
        
        if ($logs->isEmpty()) {
            return null;
        }
        
        $totalScore = 0;
        foreach ($logs as $log) {
            $totalScore += self::qualityScore($log->sleep_quality);
        }
        
        return round($totalScore / $logs->count(), 1);
    }

    /**
     * تحلیل کیفیت خواب بر اساس چرخه‌های خواب و زمان خواب
     * هر چرخه خواب حدود 90 دقیقه است
     * خواب ایده‌آل: 5-6 چرخه (7.5-9 ساعت)
     * زمان خوابیدن هم مهم است (خوابیدن قبل از نیمه شب بهتر است)
     */
    public function analyzeSleepCycles(): array {
        if (!$this->sleep_duration_minutes && !$this->bedtime) {
            return [
                'cycles' => 0,
                'quality_score' => 0,
                'quality_label' => 'نامشخص',
                'quality_description' => 'زمان خواب و بیداری ثبت نشده است',
                'recommendation' => 'لطفاً زمان خواب و بیداری خود را وارد کنید',
                'color' => 'gray',
                'bedtime_score' => 0,
                'duration_score' => 0
            ];
        }

        // محاسبه امتیاز بر اساس چرخه‌های خواب
        $cycleMinutes = 90;
        $cycles = $this->sleep_duration_minutes ? floor($this->sleep_duration_minutes / $cycleMinutes) : 0;
        
        // تعیین کیفیت بر اساس تعداد چرخه‌ها (امتیاز 0-100)
        if ($cycles >= 5 && $cycles <= 6) {
            // خواب عالی (7.5 تا 9 ساعت)
            $durationScore = 100;
            $durationLabel = 'عالی';
            $durationDescription = 'مدت خواب شما در محدوده ایده‌آل قرار دارد!';
            $durationRecommendation = 'همین روند را ادامه دهید 👏';
        } elseif ($cycles == 4) {
            // خواب خوب (6 ساعت)
            $durationScore = 75;
            $durationLabel = 'خوب';
            $durationDescription = 'مدت خواب قابل قبولی داشتید';
            $durationRecommendation = 'سعی کنید 1-2 چرخه دیگر هم بخوابید';
        } elseif ($cycles == 3) {
            // خواب متوسط (4.5 ساعت)
            $durationScore = 50;
            $durationLabel = 'متوسط';
            $durationDescription = 'خواب شما کمتر از حد توصیه شده است';
            $durationRecommendation = 'سعی کنید زودتر بخوابید تا حداقل 5 چرخه کامل شود';
        } elseif ($cycles < 3 && $cycles > 0) {
            // خواب کم
            $durationScore = 25;
            $durationLabel = 'ضعیف';
            $durationDescription = 'خواب شما بسیار کم است';
            $durationRecommendation = 'برای سلامتی بیشتر بخوابید (حداقل 6-7 ساعت)';
        } elseif ($cycles > 6) {
            // خواب زیاد (بیش از 6 چرخه)
            $durationScore = 60;
            $durationLabel = 'زیاد';
            $durationDescription = 'خواب شما بیشتر از حد معمول است';
            $durationRecommendation = 'خواب بیش از حد هم می‌تواند باعث خستگی شود';
        } else {
            // اگر مدت خواب ثبت نشده
            $durationScore = 0;
            $durationLabel = 'ثبت نشده';
            $durationDescription = 'مدت خواب ثبت نشده است';
            $durationRecommendation = 'زمان خواب و بیداری خود را وارد کنید';
        }

        // محاسبه امتیاز زمان خواب (bedtime score)
        // خوابیدن بین 21:00 تا 23:00 بهترین زمان است
        $bedtimeScore = 0;
        $bedtimeLabel = '';
        if ($this->bedtime) {
            $bedHour = (int) substr($this->bedtime, 0, 2);
            
            if ($bedHour >= 21 && $bedHour <= 23) {
                // بهترین زمان خواب (9-11 شب)
                $bedtimeScore = 100;
                $bedtimeLabel = 'زمان عالی برای خواب';
            } elseif ($bedHour >= 19 && $bedHour < 21) {
                // زود ولی خوب (7-9 شب)
                $bedtimeScore = 85;
                $bedtimeLabel = 'زمان خوبی برای خواب';
            } elseif ($bedHour == 0 || $bedHour == 24) {
                // نیمه شب
                $bedtimeScore = 70;
                $bedtimeLabel = 'زمان قابل قبول';
            } elseif ($bedHour >= 1 && $bedHour <= 2) {
                // 1-2 صبح - دیر ولی هنوز قابل قبول
                $bedtimeScore = 50;
                $bedtimeLabel = 'کمی دیر';
            } elseif ($bedHour >= 3 && $bedHour <= 5) {
                // 3-5 صبح - خیلی دیر
                $bedtimeScore = 25;
                $bedtimeLabel = 'خیلی دیر';
            } else {
                // سایر مواقع
                $bedtimeScore = 40;
                $bedtimeLabel = 'زمان نامناسب';
            }
        }

        // ترکیب امتیازات: 50% مدت خواب + 30% زمان خواب + 20% کیفیت احساسی
        $qualityScoreFromUser = $this->sleep_quality ? (self::qualityScore($this->sleep_quality) * 20) : 0;
        
        if ($this->sleep_duration_minutes && $this->bedtime) {
            // ترکیب نهایی: 50% مدت + 30% زمان + 20% کیفیت احساسی
            $finalScore = round(($durationScore * 0.5) + ($bedtimeScore * 0.3) + ($qualityScoreFromUser * 0.2));
            
            // تنظیم رنگ و لیبل بر اساس امتیاز نهایی
            if ($finalScore >= 80) {
                $color = 'green';
                $qualityLabel = 'عالی';
                $qualityDescription = "خواب عالی داشتید! مدت: {$durationLabel}, زمان خواب: {$bedtimeLabel}";
                $recommendation = 'همین روند عالی را ادامه دهید 👏';
            } elseif ($finalScore >= 60) {
                $color = 'blue';
                $qualityLabel = 'خوب';
                $qualityDescription = "خواب خوبی داشتید. مدت: {$durationLabel}, زمان خواب: {$bedtimeLabel}";
                $recommendation = $durationRecommendation;
            } elseif ($finalScore >= 40) {
                $color = 'yellow';
                $qualityLabel = 'متوسط';
                $qualityDescription = "خواب متوسطی داشتید. مدت: {$durationLabel}, زمان خواب: {$bedtimeLabel}";
                $recommendation = 'سعی کنید هم مدت خواب و هم زمان آن را بهبود بخشید';
            } elseif ($finalScore >= 20) {
                $color = 'orange';
                $qualityLabel = 'ضعیف';
                $qualityDescription = "خواب ضعیفی داشتید. مدت: {$durationLabel}, زمان خواب: {$bedtimeLabel}";
                $recommendation = 'به برنامه خواب منظم‌تری نیاز دارید';
            } else {
                $color = 'red';
                $qualityLabel = 'بسیار ضعیف';
                $qualityDescription = "خواب بسیار ضعیفی داشتید. مدت: {$durationLabel}, زمان خواب: {$bedtimeLabel}";
                $recommendation = 'برای بهبود خواب خود اقدام کنید';
            }
        } elseif ($this->sleep_duration_minutes) {
            // فقط مدت خواب موجود است
            $finalScore = $durationScore;
            $qualityLabel = $durationLabel;
            $qualityDescription = $durationDescription . ' (زمان خواب ثبت نشده)';
            $recommendation = $durationRecommendation;
        } else {
            // فقط زمان خواب موجود است
            $finalScore = $bedtimeScore;
            $cycles = 0;
            $qualityLabel = $bedtimeLabel ?: 'ثبت نشده';
            $qualityDescription = "زمان خواب: {$bedtimeLabel} (مدت خواب ثبت نشده)";
            $recommendation = 'زمان بیداری خود را هم وارد کنید تا تحلیل کامل‌تری دریافت کنید';
        }

        return [
            'cycles' => $cycles,
            'quality_score' => $finalScore,
            'quality_label' => $qualityLabel,
            'quality_description' => $qualityDescription,
            'recommendation' => $recommendation,
            'color' => $color,
            'duration_score' => $durationScore,
            'bedtime_score' => $bedtimeScore,
            'user_quality_score' => $qualityScoreFromUser
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
