<?php

namespace App\Http\Controllers;

use App\Models\SleepLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SleepLogController extends Controller {
    
    /**
     * نمایش صفحه ثبت خواب
     */
    public function index() {
        $user = Auth::user();
        
        // دریافت لاگ‌های ۷ روز اخیر
        $sleepLogs = $user->sleepLogs()
            ->where('log_date', '>=', Carbon::today()->subDays(6))
            ->orderBy('log_date', 'desc')
            ->get();
        
        // آمار هفته جاری
        $avgDuration = SleepLog::getAverageSleepDuration($user->id, 7);
        $avgQuality = SleepLog::getAverageSleepQuality($user->id, 7);
        $todayLog = SleepLog::getTodayLog($user->id);
        
        // آماده‌سازی داده‌ها برای نمودار
        $chartData = $this->prepareChartData($sleepLogs);
        
        return view('sleep.index', compact('sleepLogs', 'avgDuration', 'avgQuality', 'todayLog', 'chartData'));
    }
    
    /**
     * آماده‌سازی داده‌ها برای نمودار
     */
    private function prepareChartData($sleepLogs) {
        $labels = [];
        $sleepDurations = []; // مدت خواب به ساعت
        $qualityScores = []; // نمره کیفیت خواب
        $bedTimes = []; // زمان خواب برای نمایش خط تیره
        $wakeTimes = []; // زمان بیداری برای نمایش خط تیره
        
        // مرتب‌سازی بر اساس تاریخ صعودی (از قدیمی به جدید)
        $sortedLogs = $sleepLogs->sortBy('log_date');
        
        foreach ($sortedLogs as $log) {
            // فرمت تاریخ برای نمایش در نمودار
            $labels[] = $log->log_date->format('m/d');
            
            // مدت خواب به ساعت
            $sleepDurations[] = $log->sleep_duration_minutes ? round($log->sleep_duration_minutes / 60, 1) : null;
            
            // نمره کیفیت خواب (1-5)
            $qualityScores[] = $log->sleep_quality;
            
            // زمان خواب و بیداری برای نمایش
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
     * ذخیره یا بروزرسانی لاگ خواب امروز
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'bedtime' => 'nullable|date_format:H:i',
            'wake_time' => 'nullable|date_format:H:i',
            'sleep_quality' => 'nullable|integer|min:1|max:5',
            'note' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        $today = Carbon::today();
        
        // پیدا کردن لاگ امروز یا ایجاد جدید
        $sleepLog = SleepLog::firstOrCreate(
            ['user_id' => $user->id, 'log_date' => $today],
            []
        );
        
        // بروزرسانی فیلدها
        if (isset($validated['bedtime'])) {
            $sleepLog->bedtime = $validated['bedtime'];
        }
        if (isset($validated['wake_time'])) {
            $sleepLog->wake_time = $validated['wake_time'];
        }
        if (isset($validated['sleep_quality'])) {
            $sleepLog->sleep_quality = $validated['sleep_quality'];
        }
        if (isset($validated['note'])) {
            $sleepLog->note = $validated['note'];
        }
        
        // محاسبه مدت خواب
        if ($sleepLog->bedtime && $sleepLog->wake_time) {
            $sleepLog->calculateDuration();
        }
        
        $sleepLog->save();
        
        return redirect()->route('sleep.index')
            ->with('success', '✅ اطلاعات خواب شما با موفقیت ثبت شد!');
    }
    
    /**
     * بروزرسانی لاگ خواب برای روزهای گذشته
     */
    public function update(Request $request, SleepLog $sleepLog) {
        if ($sleepLog->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'bedtime' => 'nullable|date_format:H:i',
            'wake_time' => 'nullable|date_format:H:i',
            'sleep_quality' => 'nullable|integer|min:1|max:5',
            'note' => 'nullable|string|max:500',
        ]);
        
        if (isset($validated['bedtime'])) {
            $sleepLog->bedtime = $validated['bedtime'];
        }
        if (isset($validated['wake_time'])) {
            $sleepLog->wake_time = $validated['wake_time'];
        }
        if (isset($validated['sleep_quality'])) {
            $sleepLog->sleep_quality = $validated['sleep_quality'];
        }
        if (isset($validated['note'])) {
            $sleepLog->note = $validated['note'];
        }
        
        if ($sleepLog->bedtime && $sleepLog->wake_time) {
            $sleepLog->calculateDuration();
        }
        
        $sleepLog->save();
        
        return redirect()->route('sleep.index')
            ->with('success', '✅ اطلاعات خواب بروزرسانی شد!');
    }
    
    /**
     * حذف لاگ خواب
     */
    public function destroy(SleepLog $sleepLog) {
        if ($sleepLog->user_id !== Auth::id()) {
            abort(403);
        }
        
        $sleepLog->delete();
        
        return redirect()->route('sleep.index')
            ->with('success', 'اطلاعات خواب حذف شد.');
    }
}
