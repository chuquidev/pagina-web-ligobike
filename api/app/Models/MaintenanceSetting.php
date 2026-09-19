<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSetting extends Model
{
    protected $fillable = [
        'business_hours',
        'capacity',
        'slot_interval_minutes',
        'advance_booking_days',
        'min_notice_hours',
    ];

    protected $casts = [
        'business_hours' => 'array',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '19:00'],
                'friday' => ['open' => '09:00', 'close' => '19:00'],
                'saturday' => ['open' => '09:00', 'close' => '19:00'],
                'sunday' => null,
            ],
            'capacity' => 1,
            'slot_interval_minutes' => 30,
            'advance_booking_days' => 14,
            'min_notice_hours' => 2,
        ]);
    }
}
