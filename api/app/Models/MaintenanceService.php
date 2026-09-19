<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceService extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes', 'price', 'is_active', 'order'];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(MaintenanceAppointment::class);
    }
}
