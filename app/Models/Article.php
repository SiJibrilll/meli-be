<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image_id',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    // public function likes()
    // {
    //     return $this->hasMany(Like::class);
    // }

    function verified_by() {
        return $this->belongsToMany(User::class, 'article_verification', 'article_id', 'user_id');
    }
}
