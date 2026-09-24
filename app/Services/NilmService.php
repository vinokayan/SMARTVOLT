<?php

namespace App\Services;

use App\Models\EnergyLog;
use App\Models\NilmPrediction;
use App\Models\DeviceClass;
use App\Models\AiModel;
use App\Models\NilmFeature;

class NilmService
{
    /**
     * Prototype NILM inference.
     *
     * Saat ini menggunakan feature-based classifier.
     * Nantinya diganti dengan LSTM inference.
     */
    public function predict(EnergyLog $energyLog)
    {

        /*
         * Cegah prediksi ganda
         */
        $existing = NilmPrediction::where(
            'energy_log_id',
            $energyLog->id
        )->first();


        if ($existing) {
            return $existing;
        }


        /*
         * Pastikan EnergyLog memiliki power
         */
        if ($energyLog->power === null) {
            return null;
        }


        /*
         * Ambil feature NILM.
         * Jika belum ada maka dibuat otomatis.
         */
        $feature = $energyLog->nilmFeature;


        if (! $feature) {

            $feature = app(NilmFeatureService::class)
                ->generate($energyLog);

        }


        if (! $feature) {
            return null;
        }


        /*
         * Feature-based inference
         */
        $prediction = $this->classifyFeature($feature);



        /*
         * Ambil kelas perangkat
         */
        $deviceClass = DeviceClass::where(
            'name',
            $prediction['device']
        )->first();



        /*
         * Ambil model AI
         */
        $aiModel = AiModel::where(
            'name',
            'NILM-LSTM'
        )->first();



        if (! $deviceClass || ! $aiModel) {
            return null;
        }



        /*
         * Simpan hasil prediksi
         */
        return NilmPrediction::create([

            'energy_log_id' => $energyLog->id,

            'device_class_id' => $deviceClass->id,

            'ai_model_id' => $aiModel->id,

            'estimated_power' => $prediction['power'],

            'confidence' => $prediction['confidence'],

            'predicted_at' => now(),

        ]);

    }



    /**
     * Feature based classifier.
     *
     * Prototype sebelum LSTM.
     *
     * Input:
     * - power
     * - current
     * - voltage
     * - power factor
     * - power delta
     */
    private function classifyFeature(NilmFeature $feature)
    {

        $power = (float) $feature->power;

        $current = (float) $feature->current;

        $powerFactor = (float) $feature->power_factor;



        /*
         * AC
         */
        if (
            $power >= 700 &&
            $power <= 1500
        ) {

            return [

                'device' => 'AC',

                'power' => $power,

                'confidence' => 0.75,

            ];

        }



        /*
         * Kipas
         *
         * Karakter:
         * - daya menengah
         * - beban motor
         * - power factor tidak sempurna
         */
        if (
            $power >= 35 &&
            $power <= 150 &&
            $powerFactor < 0.95
        ) {

            return [

                'device' => 'Kipas',

                'power' => $power,

                'confidence' => 0.70,

            ];

        }



        /*
         * Lampu
         */
        if (
            $power >= 5 &&
            $power < 35
        ) {

            return [

                'device' => 'Lampu',

                'power' => $power,

                'confidence' => 0.75,

            ];

        }



        /*
         * Tidak dikenali
         */
        return [

            'device' => 'Unknown',

            'power' => $power,

            'confidence' => 0.30,

        ];

    }
}