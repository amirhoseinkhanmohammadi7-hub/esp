<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model {
    protected $fillable = ['user_id', 'habit_id', 'title', 'icon', 'description', 'type'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function habit(): BelongsTo { return $this->belongsTo(Habit::class); }
}
