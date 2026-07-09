<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'name',
        'device_key',
        'relay_code',
        'esp32_device_id',
        'esp_unit_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function energyLogs()
    {
        return $this->hasMany(EnergyLog::class);
    }
}