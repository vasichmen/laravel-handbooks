<?php


namespace Laravel\Handbooks;

use Illuminate\Support\ServiceProvider;

class LaravelHandbooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/handbooks.php' => config_path('handbooks.php'),], 'config');

        $this->loadRoutesFrom(__DIR__ . '/../routes/handbooks.php');
        $this->mergeConfigFrom(__DIR__ . '/../config/handbooks.php', 'handbooks');
    }

}