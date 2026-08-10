<?php

namespace App\Services;

use App\Models\SleepLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SleepAnalysisService
{
    /**
     * Sleep cycle duration in minutes
     */
    private const CYCLE_MINUTES = 90;

    /**
     * Quality scores mapping
     */
    private const QUALITY_SCORES = [
        'very_poor' => 1,
        'poor' => 2,
        'fair' => 3,
        'good' => 4,
        'excellent' => 5,
    ];

    /**
     * Calculate sleep duration in minutes
     */
    public function calculateDuration(string $logDate, string $bedtime, string $wakeTime): int
    {
        $bedDateTime = Carbon::parse($logDate . ' ' . $bedtime);
        $wakeDateTime = Carbon::parse($logDate . ' ' . $wakeTime);

        // If wake time is before bedtime, it means waking up the next day
        if ($wakeDateTime < $bedDateTime) {
            $wakeDateTime->addDay();
        }

        return $bedDateTime->diffInMinutes($wakeDateTime);
    }

    /**
     * Format sleep duration to human readable string
     */
    public function formatDuration(?int $minutes): string
    {
        if (!$minutes) {
            return '-';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}س و {$remainingMinutes}د";
        }

        return "{$remainingMinutes}د";
    }

    /**
     * Get quality score for a given quality level
     */
    public function getQualityScore(string $quality): int
    {
        return self::QUALITY_SCORES[$quality] ?? 0;
    }

    /**
     * Analyze sleep cycles and provide comprehensive analysis
     */
    public function analyzeSleepCycles(SleepLog $log): array
    {
        if (!$log->sleep_duration_minutes && !$log->bedtime) {
            return $this->getEmptyAnalysis();
        }

        // Calculate cycles
        $cycles = $log->sleep_duration_minutes 
            ? floor($log->sleep_duration_minutes / self::CYCLE_MINUTES) 
            : 0;

        // Calculate duration score
        $durationAnalysis = $this->analyzeDuration($cycles);

        // Calculate bedtime score
        $bedtimeAnalysis = $this->analyzeBedtime($log->bedtime);

        // Calculate user-reported quality score (0-100 scale)
        $userQualityScore = $log->sleep_quality 
            ? ($this->getQualityScore($log->sleep_quality) * 20) 
            : 0;

        // Combine scores: 50% duration + 30% bedtime + 20% user quality
        if ($log->sleep_duration_minutes && $log->bedtime) {
            $finalScore = round(
                ($durationAnalysis['score'] * 0.5) + 
                ($bedtimeAnalysis['score'] * 0.3) + 
                ($userQualityScore * 0.2)
            );

            $result = $this->buildAnalysisResult(
                $finalScore,
                $cycles,
                $durationAnalysis,
                $bedtimeAnalysis,
                $userQualityScore
            );
        } elseif ($log->sleep_duration_minutes) {
            // Only duration available
            $finalScore = $durationAnalysis['score'];
            $result = $this->buildDurationOnlyResult(
                $finalScore,
                $cycles,
                $durationAnalysis
            );
        } else {
            // Only bedtime available
            $finalScore = $bedtimeAnalysis['score'];
            $result = $this->buildBedtimeOnlyResult(
                $finalScore,
                $bedtimeAnalysis
            );
        }

        return $result;
    }

    /**
     * Analyze sleep duration based on cycles
     */
    private function analyzeDuration(int $cycles): array
    {
        return match(true) {
            $cycles >= 5 && $cycles <= 6 => [
                'score' => 100,
                'label' => 'عالی',
                'description' => 'مدت خواب شما در محدوده ایده‌آل قرار دارد!',
                'recommendation' => 'همین روند را ادامه دهید 👏',
            ],
            $cycles === 4 => [
                'score' => 75,
                'label' => 'خوب',
                'description' => 'مدت خواب قابل قبولی داشتید',
                'recommendation' => 'سعی کنید ۱-۲ چرخه دیگر هم بخوابید',
            ],
            $cycles === 3 => [
                'score' => 50,
                'label' => 'متوسط',
                'description' => 'خواب شما کمتر از حد توصیه شده است',
                'recommendation' => 'سعی کنید زودتر بخوابید تا حداقل ۵ چرخه کامل شود',
            ],
            $cycles > 0 && $cycles < 3 => [
                'score' => 25,
                'label' => 'ضعیف',
                'description' => 'خواب شما بسیار کم است',
                'recommendation' => 'برای سلامتی بیشتر بخوابید (حداقل ۶-۷ ساعت)',
            ],
            $cycles > 6 => [
                'score' => 60,
                'label' => 'زیاد',
                'description' => 'خواب شما بیشتر از حد معمول است',
                'recommendation' => 'خواب بیش از حد هم می‌تواند باعث خستگی شود',
            ],
            default => [
                'score' => 0,
                'label' => 'ثبت نشده',
                'description' => 'مدت خواب ثبت نشده است',
                'recommendation' => 'زمان خواب و بیداری خود را وارد کنید',
            ],
        };
    }

    /**
     * Analyze bedtime quality
     */
    private function analyzeBedtime(?string $bedtime): array
    {
        if (!$bedtime) {
            return [
                'score' => 0,
                'label' => '',
            ];
        }

        $bedHour = (int) substr($bedtime, 0, 2);

        return match(true) {
            $bedHour >= 21 && $bedHour <= 23 => [
                'score' => 100,
                'label' => 'زمان عالی برای خواب',
            ],
            $bedHour >= 19 && $bedHour < 21 => [
                'score' => 85,
                'label' => 'زمان خوبی برای خواب',
            ],
            $bedHour === 0 || $bedHour === 24 => [
                'score' => 70,
                'label' => 'زمان قابل قبول',
            ],
            $bedHour >= 1 && $bedHour <= 2 => [
                'score' => 50,
                'label' => 'کمی دیر',
            ],
            $bedHour >= 3 && $bedHour <= 5 => [
                'score' => 25,
                'label' => 'خیلی دیر',
            ],
            default => [
                'score' => 40,
                'label' => 'زمان نامناسب',
            ],
        };
    }

    /**
     * Build complete analysis result
     */
    private function buildAnalysisResult(
        int $finalScore,
        int $cycles,
        array $durationAnalysis,
        array $bedtimeAnalysis,
        int $userQualityScore
    ): array {
        if ($finalScore >= 80) {
            return [
                'cycles' => $cycles,
                'quality_score' => $finalScore,
                'quality_label' => 'عالی',
                'quality_description' => "خواب عالی داشتید! مدت: {$durationAnalysis['label']}, زمان خواب: {$bedtimeAnalysis['label']}",
                'recommendation' => 'همین روند عالی را ادامه دهید 👏',
                'color' => 'green',
                'duration_score' => $durationAnalysis['score'],
                'bedtime_score' => $bedtimeAnalysis['score'],
                'user_quality_score' => $userQualityScore,
            ];
        }

        if ($finalScore >= 60) {
            return [
                'cycles' => $cycles,
                'quality_score' => $finalScore,
                'quality_label' => 'خوب',
                'quality_description' => "خواب خوبی داشتید. مدت: {$durationAnalysis['label']}, زمان خواب: {$bedtimeAnalysis['label']}",
                'recommendation' => $durationAnalysis['recommendation'],
                'color' => 'blue',
                'duration_score' => $durationAnalysis['score'],
                'bedtime_score' => $bedtimeAnalysis['score'],
                'user_quality_score' => $userQualityScore,
            ];
        }

        if ($finalScore >= 40) {
            return [
                'cycles' => $cycles,
                'quality_score' => $finalScore,
                'quality_label' => 'متوسط',
                'quality_description' => "خواب متوسطی داشتید. مدت: {$durationAnalysis['label']}, زمان خواب: {$bedtimeAnalysis['label']}",
                'recommendation' => 'سعی کنید هم مدت خواب و هم زمان آن را بهبود بخشید',
                'color' => 'yellow',
                'duration_score' => $durationAnalysis['score'],
                'bedtime_score' => $bedtimeAnalysis['score'],
                'user_quality_score' => $userQualityScore,
            ];
        }

        if ($finalScore >= 20) {
            return [
                'cycles' => $cycles,
                'quality_score' => $finalScore,
                'quality_label' => 'ضعیف',
                'quality_description' => "خواب ضعیفی داشتید. مدت: {$durationAnalysis['label']}, زمان خواب: {$bedtimeAnalysis['label']}",
                'recommendation' => 'به برنامه خواب منظم‌تری نیاز دارید',
                'color' => 'orange',
                'duration_score' => $durationAnalysis['score'],
                'bedtime_score' => $bedtimeAnalysis['score'],
                'user_quality_score' => $userQualityScore,
            ];
        }

        return [
            'cycles' => $cycles,
            'quality_score' => $finalScore,
            'quality_label' => 'بسیار ضعیف',
            'quality_description' => "خواب بسیار ضعیفی داشتید. مدت: {$durationAnalysis['label']}, زمان خواب: {$bedtimeAnalysis['label']}",
            'recommendation' => 'برای بهبود خواب خود اقدام کنید',
            'color' => 'red',
            'duration_score' => $durationAnalysis['score'],
            'bedtime_score' => $bedtimeAnalysis['score'],
            'user_quality_score' => $userQualityScore,
        ];
    }

    /**
     * Build result when only duration is available
     */
    private function buildDurationOnlyResult(int $finalScore, int $cycles, array $durationAnalysis): array
    {
        return [
            'cycles' => $cycles,
            'quality_score' => $finalScore,
            'quality_label' => $durationAnalysis['label'],
            'quality_description' => $durationAnalysis['description'] . ' (زمان خواب ثبت نشده)',
            'recommendation' => $durationAnalysis['recommendation'],
            'color' => $this->getColorForScore($finalScore),
            'duration_score' => $durationAnalysis['score'],
            'bedtime_score' => 0,
            'user_quality_score' => 0,
        ];
    }

    /**
     * Build result when only bedtime is available
     */
    private function buildBedtimeOnlyResult(int $finalScore, array $bedtimeAnalysis): array
    {
        return [
            'cycles' => 0,
            'quality_score' => $finalScore,
            'quality_label' => $bedtimeAnalysis['label'] ?: 'ثبت نشده',
            'quality_description' => "زمان خواب: {$bedtimeAnalysis['label']} (مدت خواب ثبت نشده)",
            'recommendation' => 'زمان بیداری خود را هم وارد کنید تا تحلیل کامل‌تری دریافت کنید',
            'color' => $this->getColorForScore($finalScore),
            'duration_score' => 0,
            'bedtime_score' => $bedtimeAnalysis['score'],
            'user_quality_score' => 0,
        ];
    }

    /**
     * Get empty analysis result
     */
    private function getEmptyAnalysis(): array
    {
        return [
            'cycles' => 0,
            'quality_score' => 0,
            'quality_label' => 'نامشخص',
            'quality_description' => 'زمان خواب و بیداری ثبت نشده است',
            'recommendation' => 'لطفاً زمان خواب و بیداری خود را وارد کنید',
            'color' => 'gray',
            'duration_score' => 0,
            'bedtime_score' => 0,
            'user_quality_score' => 0,
        ];
    }

    /**
     * Get color based on score
     */
    private function getColorForScore(int $score): string
    {
        return match(true) {
            $score >= 80 => 'green',
            $score >= 60 => 'blue',
            $score >= 40 => 'yellow',
            $score >= 20 => 'orange',
            default => 'red',
        };
    }

    /**
     * Calculate current sleep cycle progress percentage
     */
    public function getCycleProgress(?int $sleepDurationMinutes): float
    {
        if (!$sleepDurationMinutes) {
            return 0;
        }

        $progress = ($sleepDurationMinutes % self::CYCLE_MINUTES) / self::CYCLE_MINUTES * 100;

        return round($progress, 0);
    }
}
