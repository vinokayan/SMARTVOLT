<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiModel;


class AiModelSeeder extends Seeder
{
    public function run(): void
    {
        AiModel::updateOrCreate(
            [
                'name' => 'NILM-LSTM',
                'version' => 'v1'
            ],
            [
                'accuracy' => null,
                'status' => 'development',
                'model_path' => null
            ]
        );
    }
}