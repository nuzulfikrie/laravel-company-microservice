<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait DatabaseMigrations
{
    protected function runDatabaseMigrations(): void
    {
        $this->dropAllTables();
        Artisan::call('migrate');
        $this->beforeApplicationDestroyed(function () {
            $this->dropAllTables();
        });
    }

    protected function dropAllTables(): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . env('DB_DATABASE');

            foreach ($tables as $table) {
                DB::statement("DROP TABLE IF EXISTS `{$table->$tableKey}`");
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            Log::error('Error dropping tables: ' . $e->getMessage());
        }
    }
} 