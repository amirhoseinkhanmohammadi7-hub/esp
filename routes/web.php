<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;

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
})->name('profile.user')->where('username', '(?!login|register|forgot-password|reset-password|verify-email|confirm-password|logout|habits|profile|s|api|messages|my-messages|chat|chat-requests)[a-zA-Z0-9_]+');

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
    
    // Chat routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{userId}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{userId}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat-request/{userId}', [ChatController::class, 'sendRequest'])->name('chat.request');
    Route::get('/chat-requests', [ChatController::class, 'getRequests'])->name('chat.requests');
    Route::post('/chat-request/{requestId}/respond', [ChatController::class, 'respondToRequest'])->name('chat.respond');
    Route::get('/api/chat/{userId}/messages', [ChatController::class, 'getNewMessages'])->name('chat.api.messages');
    Route::post('/api/chat/{userId}/typing', [ChatController::class, 'typingStatus'])->name('chat.api.typing');
    Route::get('/api/chat/{userId}/typing-status', [ChatController::class, 'getTypingStatus'])->name('chat.api.typing-status');
});

/*
|--------------------------------------------------------------------------
| مسیرهای احراز هویت (لاگین، ثبت‌نام، خروج)
|--------------------------------------------------------------------------\n*/

// ارسال پیام به صاحب چارت
Route::post('/profile/{username}/message', function (\Illuminate\Http\Request $request, $username) {
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

