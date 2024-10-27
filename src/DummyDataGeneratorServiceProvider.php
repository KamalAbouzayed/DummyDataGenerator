<?php

namespace Kamal\DummyDataGenerator;

use Illuminate\Support\ServiceProvider;
use Kamal\DummyDataGenerator\Commands\GenerateDummyData;

class DummyDataGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateDummyData::class,
        ]);
    }

    public function boot()
    {
        // Publish configuration file if needed
        $this->publishes([
            __DIR__ . '/../config/dummy-data-generator.php' => config_path('dummy-data-generator.php'),
        ], 'config');
    }
}
