<?php

namespace Modules\UserManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverApplication extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'driver_applications';

    protected $fillable = [
        'status',
        'first_name',
        'last_name',
        'phone',
        'email',
        'city',
        'state',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'rideshare_insurance',
        'availability',
        'preferred_service_area',
        'notes',
        'consent',
        'license_photo_path',
        'license_photo_original_name',
        'license_photo_mime',
        'license_photo_size',
        'docs',
        'reviewed_by',
        'reviewed_at',
        'reject_reason',
    ];

    protected $casts = [
        'availability'         => 'array',
        'rideshare_insurance'  => 'boolean',
        'consent'              => 'boolean',
        'license_photo_size'   => 'integer',
        'docs'                 => 'array',
        'reviewed_at'          => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get a specific document entry from the docs JSON.
     */
    public function getDoc(string $key): ?array
    {
        return $this->docs[$key] ?? null;
    }

    /**
     * Get all document keys that have been uploaded.
     */
    public function getUploadedDocKeys(): array
    {
        if (!$this->docs || !is_array($this->docs)) {
            return [];
        }

        return array_keys(array_filter($this->docs, fn($doc) => !empty($doc['path'])));
    }
}
