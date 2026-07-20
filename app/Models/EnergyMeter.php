<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'esp_unit_id',
        'meter_code',
        'name',
        'sensor_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function readings()
    {
        return $this->hasMany(EnergyLog::class, 'energy_meter_id');
    }
public function dailySummaries()
{
    return $this->hasMany(\App\Models\EnergyDailySummary::class);
}
}