<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model {
    protected $fillable = ['habit_id', 'log_date', 'type', 'note'];
    protected $casts = ['log_date' => 'date'];
    public function habit(): BelongsTo { return $this->belongsTo(Habit::class); }
    public function isMicro(): bool { return $this->type === 'micro'; }
}
