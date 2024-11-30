<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DBCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $databaseName = $this->argument('name');

        $this->info("Creating database: $databaseName");

        // Create database
        DB::statement("CREATE DATABASE $databaseName");

        $this->info("Database $databaseName created successfully");
    }
}