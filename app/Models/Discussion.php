<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    protected $table = 'discussions';

    protected $fillable = [
        'user_id',
        'article_id',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
