<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\SleepAnalysisService;

class SleepLog extends Model 
{
    /**
     * Quality options mapping
     */
    private const QUALITY_OPTIONS = [
        'very_poor' => 'خیلی بد 😫',
        'poor' => 'بد 😴',
        'fair' => 'متوسط 😐',
        'good' => 'خوب 🙂',
        'excellent' => 'خیلی خوب 🌟',
    ];

    /**
     * Color mapping for quality levels
     */
    private const QUALITY_COLORS = [
        'excellent' => 'green',
        'good' => 'blue',
        'fair' => 'yellow',
        'poor' => 'orange',
        'very_poor' => 'red',
    ];

    protected $fillable = [
        'user_id', 
        'log_date', 
        'bedtime', 
        'wake_time', 
        'sleep_duration_minutes', 
        'sleep_quality',
        'note',
    ];
    
    protected $casts = [
        'log_date' => 'date',
        'sleep_duration_minutes' => 'integer',
    ];

    /**
     * Get the user associated with this sleep log
     */
    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get allowed quality options
     */
    public static function qualityOptions(): array 
    {
        return self::QUALITY_OPTIONS;
    }

    /**
     * Get quality label for this log
     */
    public function getQualityLabel(): string 
    {
        if (!$this->sleep_quality) {
            return 'ثبت نشده';
        }

        return self::QUALITY_OPTIONS[$this->sleep_quality] ?? 'نامشخص';
    }

    /**
     * Get quality color for this log
     */
    public function getQualityColor(): string 
    {
        return self::QUALITY_COLORS[$this->sleep_quality] ?? 'gray';
    }

    /**
     * Get formatted sleep duration (hours and minutes)
     */
    public function getFormattedDuration(): string 
    {
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
     * Analyze sleep cycles using the analysis service
     */
    public function analyzeSleepCycles(): array 
    {
        $analysisService = app(SleepAnalysisService::class);
        return $analysisService->analyzeSleepCycles($this);
    }

    /**
     * Get current sleep cycle progress percentage
     */
    public function getCurrentCycleProgress(): float 
    {
        $analysisService = app(SleepAnalysisService::class);
        return $analysisService->getCycleProgress($this->sleep_duration_minutes);
    }
}
