<?php

namespace App\Services;

use App\Models\SleepLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SleepStatisticsService
{
    /**
     * Get average sleep duration for a user in the last N days (in hours)
     */
    public function getAverageSleepDuration(int $userId, int $days = 7): ?float
    {
        $avg = SleepLog::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->whereNotNull('sleep_duration_minutes')
            ->avg('sleep_duration_minutes');

        return $avg ? round($avg / 60, 1) : null;
    }

    /**
     * Get average sleep quality score for a user in the last N days
     */
    public function getAverageSleepQualityScore(int $userId, int $days = 7): ?float
    {
        $logs = SleepLog::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->whereNotNull('sleep_quality')
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $analysisService = app(SleepAnalysisService::class);
        $totalScore = 0;

        foreach ($logs as $log) {
            $totalScore += $analysisService->getQualityScore($log->sleep_quality);
        }

        return round($totalScore / $logs->count(), 1);
    }

    /**
     * Check if user has logged sleep today
     */
    public function hasLoggedToday(int $userId): bool
    {
        return SleepLog::where('user_id', $userId)
            ->where('log_date', Carbon::today())
            ->exists();
    }

    /**
     * Get today's sleep log
     */
    public function getTodayLog(int $userId): ?SleepLog
    {
        return SleepLog::where('user_id', $userId)
            ->where('log_date', Carbon::today())
            ->first();
    }

    /**
     * Check if user should be reminded to log sleep
     */
    public function shouldRemindToLogSleep(int $userId): bool
    {
        $todayLog = $this->getTodayLog($userId);

        // If no log exists for today, remind
        if (!$todayLog) {
            return true;
        }

        // If log exists but bedtime/wake_time not set, remind
        if (!$todayLog->bedtime || !$todayLog->wake_time) {
            return true;
        }

        return false;
    }

    /**
     * Get sleep logs for the last N days
     */
    public function getRecentLogs(int $userId, int $days = 7): Collection
    {
        return SleepLog::where('user_id', $userId)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1))
            ->orderBy('log_date', 'desc')
            ->get();
    }

    /**
     * Prepare chart data for visualization
     */
    public function prepareChartData(Collection $sleepLogs): array
    {
        $labels = [];
        $sleepDurations = [];
        $qualityScores = [];
        $bedTimes = [];
        $wakeTimes = [];

        // Sort by date ascending (oldest to newest)
        $sortedLogs = $sleepLogs->sortBy('log_date');

        $analysisService = app(SleepAnalysisService::class);

        foreach ($sortedLogs as $log) {
            $labels[] = $log->log_date->format('m/d');
            $sleepDurations[] = $log->sleep_duration_minutes 
                ? round($log->sleep_duration_minutes / 60, 1) 
                : null;
            $qualityScores[] = $log->sleep_quality 
                ? $analysisService->getQualityScore($log->sleep_quality) 
                : null;
            $bedTimes[] = $log->bedtime ?? null;
            $wakeTimes[] = $log->wake_time ?? null;
        }

        return [
            'labels' => $labels,
            'sleepDurations' => $sleepDurations,
            'qualityScores' => $qualityScores,
            'bedTimes' => $bedTimes,
            'wakeTimes' => $wakeTimes,
        ];
    }

    /**
     * Get sleep statistics summary for dashboard
     */
    public function getDashboardSummary(int $userId, int $days = 7): array
    {
        $recentLogs = $this->getRecentLogs($userId, $days);
        $todayLog = $this->getTodayLog($userId);

        return [
            'avg_duration' => $this->getAverageSleepDuration($userId, $days),
            'avg_quality' => $this->getAverageSleepQualityScore($userId, $days),
            'has_today_log' => $todayLog !== null,
            'today_log' => $todayLog,
            'logs_count' => $recentLogs->count(),
            'chart_data' => $this->prepareChartData($recentLogs),
        ];
    }
}
