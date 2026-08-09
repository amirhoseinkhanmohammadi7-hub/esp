<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $fillable = ['user_id', 'sender_name', 'session_id', 'message', 'is_approved'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
