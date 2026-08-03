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
        'is_online',
        'last_seen_at',
        'last_confirmed_at',
        'last_command_at',
        'last_command_id',
        'last_ack_command_id',
        'pending_state',
        'last_command_success',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_online' => 'boolean',
        'pending_state' => 'boolean',
        'last_command_success' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_confirmed_at' => 'datetime',
        'last_command_at' => 'datetime',
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
