<?php
namespace App\Console\Commands;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LogMissedHabits extends Command {
    protected $signature = 'habits:log-missed';
    protected $description = 'ثبت خودکار عادت‌های انجام نشده بعد از 24 ساعت';

    public function handle(): void {
        $yesterday = Carbon::yesterday();
        $habits = Habit::whereDoesntHave('logs', function ($q) use ($yesterday) {
            $q->where('log_date', $yesterday->toDateString());
        })->where('is_completed', false)->get();

        foreach ($habits as $habit) {
            $habit->logs()->create([
                'log_date' => $yesterday,
                'type' => 'missed',
                'note' => 'ثبت خودکار - انجام نشده',
            ]);
        }
        $this->info(count($habits) . ' عادت به عنوان انجام نشده ثبت شد.');
    }
}
