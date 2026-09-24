<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_model_id',
        'dataset_name',
        'epoch',
        'loss',
        'accuracy',
        'trained_at',
    ];

    protected $casts = [
        'epoch' => 'integer',
        'loss' => 'float',
        'accuracy' => 'float',
        'trained_at' => 'datetime',
    ];

    public function aiModel()
    {
        return $this->belongsTo(
            AiModel::class,
            'ai_model_id'
        );
    }
}