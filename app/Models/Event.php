<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'location_name',
        'address',
        'lat',
        'lng',
        'starts_at',
        'ends_at',
        'timezone',
        'is_public',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }
}
