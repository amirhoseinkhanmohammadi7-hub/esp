<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sleep\StoreSleepLogRequest;
use App\Http\Requests\Sleep\UpdateSleepLogRequest;
use App\Models\SleepLog;
use App\Services\SleepAnalysisService;
use App\Services\SleepStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SleepLogController extends Controller 
{
    public function __construct(
        private readonly SleepStatisticsService $statisticsService,
        private readonly SleepAnalysisService $analysisService
    ) {}

    /**
     * Display the sleep tracker dashboard
     */
    public function index() 
    {
        $user = Auth::user();
        
        // Get dashboard summary from service
        $summary = $this->statisticsService->getDashboardSummary($user->id, 7);
        
        return view('sleep.index', [
            'sleepLogs' => $this->statisticsService->getRecentLogs($user->id, 7),
            'avgDuration' => $summary['avg_duration'],
            'avgQuality' => $summary['avg_quality'],
            'todayLog' => $summary['today_log'],
            'chartData' => $summary['chart_data'],
            'qualityOptions' => SleepLog::qualityOptions(),
        ]);
    }

    /**
     * Store a new sleep log or update today's log
     */
    public function store(StoreSleepLogRequest $request) 
    {
        $user = Auth::user();
        $validated = $request->validated();
        
        // Find or create today's log
        $sleepLog = SleepLog::firstOrCreate(
            ['user_id' => $user->id, 'log_date' => now()->toDateString()],
            []
        );
        
        // Update fields
        $this->updateSleepLog($sleepLog, $validated);
        
        return redirect()->route('sleep.index')
            ->with('success', '✅ اطلاعات خواب شما با موفقیت ثبت شد!');
    }

    /**
     * Update an existing sleep log
     */
    public function update(UpdateSleepLogRequest $request, SleepLog $sleepLog) 
    {
        if ($sleepLog->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $this->updateSleepLog($sleepLog, $request->validated());
        
        return redirect()->route('sleep.index')
            ->with('success', '✅ اطلاعات خواب بروزرسانی شد!');
    }

    /**
     * Delete a sleep log
     */
    public function destroy(SleepLog $sleepLog) 
    {
        if ($sleepLog->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $sleepLog->delete();
        
        return redirect()->route('sleep.index')
            ->with('success', 'اطلاعات خواب حذف شد.');
    }

    /**
     * Helper method to update sleep log with calculated duration
     */
    private function updateSleepLog(SleepLog $sleepLog, array $data): void 
    {
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $sleepLog->{$key} = $value;
            }
        }
        
        // Calculate duration if both times are available
        if ($sleepLog->bedtime && $sleepLog->wake_time) {
            $sleepLog->sleep_duration_minutes = $this->analysisService->calculateDuration(
                $sleepLog->log_date->toDateString(),
                $sleepLog->bedtime,
                $sleepLog->wake_time
            );
        }
        
        $sleepLog->save();
    }
}
