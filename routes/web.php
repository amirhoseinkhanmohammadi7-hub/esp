<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ProfileController;

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

// نمودار عمومی بر اساس نام کاربر
Route::get('/chart/{username}', function ($username) {
    $user = \App\Models\User::where('name', $username)->firstOrFail();
    
    $reactions = \App\Models\Reaction::orderBy('created_at', 'desc')->get();
    $reactionsByType = \App\Models\Reaction::selectRaw('reaction_type, COUNT(*) as count')
        ->groupBy('reaction_type')
        ->pluck('count', 'reaction_type');
    
    return view('profile.public-chart', compact('user', 'reactions', 'reactionsByType'));
})->name('profile.chart');

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

// دریافت ری‌اکشن‌ها (برای بروزرسانی زنده)
Route::get('/chart/{username}/reactions', function ($username) {
    $reactions = \App\Models\Reaction::orderBy('created_at', 'desc')->take(50)->get();
    $reactionsByType = \App\Models\Reaction::selectRaw('reaction_type, COUNT(*) as count')
        ->groupBy('reaction_type')
        ->pluck('count', 'reaction_type');
    
    return response()->json([
        'reactions' => $reactions,
        'counts' => $reactionsByType,
    ]);
})->name('profile.reactions');

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

    // عادت‌ها
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::get('/habits/create', [HabitController::class, 'create'])->name('habits.create');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::get('/habits/{habit}', [HabitController::class, 'show'])->name('habits.show');
    Route::post('/habits/{habit}/log/{type}', [HabitController::class, 'log'])->name('habits.log');
    Route::post('/habits/{habit}/complete', [HabitController::class, 'complete'])->name('habits.complete');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    // نمایش پیام‌های در انتظار تایید (فقط برای صاحب چارت)
Route::get('/my-messages', function () {
    $user = auth()->user();
    $pendingMessages = \App\Models\Message::where('is_approved', false)->orderBy('created_at', 'desc')->get();
    $approvedMessages = \App\Models\Message::where('is_approved', true)->orderBy('created_at', 'desc')->get();
    return view('profile.manage-messages', compact('user', 'pendingMessages', 'approvedMessages'));
})->name('profile.manage-messages');

// تایید پیام
Route::post('/messages/{message}/approve', function (\App\Models\Message $message) {
    if (auth()->id() !== $message->user_id && $message->user_id !== null) {
        // اگر پیام از طرف مهمان است، فقط صاحب چارت می‌تواند تایید کند
    }
    $message->update(['is_approved' => true]);
    return back()->with('success', '✅ پیام تایید شد!');
})->name('messages.approve');

// رد/حذف پیام
Route::delete('/messages/{message}', function (\App\Models\Message $message) {
    $message->delete();
    return back()->with('success', '🗑️ پیام حذف شد!');
})->name('messages.delete');
});

/*
|--------------------------------------------------------------------------
| مسیرهای احراز هویت (لاگین، ثبت‌نام، خروج)
|--------------------------------------------------------------------------
*/
// ارسال پیام به صاحب چارت
Route::post('/chart/{username}/message', function (\Illuminate\Http\Request $request, $username) {
    $validated = $request->validate([
        'message' => 'required|string|max:500',
        'sender_name' => 'nullable|string|max:100',
    ]);
    
    \App\Models\Message::create([
        'user_id' => auth()->id(),
        'sender_name' => $validated['sender_name'] ?? (auth()->id() ? auth()->user()->name : 'مهمان'),
        'session_id' => session()->getId(),
        'message' => $validated['message'],
        'is_approved' => false,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'پیام شما ارسال شد و پس از تایید نمایش داده می‌شود.'
    ]);
})->name('profile.message');

// دریافت پیام‌های تایید شده
Route::get('/chart/{username}/messages', function ($username) {
    $messages = \App\Models\Message::where('is_approved', true)
        ->orderBy('created_at', 'desc')
        ->get();
    
    return response()->json(['messages' => $messages]);
})->name('profile.messages');

// تایید پیام (فقط برای صاحب چارت)
Route::post('/chart/{username}/message/{message}/approve', function (\Illuminate\Http\Request $request, $username, $messageId) {
    $user = \App\Models\User::where('name', $username)->firstOrFail();
    
    if (auth()->id() !== $user->id) {
        return response()->json(['success' => false, 'message' => 'دسترسی ندارید'], 403);
    }
    
    $message = \App\Models\Message::findOrFail($messageId);
    $message->update(['is_approved' => true]);
    
    return response()->json(['success' => true]);
})->name('profile.message.approve')->middleware('auth');

// دریافت پیام‌های در انتظار تایید (برای صاحب چارت)
Route::get('/chart/{username}/pending-messages', function ($username) {
    $user = \App\Models\User::where('name', $username)->firstOrFail();
    
    if (auth()->id() !== $user->id) {
        return response()->json(['success' => false, 'message' => 'دسترسی ندارید'], 403);
    }
    
    $messages = \App\Models\Message::where('is_approved', false)
        ->orderBy('created_at', 'desc')
        ->get();
    
    return response()->json(['messages' => $messages]);
})->name('profile.pending-messages')->middleware('auth');
require __DIR__.'/auth.php';
