<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceAppointment extends Model
{
    protected $fillable = [
        'maintenance_service_id',
        'customer_name',
        'customer_phone',
        'bike_info',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(MaintenanceService::class, 'maintenance_service_id');
    }
}
