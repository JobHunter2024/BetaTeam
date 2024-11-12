<?php

namespace App\Aspects;

// use Go\Aop\AspectKernel;
// use Go\Aop\AspectContainer;
// use Go\Aop\Pointcut;
// use Go\Aop\Aspect;

//use Go\Core\AspectKernel;
use Go\Core\AspectKernel;
use Go\Core\AspectContainer;
use App\Aspects\CustomLogger;
use Monolog\Handler\StreamHandler;

class CustomAspectKernel extends AspectKernel
{
    protected function configureAop(AspectContainer $container)
    {
        // Register your aspects here
        $container->registerAspect(new LoggingAspect());
    }
}


















// class CustomAspectKernel extends AspectKernel
// {
//     /**
//      * Override the `init` method to configure your aspects
//      *
//      * @param AspectContainer $container
//      */
//     protected function init(AspectContainer $container)
//     {
//         // Register an aspect
//         $container->registerAspect(new MyAspect());
//     }

//     /**
//      * Configure AOP settings such as classes to be woven.
//      *
//      * @return array
//      */
//     protected function getOptions()
//     {
//         return [
//             'debug' => true, // Enable debug mode
//             'appDir' => __DIR__ . '/app', // Application directory
//             'cacheDir' => __DIR__ . '/cache', // Cache directory for the AOP framework
//             'includePaths' => [__DIR__ . '/app'] // Paths to include
//         ];
//     }
// } -->
