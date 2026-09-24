<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilmPrediction extends Model
{
    use HasFactory;


    protected $fillable = [

        'energy_log_id',

        'device_class_id',

        'ai_model_id',

        'estimated_power',

        'confidence',

        'predicted_at',

    ];


    protected $casts = [

        'estimated_power' => 'float',

        'confidence' => 'float',

        'predicted_at' => 'datetime',

    ];



    public function energyLog()
    {
        return $this->belongsTo(
            EnergyLog::class,
            'energy_log_id'
        );
    }



    public function deviceClass()
    {
        return $this->belongsTo(
            DeviceClass::class,
            'device_class_id'
        );
    }



    public function aiModel()
    {
        return $this->belongsTo(
            AiModel::class,
            'ai_model_id'
        );
    }
}