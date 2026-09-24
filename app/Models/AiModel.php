<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'version',
        'accuracy',
        'status',
        'model_path',
    ];


    protected $casts = [
        'accuracy'=>'float',
    ];


    public function trainingSessions()
    {
        return $this->hasMany(
            TrainingSession::class
        );
    }


    public function predictions()
    {
        return $this->hasMany(
            NilmPrediction::class
        );
    }
}