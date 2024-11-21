<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'date_of_birth',
        'image',
        'bio',
        'status',
        'email',
        'password',
        'is_private',
        'friend_request_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_private' => 'boolean',
        'friend_request_enabled' => 'boolean',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }
}