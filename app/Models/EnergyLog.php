<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyLog extends Model
{
    protected $fillable = [
        'voltage',
        'current',
        'power',
        'energy'
    ];
}