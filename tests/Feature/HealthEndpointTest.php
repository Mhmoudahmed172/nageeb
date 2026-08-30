<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_returns_ok_json_without_secrets(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'app' => 'running',
            ])
            ->assertDontSee('APP_KEY')
            ->assertDontSee('DB_PASSWORD')
            ->assertDontSee('password', false);
    }
}
