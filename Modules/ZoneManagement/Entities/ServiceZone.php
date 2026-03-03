<?php

namespace Modules\ZoneManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceZone extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'service_zones';

    protected $fillable = [
        'name',
        'boundary_type',
        'country_code',
        'state_code',
        'source',
        'boundary',
        'exclusions',
        'inclusions_override',
        'is_active',
        'priority',
        'pricing_rules',
    ];

    protected $casts = [
        'boundary'            => 'array',
        'exclusions'          => 'array',
        'inclusions_override' => 'array',
        'is_active'           => 'boolean',
        'priority'            => 'integer',
        'pricing_rules'       => 'array',
    ];

    public const DEFAULT_PRICING_RULES = [
        'min_fare_cents'          => 900,
        'max_fare_cents'          => 5000,
        'per_mile_cents'          => 180,
        'per_minute_cents'        => 35,
        'base_fee_cents'          => 250,
        'booking_fee_cents'       => 100,
        'surge_cap_multiplier'    => 1.6,
        'event_surge_multiplier'  => 1.0,
        'airport_fee_cents'       => 0,
        'driver_split_percent'    => 85,
    ];

    public const PRICING_RULES_VALIDATION = [
        'min_fare_cents'          => 'nullable|integer|min:0|max:5000',
        'max_fare_cents'          => 'nullable|integer|min:500|max:25000',
        'per_mile_cents'          => 'nullable|integer|min:50|max:500',
        'per_minute_cents'        => 'nullable|integer|min:10|max:200',
        'base_fee_cents'          => 'nullable|integer|min:0|max:2000',
        'booking_fee_cents'       => 'nullable|integer|min:0|max:2000',
        'surge_cap_multiplier'    => 'nullable|numeric|min:1.0|max:2.5',
        'event_surge_multiplier'  => 'nullable|numeric|min:1.0|max:2.5',
        'airport_fee_cents'       => 'nullable|integer|min:0|max:5000',
        'driver_split_percent'    => 'nullable|integer|min:50|max:95',
    ];

    /**
     * Return pricing rules merged with defaults; stored overrides win.
     */
    public function effectivePricingRules(): array
    {
        return array_merge(self::DEFAULT_PRICING_RULES, array_filter(
            $this->pricing_rules ?? [],
            fn($v) => $v !== null
        ));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderByDesc('priority');
    }

    public function components()
    {
        return $this->hasMany(ServiceZoneComponent::class, 'service_zone_id');
    }

    public function zoneExclusions()
    {
        return $this->hasMany(ServiceZoneExclusion::class, 'service_zone_id');
    }

    public function zoneInclusions()
    {
        return $this->hasMany(ServiceZoneInclusion::class, 'service_zone_id');
    }

    public function drivers()
    {
        return $this->belongsToMany(
            \Modules\UserManagement\Entities\User::class,
            'service_zone_drivers',
            'service_zone_id',
            'driver_user_id'
        )->withPivot(['is_active', 'priority'])->withTimestamps();
    }

    /**
     * Recompute the boundary from components (concat all component polygons).
     */
    public function recomputeBoundary(): void
    {
        $allCoords = [];
        foreach ($this->components as $comp) {
            $geom = $comp->geometry;
            if ($geom && ($geom['type'] ?? '') === 'MultiPolygon') {
                foreach ($geom['coordinates'] as $poly) {
                    $allCoords[] = $poly;
                }
            }
        }

        $this->boundary = empty($allCoords) ? null : [
            'type' => 'MultiPolygon',
            'coordinates' => $allCoords,
        ];
    }

    /**
     * Recompute exclusions from zoneExclusions relation.
     */
    public function recomputeExclusions(): void
    {
        $allCoords = [];
        foreach ($this->zoneExclusions as $ex) {
            $geom = $ex->geometry;
            if ($geom && ($geom['type'] ?? '') === 'MultiPolygon') {
                foreach ($geom['coordinates'] as $poly) {
                    $allCoords[] = $poly;
                }
            }
        }

        $this->exclusions = empty($allCoords) ? null : [
            'type' => 'MultiPolygon',
            'coordinates' => $allCoords,
        ];
    }

    /**
     * Recompute inclusions_override from zoneInclusions relation.
     */
    public function recomputeInclusions(): void
    {
        $allCoords = [];
        foreach ($this->zoneInclusions as $inc) {
            $geom = $inc->geometry;
            if ($geom && ($geom['type'] ?? '') === 'MultiPolygon') {
                foreach ($geom['coordinates'] as $poly) {
                    $allCoords[] = $poly;
                }
            }
        }

        $this->inclusions_override = empty($allCoords) ? null : [
            'type' => 'MultiPolygon',
            'coordinates' => $allCoords,
        ];
    }
}
