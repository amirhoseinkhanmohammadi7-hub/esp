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
     * تحلیل کیفیت خواب بر اساس چرخه‌های خواب و نمره کیفیت خواب کاربر
     * هر چرخه خواب حدود 90 دقیقه است
     * خواب ایده‌آل: 5-6 چرخه (7.5-9 ساعت)
     * نمره کیفیت خواب کاربر هم در محاسبه نهایی دخیل می‌شود
     */
    public function analyzeSleepCycles(): array {
        if (!$this->sleep_duration_minutes && !$this->sleep_quality) {
            return [
                'cycles' => 0,
                'quality_score' => 0,
                'quality_label' => 'نامشخص',
                'quality_description' => 'زمان خواب و بیداری ثبت نشده است',
                'recommendation' => 'لطفاً زمان خواب و بیداری خود را وارد کنید',
                'color' => 'gray'
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
            $color = 'green';
        } elseif ($cycles == 4) {
            // خواب خوب (6 ساعت)
            $durationScore = 75;
            $durationLabel = 'خوب';
            $durationDescription = 'مدت خواب قابل قبولی داشتید';
            $durationRecommendation = 'سعی کنید 1-2 چرخه دیگر هم بخوابید';
            $color = 'blue';
        } elseif ($cycles == 3) {
            // خواب متوسط (4.5 ساعت)
            $durationScore = 50;
            $durationLabel = 'متوسط';
            $durationDescription = 'خواب شما کمتر از حد توصیه شده است';
            $durationRecommendation = 'سعی کنید زودتر بخوابید تا حداقل 5 چرخه کامل شود';
            $color = 'yellow';
        } elseif ($cycles < 3 && $cycles > 0) {
            // خواب کم
            $durationScore = 25;
            $durationLabel = 'ضعیف';
            $durationDescription = 'خواب شما بسیار کم است';
            $durationRecommendation = 'برای سلامتی بیشتر بخوابید (حداقل 6-7 ساعت)';
            $color = 'red';
        } elseif ($cycles > 6) {
            // خواب زیاد (بیش از 6 چرخه)
            $durationScore = 60;
            $durationLabel = 'زیاد';
            $durationDescription = 'خواب شما بیشتر از حد معمول است';
            $durationRecommendation = 'خواب بیش از حد هم می‌تواند باعث خستگی شود';
            $color = 'orange';
        } else {
            // اگر مدت خواب ثبت نشده
            $durationScore = 0;
            $durationLabel = 'ثبت نشده';
            $durationDescription = 'مدت خواب ثبت نشده است';
            $durationRecommendation = 'زمان خواب و بیداری خود را وارد کنید';
            $color = 'gray';
        }

        // محاسبه امتیاز کیفیت خواب کاربر (1-5 ستاره -> 20-100)
        $qualityScoreFromUser = $this->sleep_quality ? ($this->sleep_quality * 20) : 0;
        
        // ترکیب امتیاز مدت خواب و کیفیت خواب کاربر
        // اگر هر دو موجود باشند، میانگین می‌گیریم
        if ($this->sleep_duration_minutes && $this->sleep_quality) {
            // ترکیب 60% مدت خواب + 40% کیفیت خواب کاربر
            $finalScore = round(($durationScore * 0.6) + ($qualityScoreFromUser * 0.4));
            
            // تنظیم رنگ بر اساس امتیاز نهایی
            if ($finalScore >= 80) {
                $color = 'green';
                $qualityLabel = 'عالی';
                $qualityDescription = "خواب عالی داشتید! مدت خواب: {$durationLabel}، کیفیت احساسی: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'همین روند عالی را ادامه دهید 👏';
            } elseif ($finalScore >= 60) {
                $color = 'blue';
                $qualityLabel = 'خوب';
                $qualityDescription = "خواب خوبی داشتید. مدت خواب: {$durationLabel}، کیفیت احساسی: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = $durationRecommendation;
            } elseif ($finalScore >= 40) {
                $color = 'yellow';
                $qualityLabel = 'متوسط';
                $qualityDescription = "خواب متوسطی داشتید. مدت خواب: {$durationLabel}، کیفیت احساسی: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'سعی کنید هم مدت خواب و هم کیفیت آن را بهبود بخشید';
            } elseif ($finalScore >= 20) {
                $color = 'orange';
                $qualityLabel = 'ضعیف';
                $qualityDescription = "خواب ضعیفی داشتید. مدت خواب: {$durationLabel}، کیفیت احساسی: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'به برنامه خواب منظم‌تری نیاز دارید';
            } else {
                $color = 'red';
                $qualityLabel = 'بسیار ضعیف';
                $qualityDescription = "خواب بسیار ضعیفی داشتید. مدت خواب: {$durationLabel}، کیفیت احساسی: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'برای بهبود خواب خود اقدام کنید';
            }
        } elseif ($this->sleep_duration_minutes) {
            // فقط مدت خواب موجود است
            $finalScore = $durationScore;
            $qualityLabel = $durationLabel;
            $qualityDescription = $durationDescription . ' (کیفیت احساسی ثبت نشده)';
            $recommendation = $durationRecommendation;
        } else {
            // فقط کیفیت خواب موجود است
            $finalScore = $qualityScoreFromUser;
            $cycles = 0;
            
            if ($finalScore >= 80) {
                $color = 'green';
                $qualityLabel = 'عالی';
                $qualityDescription = "کیفیت خواب احساسی عالی بود: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'عالی است! حالا سعی کنید مدت خواب مناسبی هم داشته باشید';
            } elseif ($finalScore >= 60) {
                $color = 'blue';
                $qualityLabel = 'خوب';
                $qualityDescription = "کیفیت خواب احساسی خوبی داشتید: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'خوب است! مدت خواب مناسب هم اضافه کنید';
            } elseif ($finalScore >= 40) {
                $color = 'yellow';
                $qualityLabel = 'متوسط';
                $qualityDescription = "کیفیت خواب احساسی متوسط: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'سعی کنید کیفیت و مدت خواب را بهبود بخشید';
            } else {
                $color = 'red';
                $qualityLabel = 'ضعیف';
                $qualityDescription = "کیفیت خواب احساسی ضعیف: " . str_repeat('⭐', $this->sleep_quality);
                $recommendation = 'به بهبود کیفیت خواب خود توجه کنید';
            }
        }

        return [
            'cycles' => $cycles,
            'quality_score' => $finalScore,
            'quality_label' => $qualityLabel,
            'quality_description' => $qualityDescription,
            'recommendation' => $recommendation,
            'color' => $color,
            'duration_score' => $durationScore,
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
