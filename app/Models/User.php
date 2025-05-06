<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use  HasApiTokens,HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    function details() {
        return $this->hasOne(UserDetail::class);
    }

    function following() {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withPivot('followed_at');
    }

    function followers() {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withPivot('followed_at');
    }

    function articles() {
        return $this->hasMany(Article::class);
    }

    function threads() {
        return $this->hasMany(Thread::class);
    }

    function verifies() {
        return $this->belongsToMany(Article::class, 'article_verification', 'user_id', 'article_id');
    }

    function likedArticles() { //TODO dulunya likes, check this part if this name change caused trouble
        return $this->belongsToMany(Article::class, 'likes', 'user_id', 'article_id');
    }

    function likedThreads() {
        return $this->belongsToMany(Thread::class, 'likes', 'user_id', 'thread_id');
    }

    function communities() {
        return $this->belongsToMany(Community::class, 'communities_users', 'user_id', 'community_id');
    }

    function ownsCommunity() {
        return $this->hasMany(Community::class, 'user_id');
    }

    function reposts() {
        return $this->belongsToMany(Thread::class, 'repost_thread', 'user_id', 'thread_id')
            ->withPivot('content');
    }
}
