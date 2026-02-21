<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DriverInviteToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'token',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /** Generate a fresh invite token for the given driver. */
    public static function generate(string $driverId): self
    {
        // Invalidate any existing unused tokens
        static::where('driver_id', $driverId)->where('used', false)->delete();

        return static::create([
            'driver_id'  => $driverId,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
            'used'       => false,
        ]);
    }

    public function isValid(): bool
    {
        return ! $this->used && $this->expires_at->isFuture();
    }

    /** URL the driver can click to log in without a password. */
    public function inviteUrl(): string
    {
        return route('driver.invite.accept', ['token' => $this->token]);
    }
}
