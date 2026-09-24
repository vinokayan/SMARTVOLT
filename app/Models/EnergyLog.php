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



    /**
     * Relasi perangkat fisik
     */
    public function device()
    {
        return $this->belongsTo(
            Device::class
        );
    }



    /**
     * Relasi jalur MCB / Energy Meter
     */
    public function energyMeter()
    {
        return $this->belongsTo(
            EnergyMeter::class,
            'energy_meter_id'
        );
    }



    /**
     * Hasil prediksi NILM
     *
     * Satu EnergyLog dapat memiliki
     * beberapa hasil prediksi AI.
     *
     * Contoh:
     * Lampu 22W
     * Kipas 45W
     * AC 900W
     */
    public function predictions()
    {
        return $this->hasMany(
            NilmPrediction::class,
            'energy_log_id'
        );
    }



    /**
     * Feature hasil ekstraksi NILM
     *
     * Digunakan sebagai input
     * machine learning / LSTM.
     */
    public function nilmFeature()
    {
        return $this->hasOne(
            NilmFeature::class,
            'energy_log_id'
        );
    }

}