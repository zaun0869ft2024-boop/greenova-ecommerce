<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait UsesIsolatedDatabase
{
    use RefreshDatabase;

    protected function migrateFreshUsing()
    {
        return [
            '--path' => [
                'tests/migrations',
                'database/migrations',
            ],
            '--realpath' => false,
        ];
    }
}
