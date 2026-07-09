<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Kolom ini mungkin sudah ada pada project Anda. Pemeriksaan dibuat
         * agar migration aman dijalankan pada struktur lama maupun baru.
         */
        if (! Schema::hasColumn('devices', 'relay_code')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('relay_code', 50)
                    ->nullable()
                    ->after('device_key');
            });
        }

        if (! Schema::hasColumn('devices', 'esp_unit_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('esp_unit_id', 100)
                    ->nullable()
                    ->after('esp32_device_id');
            });
        }

        /*
         * Versi lama menjadikan esp32_device_id unik. Aturan tersebut
         * membuat satu ESP tidak dapat memiliki Relay 1 dan Relay 2.
         */
        foreach ($this->uniqueIndexesOnlyForEsp32DeviceId() as $indexName) {
            Schema::table('devices', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        if (! $this->indexExists('devices', 'devices_esp32_device_id_index')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->index(
                    'esp32_device_id',
                    'devices_esp32_device_id_index'
                );
            });
        }

        /*
         * Yang unik adalah pasangan ESP + channel relay, bukan ESP sendiri.
         */
        if (! $this->indexExists('devices', 'devices_esp_unit_relay_unique')) {
            $duplicates = DB::table('devices')
                ->select(
                    'esp_unit_id',
                    'relay_code',
                    DB::raw('COUNT(*) as total')
                )
                ->whereNotNull('esp_unit_id')
                ->whereNotNull('relay_code')
                ->groupBy('esp_unit_id', 'relay_code')
                ->having('total', '>', 1)
                ->get();

            if ($duplicates->isNotEmpty()) {
                throw new RuntimeException(
                    'Migration dihentikan: terdapat data relay duplikat dengan kombinasi esp_unit_id dan relay_code yang sama.'
                );
            }

            Schema::table('devices', function (Blueprint $table) {
                $table->unique(
                    ['esp_unit_id', 'relay_code'],
                    'devices_esp_unit_relay_unique'
                );
            });
        }
    }

    public function down(): void
    {
        /*
         * Jangan mengembalikan unique index lama pada esp32_device_id,
         * karena konfigurasi multi-relay yang sudah dibuat akan gagal.
         */
        if ($this->indexExists('devices', 'devices_esp_unit_relay_unique')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropUnique('devices_esp_unit_relay_unique');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }

    private function uniqueIndexesOnlyForEsp32DeviceId(): array
    {
        $rows = DB::select('SHOW INDEX FROM `devices`');
        $indexes = [];

        foreach ($rows as $row) {
            $indexes[$row->Key_name][] = $row;
        }

        $result = [];

        foreach ($indexes as $indexName => $indexRows) {
            $isUnique = (int) $indexRows[0]->Non_unique === 0;
            $columns = collect($indexRows)
                ->sortBy('Seq_in_index')
                ->pluck('Column_name')
                ->values()
                ->all();

            if ($isUnique && $columns === ['esp32_device_id']) {
                $result[] = $indexName;
            }
        }

        return $result;
    }
};
