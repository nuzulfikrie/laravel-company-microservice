<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Dotenv\Dotenv;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Force testing environment
        $app['env'] = 'testing';

        // Load .env.testing file
        if (file_exists(__DIR__ . '/../.env.testing')) {

            $dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
            $dotenv->safeLoad();
        }

        return $app;
    }
}
