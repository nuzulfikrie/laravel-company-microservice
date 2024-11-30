<?php

namespace CompanyManagement;

use Illuminate\Support\ServiceProvider;
use CompanyManagement\Clients\CompanyManagementClient;

class CompanyManagementServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/company-management-auth.php' => config_path('company-management-auth.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/company-management-auth.php', 'company-management-auth'
        );

        $this->app->singleton(CompanyManagementClient::class, function ($app) {
            $config = config('company-management-auth');
            return new CompanyManagementClient(
                $config['base_url'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect_uri']
            );
        });
    }
}