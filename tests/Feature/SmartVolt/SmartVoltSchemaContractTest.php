<?php

namespace Tests\Feature\SmartVolt;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmartVoltSchemaContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_energy_meters_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('energy_meters'), 'Tabel energy_meters belum ada.');

        $requiredColumns = [
            'id',
            'user_id',
            'room_id',
            'esp_unit_id',
            'meter_code',
            'name',
            'sensor_type',
            'is_active',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('energy_meters', $column),
                "Kolom energy_meters.{$column} belum ada."
            );
        }
    }

    public function test_energy_logs_table_has_required_columns_for_pzem_telemetry(): void
    {
        $this->assertTrue(Schema::hasTable('energy_logs'), 'Tabel energy_logs belum ada.');

        $requiredColumns = [
            'id',
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
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('energy_logs', $column),
                "Kolom energy_logs.{$column} belum ada."
            );
        }
    }

    public function test_devices_table_has_required_relay_columns(): void
    {
        $this->assertTrue(Schema::hasTable('devices'), 'Tabel devices belum ada.');

        $requiredColumns = [
            'id',
            'room_id',
            'name',
            'relay_code',
            'esp_unit_id',
            'esp32_device_id',
            'status',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('devices', $column),
                "Kolom devices.{$column} belum ada."
            );
        }
    }

    public function test_devices_type_column_must_not_exist_anymore(): void
    {
        $this->assertTrue(Schema::hasTable('devices'), 'Tabel devices belum ada.');

        $this->assertFalse(
            Schema::hasColumn('devices', 'type'),
            'Kolom devices.type masih ada. Jika sudah tidak dipakai, buat migration untuk menghapus kolom type.'
        );
    }
}