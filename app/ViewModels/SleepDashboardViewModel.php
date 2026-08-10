<?php

namespace App\ViewModels;

use App\Models\SleepLog;
use App\Services\SleepAnalysisService;
use App\Services\SleepStatisticsService;
use Illuminate\Support\Collection;

class SleepDashboardViewModel
{
    public function __construct(
        private readonly int $userId,
        private readonly SleepStatisticsService $statisticsService,
        private readonly SleepAnalysisService $analysisService,
        private readonly int $days = 7
    ) {}

    public function getRecentLogs(): Collection
    {
        return $this->statisticsService->getRecentLogs($this->userId, $this->days);
    }

    public function getAverageDuration(): ?float
    {
        return $this->statisticsService->getAverageSleepDuration($this->userId, $this->days);
    }

    public function getAverageQuality(): ?float
    {
        return $this->statisticsService->getAverageSleepQualityScore($this->userId, $this->days);
    }

    public function getTodayLog(): ?SleepLog
    {
        return $this->statisticsService->getTodayLog($this->userId);
    }

    public function getChartData(): array
    {
        $logs = $this->getRecentLogs();
        return $this->statisticsService->prepareChartData($logs);
    }

    public function getQualityOptions(): array
    {
        return SleepLog::qualityOptions();
    }

    public function toArray(): array
    {
        return [
            'sleepLogs' => $this->getRecentLogs(),
            'avgDuration' => $this->getAverageDuration(),
            'avgQuality' => $this->getAverageQuality(),
            'todayLog' => $this->getTodayLog(),
            'chartData' => $this->getChartData(),
            'qualityOptions' => $this->getQualityOptions(),
        ];
    }
}
