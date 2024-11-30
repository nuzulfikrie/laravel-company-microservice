<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

trait DatabaseMigrations
{
    public function setupDatabase()
    {
        $this->dropAllTables();
        $this->runMigrations();
    }

    protected function dropAllTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $tables = $this->getTables();
            $this->dropTables($tables);
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function runMigrations()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            Artisan::call('migrate:fresh', [
                '--database' => 'testing',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Migration error: ' . $e->getMessage());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function getTables(): array
    {
        return array_map(function ($table) {
            return get_object_vars($table)[key(get_object_vars($table))];
        }, DB::select('SHOW TABLES'));
    }

    protected function dropTables(array $tables)
    {
        collect($tables)->each(function ($table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        });
    }
} 