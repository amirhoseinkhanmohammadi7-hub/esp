<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SleepLogController;

/*
|--------------------------------------------------------------------------
| مسیرهای احراز هویت (لاگین، ثبت‌نام، خروج) - باید قبل از routeهای catch-all باشند
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

    Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);

    Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| مسیرهای عمومی (بدون نیاز به لاگین)
|--------------------------------------------------------------------------
*/

// صفحه اصلی
Route::get('/', function () {
    return auth()->check() ? redirect()->route('habits.index') : view('welcome');
});

// اشتراک‌گذاری عادت (صفحه عمومی با قابلیت استوری)
Route::get('/s/{token}', [ShareController::class, 'show'])->name('habits.share');
Route::post('/s/{token}/sign', [SignatureController::class, 'store'])->name('signatures.store');

// نمایش پروفایل عمومی کاربر
Route::get('/{username}', function ($username) {
    $user = \App\Models\User::where('name', $username)->firstOrFail();
    
    $reactions = \App\Models\Reaction::orderBy('created_at', 'desc')->get();
    $reactionsByType = \App\Models\Reaction::selectRaw('reaction_type, COUNT(*) as count')
        ->groupBy('reaction_type')
        ->pluck('count', 'reaction_type');

    return view('profile.user-page', compact('user', 'reactions', 'reactionsByType'));
})->name('profile.user')->where('username', '(?!login|register|forgot-password|reset-password|verify-email|confirm-password|logout|habits|profile|s|api|messages|my-messages)[a-zA-Z0-9_]+');

// ثبت ری‌اکشن روی نمودار عمومی
Route::post('/chart/{username}/react', function (\Illuminate\Http\Request $request, $username) {
    $validated = $request->validate([
        'reaction' => 'required|in:,🔥,💪,⭐,❤️',
        'user_name' => 'nullable|string|max:100',
    ]);

    $sessionId = session()->getId();
    $userId = auth()->id();

    $existing = \App\Models\Reaction::where(function($q) use ($userId, $sessionId) {
        if ($userId) {
            $q->where('user_id', $userId);
        } else {
            $q->where('session_id', $sessionId);
        }
    })->first();

    if ($existing) {
        return response()->json([
            'success' => false,
            'message' => 'شما قبلاً ری‌اکشن داده‌اید!'
        ], 400);
    }

    \App\Models\Reaction::create([
        'user_id' => $userId,
        'user_name' => $validated['user_name'] ?? ($userId ? auth()->user()->name : 'مهمان'),
        'session_id' => $sessionId,
        'reaction_type' => $validated['reaction'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'ری‌اکشن شما ثبت شد!'
    ]);
})->name('profile.react');

/*
|--------------------------------------------------------------------------
| APIهای عمومی (بدون نیاز به لاگین)
|--------------------------------------------------------------------------
*/

// API داده‌های نمودار (برای کاربر لاگین کرده)
Route::get('/api/chart-data/{period}', function ($period) {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $days = match($period) {
        'week' => 7,
        'month' => 30,
        'six_months' => 180,
        'year' => 365,
        default => 30,
    };

    $habits = $user->habits;
    $labels = [];
    $successRates = [];

    $currentDate = \Carbon\Carbon::today()->subDays($days - 1);

    for ($i = 0; $i < $days; $i++) {
        $dateStr = $currentDate->format('Y-m-d');
        $labels[] = $currentDate->format('m/d');

        $totalHabits = $habits->count();
        if ($totalHabits === 0) {
            $successRates[] = 0;
        } else {
            $successCount = 0;
            foreach ($habits as $habit) {
                $log = $habit->logs()
                    ->whereDate('log_date', $dateStr)
                    ->where('type', '!=', 'missed')
                    ->first();
                if ($log) {
                    $successCount++;
                }
            }
            $successRates[] = round(($successCount / $totalHabits) * 100, 1);
        }

        $currentDate->addDay();
    }

    return response()->json([
        'labels' => $labels,
        'data' => $successRates,
    ]);
})->name('api.chart-data');

/*
|--------------------------------------------------------------------------
| مسیرهای محافظت شده (نیاز به لاگین)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // پروفایل
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ردیاب خواب
    Route::get('/sleep', [SleepLogController::class, 'index'])->name('sleep.index');
    Route::post('/sleep', [SleepLogController::class, 'store'])->name('sleep.store');
    Route::put('/sleep/{sleepLog}', [SleepLogController::class, 'update'])->name('sleep.update');
    Route::delete('/sleep/{sleepLog}', [SleepLogController::class, 'destroy'])->name('sleep.destroy');

    // عادت‌ها
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::get('/habits/create', [HabitController::class, 'create'])->name('habits.create');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::get('/habits/{habit}', [HabitController::class, 'show'])->name('habits.show');
    Route::post('/habits/{habit}/log/{type}', [HabitController::class, 'log'])->name('habits.log');
    Route::post('/habits/{habit}/complete', [HabitController::class, 'complete'])->name('habits.complete');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
});
