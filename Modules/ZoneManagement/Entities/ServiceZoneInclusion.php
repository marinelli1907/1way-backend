<?php

namespace Modules\ZoneManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ServiceZoneInclusion extends Model
{
    use HasUuid;

    protected $table = 'service_zone_inclusions';

    protected $fillable = [
        'service_zone_id',
        'label',
        'geometry',
    ];

    protected $casts = [
        'geometry' => 'array',
    ];

    public function zone()
    {
        return $this->belongsTo(ServiceZone::class, 'service_zone_id');
    }
}
