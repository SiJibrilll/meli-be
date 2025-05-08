<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'community_id',
        'image_id',
        'likes_count',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    function liked_by() {
        return $this->belongsToMany(User::class, 'likes', 'thread_id', 'user_id');
    }

    function reposted_by() {
        return $this->belongsToMany(Thread::class, 'repost_thread', 'thread_id', 'user_id')
            ->withPivot('content');
    }

    function comments() {
        return $this->hasMany(Comment::class);
    }
}
