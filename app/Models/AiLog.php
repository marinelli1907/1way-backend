<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool',
        'status',
        'input',
        'output',
        'error',
        'duration_ms',
        'triggered_by',
    ];

    protected $casts = [
        'input'  => 'array',
        'output' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
