<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'password', 'profile_picture', 'bio', 'joined_at'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'joined_at' => 'datetime'];
    }
    public function habits() { return $this->hasMany(Habit::class); }
    public function signatures() { return $this->hasMany(Signature::class); }
    public function achievements() { return $this->hasMany(Achievement::class); }
    public function sentChatRequests() { return $this->hasMany(ChatRequest::class, 'sender_id'); }
    public function receivedChatRequests() { return $this->hasMany(ChatRequest::class, 'receiver_id'); }
    public function sentChats() { return $this->hasMany(Chat::class, 'user_id'); }
    public function receivedChats() { return $this->hasMany(Chat::class, 'receiver_id'); }

    /**
     * Get the profile picture URL or a default avatar.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        // Generate a default avatar using UI Avatars API with user's name
        $name = urlencode($this->name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=a855f7&color=fff&size=256";
    }
    
    /**
     * Boot method to set joined_at on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if (!$user->joined_at) {
                $user->joined_at = now();
            }
        });
    }
}
