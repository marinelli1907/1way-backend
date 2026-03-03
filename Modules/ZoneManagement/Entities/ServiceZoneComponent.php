<?php

namespace Modules\ZoneManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ServiceZoneComponent extends Model
{
    use HasUuid;

    protected $table = 'service_zone_components';

    protected $fillable = [
        'service_zone_id',
        'component_type',
        'label',
        'source',
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
