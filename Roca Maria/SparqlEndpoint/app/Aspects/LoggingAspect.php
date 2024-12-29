<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Log;
use Ytake\LaravelAspect\Annotation\After;
use Ytake\LaravelAspect\Annotation\Aspect;
use Ytake\LaravelAspect\Annotation\Before;
use Ytake\LaravelAspect\Annotation\Pointcut;

/**
 * @Aspect
 */
class LoggingAspect
{
    /**
     * @Pointcut("execution(public App\Http\Controllers\*->*(*))")
     */
    public function allPublicMethods()
    {
    }

    /**
     * @Before("allPublicMethods()")
     */
    public function logBefore($joinPoint)
    {
        $methodName = $joinPoint->getMethod()->getName();
        Log::info("Entering method: {$methodName}");
    }

    /**
     * @After("allPublicMethods()")
     */
    public function logAfter($joinPoint)
    {
        $methodName = $joinPoint->getMethod()->getName();
        Log::info("Exiting method: {$methodName}");
    }
}