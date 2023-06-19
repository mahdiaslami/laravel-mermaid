<?php

namespace Tests;

use MahdiAslami\Database\MermaidServiceProvider;
use Orchestra\Testbench\TestCase;

class ProviderTest extends TestCase
{
    protected function getPackageProviders($app) {
        return [MermaidServiceProvider::class];
    }

    public function test_ok()
    {
        $this->assertTrue(true);
    }
}
