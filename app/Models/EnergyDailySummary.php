<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyDailySummary extends Model
{
    protected $fillable = [
        'user_id',
        'energy_meter_id',
        'summary_date',
        'energy_start',
        'energy_end',
        'usage_kwh',
        'avg_voltage',
        'max_power',
        'last_voltage',
        'last_current',
        'last_power',
        'tariff_per_kwh',
        'estimated_cost',
        'sample_count',
        'last_observed_at',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'last_observed_at' => 'datetime',
        'energy_start' => 'float',
        'energy_end' => 'float',
        'usage_kwh' => 'float',
        'avg_voltage' => 'float',
        'max_power' => 'float',
        'last_voltage' => 'float',
        'last_current' => 'float',
        'last_power' => 'float',
        'tariff_per_kwh' => 'float',
        'estimated_cost' => 'float',
        'sample_count' => 'integer',
    ];

    public function energyMeter()
    {
        return $this->belongsTo(EnergyMeter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}