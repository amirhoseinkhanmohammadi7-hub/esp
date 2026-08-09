<?php
namespace App\Http\Controllers;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HabitController extends Controller {
    public function index() {
        $habits = Auth::user()->habits()->latest()->get();
        return view('habits.index', compact('habits'));
    }
    public function create() { return view('habits.create'); }
    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'emoji' => 'required|string|max:10',
        ]);
        $habit = Auth::user()->habits()->create($validated);
        return redirect()->route('habits.show', $habit)->with('success', '🎉 عادت شما ساخته شد!');
    }
    public function show(Habit $habit) {
        if ($habit->user_id !== Auth::id()) abort(403);
        $habit->load(['logs' => fn($q) => $q->where('log_date', '>=', Carbon::now()->subDays(90))->orderBy('log_date', 'desc'), 'signatures.user']);
        return view('habits.show', compact('habit'));
    }
    public function log(Request $request, Habit $habit, string $type) {
        if ($habit->user_id !== Auth::id()) abort(403);
        if (!in_array($type, ['micro', 'full'])) abort(400);
        $existing = $habit->logs()->where('log_date', Carbon::today())->first();
        if ($existing) {
            $existing->update(['type' => $type]);
            $msg = $type === 'micro' ? '✅ به حالت ۲ دقیقه‌ای تغییر کرد.' : '✅ به حالت کامل ارتقا یافت!';
        } else {
            $habit->logs()->create(['log_date' => Carbon::today(), 'type' => $type]);
            $msg = $type === 'micro' ? '⚡ عالی! حداقل ۲ دقیقه انجام دادی.' : '🔥 فوق‌العاده! امروز کامل انجام دادی!';
        }
        return redirect()->route('habits.show', $habit)->with('success', $msg);
    }
    public function complete(Request $request, Habit $habit) {
        if ($habit->user_id !== Auth::id()) abort(403);
        $validated = $request->validate(['story' => 'required|string|max:2000']);
        $habit->markAsCompleted($validated['story']);
        return redirect()->route('habits.show', $habit)->with('success', '🏆 تبریک! عادت شما با موفقیت تکمیل شد!');
    }
    public function destroy(Habit $habit) {
        if ($habit->user_id !== Auth::id()) abort(403);
        $habit->delete();
        return redirect()->route('habits.index')->with('success', 'عادت حذف شد.');
    }
}
