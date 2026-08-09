<?php
namespace App\Http\Controllers;
use App\Models\Habit;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller {
    public function store(Request $request, string $token) {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'برای امضای حمایت باید وارد شوید.');
        }
        $habit = Habit::where('share_token', $token)->firstOrFail();
        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);
        if ($habit->signatures()->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'شما قبلاً این مسیر را حمایت کرده‌اید! 💫');
        }
        $habit->signatures()->create([
            'user_id' => Auth::id(),
            'name' => Auth::user()->name,
            'message' => $validated['message'] ?? null,
        ]);
        return back()->with('success', '✨ امضای شما ثبت شد!');
    }
}
