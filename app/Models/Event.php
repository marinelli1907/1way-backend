<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location_name',
        'address',
        'lat',
        'lng',
        'start_time',
        'end_time',
        'source',
        'meta',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'meta'       => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
