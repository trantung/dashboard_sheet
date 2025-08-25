<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'phone',
        'bio',
        'login_first',
        'premium',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Accessor for avatar URL
    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If it's already a full URL (like Google avatar), return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Otherwise, return storage URL
        return asset('storage/' . $value);
    }

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function sharedWorkspaces()
    {
        return $this->belongsToMany(Workspace::class, 'user_workspace');
    }

    public function allWorkspaces()
    {
        return $this->workspaces()->union($this->sharedWorkspaces());
    }
}
