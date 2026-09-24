<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilmFeature extends Model
{
    use HasFactory;


    protected $fillable = [

        'energy_log_id',

        'power_delta',
        'current_delta',

        'current',
        'power',
        'frequency',

        'voltage',
        'power_factor',

        'duration_seconds',

        'feature_time',

    ];


    protected $casts = [

        'power_delta' => 'float',
        'current_delta' => 'float',

        'current' => 'float',
        'power' => 'float',
        'frequency' => 'float',

        'voltage' => 'float',
        'power_factor' => 'float',

        'duration_seconds' => 'float',

        'feature_time' => 'datetime',

    ];


    public function energyLog()
    {
        return $this->belongsTo(
            EnergyLog::class,
            'energy_log_id'
        );
    }
}