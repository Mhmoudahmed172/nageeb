<?php

namespace Tests;

use App\Models\Region;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function regionId(string $code = 'gaza'): int
    {
        return (int) Region::query()->where('code', $code)->value('id');
    }
}
