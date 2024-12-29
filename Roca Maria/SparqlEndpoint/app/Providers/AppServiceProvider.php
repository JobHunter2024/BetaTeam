<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Ytake\LaravelAspect\Aspect\AspectManager;
use Ytake\LaravelAspect\Aspect\AspectKernel;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // No need to manually register aspects here
    }

    public function boot()
    {
        // Initialize the aspect manager
        // AspectManager::getInstance()->init();

        // AspectKernel::getInstance()->init();
    }
}