<?php

namespace App\Aspects;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ytake\LaravelAspect\Annotation\Aspect;
use Ytake\LaravelAspect\Annotation\Around;
use Ytake\LaravelAspect\Annotation\Pointcut;
use Ytake\LaravelAspect\Annotation\Before;

/**
 * @Aspect
 */
class TripleServiceMonitoringAspect
{
    private $ontologyUri = 'http://www.semanticweb.org/ana/ontologies/'; // Define your ontology URI here

    /**
     * Pointcut for methods in TripleService
     */
    /**
     * @Pointcut("execution(* App\Services\TripleService::insertTriples(..)) || execution(* App\Services\TripleService::executeScript(..))")
     */
    public function monitorTripleServiceMethods()
    {
    }

    /**
     * @Around("monitorTripleServiceMethods()")
     */
    public function validateInputs($joinPoint)
    {
        $args = $joinPoint->getArgs();

        foreach ($args as $arg) {
            if (is_string($arg)) {
                // Validate string arguments
                if (!str_starts_with($arg, $this->ontologyUri)) {
                    throw new \InvalidArgumentException("String argument must start with the ontology URI: {$this->ontologyUri}");
                }
            } elseif (is_array($arg)) {
                // Validate array arguments
                foreach ($arg as $item) {
                    if (is_string($item) && !str_starts_with($item, $this->ontologyUri)) {
                        throw new \InvalidArgumentException("All string elements in the array must start with the ontology URI: {$this->ontologyUri}");
                    }
                }
            } else {
                throw new \InvalidArgumentException("Invalid argument type: " . gettype($arg));
            }
        }

        // Proceed with the original method if validation passes
        return $joinPoint->proceed();
    }
}