<?php

namespace Modules\UserManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverOnboardingStatus extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'driver_id',
        'profile_complete',
        'docs_uploaded',
        'approved',
        'active',
        'notes',
        'approved_by',
    ];

    protected $casts = [
        'profile_complete' => 'boolean',
        'docs_uploaded'    => 'boolean',
        'approved'         => 'boolean',
        'active'           => 'boolean',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Returns a 0–4 integer representing completed steps.
     */
    public function completedSteps(): int
    {
        return (int) $this->profile_complete
             + (int) $this->docs_uploaded
             + (int) $this->approved
             + (int) $this->active;
    }

    /**
     * Percentage complete (0–100).
     */
    public function progressPercent(): int
    {
        return (int) round(($this->completedSteps() / 4) * 100);
    }
}
