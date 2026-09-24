<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
    ];


    public function predictions()
    {
        return $this->hasMany(
            NilmPrediction::class,
            'device_class_id'
        );
    }


    public function latestPrediction()
    {
        return $this->hasOne(
            NilmPrediction::class,
            'device_class_id'
        )->latestOfMany();
    }
}