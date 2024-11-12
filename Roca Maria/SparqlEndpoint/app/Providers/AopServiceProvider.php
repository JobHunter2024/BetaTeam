<?php

namespace App\Providers;

use Monolog\Logger;
use Go\Core\AspectKernel;
use Go\Aop\Framework\Proxy;
use Go\Aop\Aspect as GoAspect;
use Monolog\Handler\StreamHandler;
use App\Aspects\CustomAspectKernel;
use App\Aspects\LoggingAspect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AopServiceProvider extends ServiceProvider
{
    // public function register()
    // {
    //     $this->app->singleton(LoggingAspect::class);
    // }

    // public function boot()
    // {
    //     $cacheDir = storage_path('logs');
    //     CustomAspectKernel::getInstance()->init([
    //         'debug' => true,
    //         'appDir' => __DIR__,  // Path to your application directory
    //         'cacheDir' => $cacheDir,
    //     ]);
    // }

    public function register()
    {
        //Register the aspect kernel
        $this->app->singleton(CustomAspectKernel::class, function ($app) {
            return CustomAspectKernel::getInstance();
        });
    }

    public function boot()
    {
        $cacheDir = storage_path('logs');  // Default cache folder in storage

        //  if (!is_dir($cacheDir)) {
        //      mkdir($cacheDir, 0775, true);  // Ensure the directory exists
        //   }

        $kernel = $this->app->make(CustomAspectKernel::class);
        $kernel->init([
            'debug' => true,
            'appDir' => __DIR__,  // Path to your application directory
            'cacheDir' => $cacheDir,  // Provide a valid cache directory
        ]);

        // Make sure the aspect is applied
        $container = $kernel->getContainer();
        $container->set(\App\Aspects\LoggingAspect::class, new \App\Aspects\LoggingAspect());
    }
}