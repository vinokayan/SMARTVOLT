<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'electricity_tariff',
        'power_limit',
        'refresh_interval',
    ];

    protected $casts = [
        'electricity_tariff' => 'decimal:2',
        'power_limit' => 'integer',
        'refresh_interval' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}