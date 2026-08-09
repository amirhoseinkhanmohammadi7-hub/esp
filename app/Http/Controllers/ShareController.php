<?php
namespace App\Http\Controllers;
use App\Models\Habit;

class ShareController extends Controller {
    public function show(string $token) {
        $habit = Habit::where('share_token', $token)->where('is_public', true)->withCount(['logs', 'signatures'])->with('signatures.user')->firstOrFail();
        return view('habits.share', compact('habit'));
    }
}
