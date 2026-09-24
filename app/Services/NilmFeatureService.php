<?php

namespace App\Services;

use App\Models\EnergyLog;
use App\Models\NilmFeature;

class NilmFeatureService
{
    public function generate(EnergyLog $energyLog)
    {
        $previous = EnergyLog::where(
            'energy_meter_id',
            $energyLog->energy_meter_id
        )
        ->where(
            'id',
            '<',
            $energyLog->id
        )
        ->latest('id')
        ->first();


        $powerDelta = 0;
        $currentDelta = 0;


        if ($previous) {

            $powerDelta =
                (float)$energyLog->power -
                (float)$previous->power;


            $currentDelta =
                (float)$energyLog->current -
                (float)$previous->current;

        }


        return NilmFeature::updateOrCreate(

            [
                'energy_log_id' => $energyLog->id
            ],

            [

                'power_delta' => $powerDelta,

                'current_delta' => $currentDelta,

                'current' => $energyLog->current,

                'power' => $energyLog->power,

                'frequency' => $energyLog->frequency,

                'voltage' => $energyLog->voltage,

                'power_factor' => $energyLog->power_factor,

                'duration_seconds' => 60,

                'feature_time' =>
                    $energyLog->observed_at ??
                    now(),

            ]

        );
    }
}