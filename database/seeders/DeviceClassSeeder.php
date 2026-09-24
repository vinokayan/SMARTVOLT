<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceClass;

class DeviceClassSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'name' => 'Lampu',
                'category' => 'Lighting',
                'description' => 'Perangkat penerangan rumah'
            ],
            [
                'name' => 'Kipas',
                'category' => 'Cooling',
                'description' => 'Perangkat pendingin dengan motor listrik'
            ],
            [
                'name' => 'Laptop',
                'category' => 'Electronic',
                'description' => 'Komputer portable'
            ],
            [
                'name' => 'Monitor',
                'category' => 'Electronic',
                'description' => 'Display komputer'
            ],
            [
                'name' => 'Printer',
                'category' => 'Electronic',
                'description' => 'Perangkat pencetak'
            ],
            [
                'name' => 'AC',
                'category' => 'Cooling',
                'description' => 'Air conditioner'
            ],
            [
                'name' => 'Unknown',
                'category' => 'Other',
                'description' => 'Perangkat belum teridentifikasi oleh sistem NILM'
            ],
        ];


        foreach ($devices as $device) {
            DeviceClass::updateOrCreate(
                [
                    'name' => $device['name']
                ],
                [
                    'category' => $device['category'],
                    'description' => $device['description'],
                ]
            );
        }
    }
}