<?php

namespace App\Aspects;

use Go\Aop\Aspect;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Pointcut;
use Illuminate\Support\Facades\Log;

namespace App\Aspects;

use Go\Aop\Aspect;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Illuminate\Support\Facades\Log;
use Go\Aop\Intercept\MethodInvocation;

class LoggingAspect implements Aspect
{
    public function __construct()
    {
        Log::info('LoggingAspect instantiated');
    }

    /**
     * Log before entering any public method in controllers
     * @Before("execution(public App\Http\Controllers\*->*(*))")
     */
    public function beforeMethod(MethodInvocation $invocation)
    {
        Log::info('Entering method: ' . $invocation->getMethod()->getName());
    }

    /**
     * Log after exiting any public method in controllers
     * @After("execution(public App\Http\Controllers\*->*(*))")
     */
    public function afterMethod(MethodInvocation $invocation)
    {
        Log::info('Exiting method: ' . $invocation->getMethod()->getName());
    }

}
