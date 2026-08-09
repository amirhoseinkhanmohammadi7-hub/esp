<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller {
    public function edit(Request $request) {
        $user = $request->user();
        $habits = $user->habits()->with('logs')->get();
        return view('profile.edit', compact('user', 'habits'));
    }
    public function update(Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);
        $user->fill($validated);
        if ($user->isDirty('email')) $user->email_verified_at = null;
        $user->save();
        return back()->with('success', 'پروفایل به‌روزرسانی شد.');
    }
    public function destroy(Request $request) {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
