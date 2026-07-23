<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'energy_meter_id',
        'telemetry_id',
        'observed_at',
        'voltage',
        'current',
        'power',
        'energy',
        'frequency',
        'power_factor',
    ];

    protected $casts = [
        'observed_at' => 'immutable_datetime',
        'voltage' => 'float',
        'current' => 'float',
        'power' => 'float',
        'energy' => 'float',
        'frequency' => 'float',
        'power_factor' => 'float',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function energyMeter()
    {
        return $this->belongsTo(EnergyMeter::class, 'energy_meter_id');
    }
}
