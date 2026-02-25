<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUuid;

    protected $fillable = [
        'title', 'description', 'start_at', 'end_at', 'timezone',
        'visibility', 'private_code', 'is_promoted', 'is_active', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_promoted' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopePublicVisible($query)
    {
        return $query->where('visibility', 'public')->where('is_active', true);
    }

    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    public function users()
    {
        return $this->belongsToMany(\Modules\UserManagement\Entities\User::class, 'user_events')
            ->withTimestamps();
    }
}
