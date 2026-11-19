<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Konfigurasi migrate:fresh untuk testing database terisolasi (SQLite in-memory).
     * Memasukkan tests/migrations agar tabel dasar legacy tersedia di SQLite
     * tanpa menyentuh atau memodifikasi database MySQL utama sama sekali.
     */
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
