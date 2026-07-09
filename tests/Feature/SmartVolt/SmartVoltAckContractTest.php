<?php

namespace Tests\Feature\SmartVolt;

use App\Http\Controllers\Api\EnergyApiController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartVoltAckContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_acknowledge_method_must_exist_because_api_route_exists(): void
    {
        $this->assertTrue(
            method_exists(EnergyApiController::class, 'acknowledge'),
            'Route /api/iot/esp/{esp_unit_id}/ack sudah ada, tetapi method EnergyApiController::acknowledge() belum dibuat.'
        );
    }
}