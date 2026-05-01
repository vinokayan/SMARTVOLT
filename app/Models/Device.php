<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'type',
        'esp32_device_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function energyLogs()
    {
        return $this->hasMany(EnergyLog::class);
    }
}