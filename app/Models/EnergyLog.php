<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnergyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'voltage',
        'current',
        'power',
        'energy',
        'frequency',
        'power_factor',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}