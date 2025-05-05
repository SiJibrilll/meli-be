<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'birthDate',
        'address',
        'phone',
        'gender',
        'bio',
    ];

    protected $primaryKey = 'user_id'; // Tell Laravel what the PK is
    public $incrementing = false;      // It's not auto-incrementing
    protected $keyType = 'int';        // Use 'string' if you're using UUIDs

    function user() {
        return $this->belongsTo(User::class);
    }
}
