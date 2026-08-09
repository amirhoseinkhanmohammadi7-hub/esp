<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model {
    protected $fillable = ['user_id', 'user_name', 'session_id', 'reaction_type'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
