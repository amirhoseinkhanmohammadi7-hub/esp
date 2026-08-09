<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model {
    protected $fillable = ['habit_id', 'user_id', 'name', 'message'];
    public function habit(): BelongsTo { return $this->belongsTo(Habit::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
