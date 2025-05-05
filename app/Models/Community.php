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

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
