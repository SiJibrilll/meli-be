<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $fillable = [
        'title',
        'bio',
        'user_id',
        'image_id',
        'members_count',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'communities_users');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
