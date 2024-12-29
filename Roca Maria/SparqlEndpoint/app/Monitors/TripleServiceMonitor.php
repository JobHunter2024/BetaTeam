<?php

namespace App\Monitors;

use InvalidArgumentException;

class TripleServiceMonitor
{
    private $ontologyUri;

    public function __construct()
    {
        // Define the ontology URI prefix (e.g., from .env or config file)
        //$this->ontologyUri = config('ontology.uri', 'http://example.org#');
        $this->ontologyUri = 'http://example.org#';
    }

    /**
     * Validates the method arguments for TripleService methods.
     *
     * @param string $methodName The name of the method being monitored.
     * @param array $arguments The arguments passed to the method.
     *
     * @throws InvalidArgumentException
     */
    public function validateInputs(string $methodName, array $arguments)
    {
        // Keep track of entity names to ensure uniqueness
        $entityNames = [];

        foreach ($arguments as $arg) {
            if (is_string($arg)) {
                $this->validateEntity($methodName, $arg, $entityNames);
            } elseif (is_array($arg)) {
                foreach ($arg as $element) {
                    if (!is_string($element)) {
                        throw new InvalidArgumentException(
                            "Invalid argument type passed to $methodName: Only strings and arrays are allowed."
                        );
                    }
                    $this->validateEntity($methodName, $element, $entityNames);
                }
            } else {
                throw new InvalidArgumentException(
                    "Invalid argument type passed to $methodName: Only strings and arrays are allowed."
                );
            }
        }
    }

    /**
     * Validates a single entity name.
     *
     * @param string $methodName The name of the method being monitored.
     * @param string $entityName The entity name to validate.
     * @param array $entityNames The array of already validated entity names.
     *
     * @throws InvalidArgumentException
     */
    private function validateEntity(string $methodName, string $entityName, array &$entityNames)
    {
        // Check if the entity name is unique
        if (in_array($entityName, $entityNames)) {
            throw new InvalidArgumentException(
                "Duplicate entity name passed to $methodName: Entity names must be unique."
            );
        }

        // Add the entity name to the list of validated names
        $entityNames[] = $entityName;

        // Check if the entity name is a valid label (not the same as the identifier)
        if (strpos($entityName, $this->ontologyUri) === 0) {
            throw new InvalidArgumentException(
                "Invalid entity name passed to $methodName: Entity names must not be the same as the identifier."
            );
        }

        // Additional validation rules can be added here
        // For example, check for length, allowed characters, etc.
    }

}
