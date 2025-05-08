<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $fillable = [
        'path',
    ];

    public function getPath() {
        return asset(Storage::url($this->path));
    }
}
