<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'caption',
        'user_id',
        'date_posted',
        'image',
        'city',
        'latitude',
        'longitude',
        'is_public'
    ];

    protected $casts = [
        'date_posted' => 'date',
        'is_public' => 'boolean',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', true)
                    ->where('published_at', '<=', now());
    }

    // Accessors & Mutators
    public function getExcerptAttribute($value)
    {
        return $value ?? \Str::limit($this->content, 150);
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = \Str::slug($value ?? $this->title);
    }

    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}