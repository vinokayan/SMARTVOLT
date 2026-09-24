<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Default user untuk testing sistem.
         */
        User::updateOrCreate(
            [
                'email' => 'test@example.com',
            ],
            [
                'name' => 'Test User',
            ]
        );


        /*
         * SMARTVOLT Master Data
         *
         * DeviceClassSeeder:
         * - Lampu
         * - Kipas
         * - Laptop
         * - Monitor
         * - Printer
         * - AC
         * - Unknown
         *
         * AiModelSeeder:
         * - NILM-LSTM
         */
        $this->call([
            DeviceClassSeeder::class,
            AiModelSeeder::class,
        ]);
    }
}