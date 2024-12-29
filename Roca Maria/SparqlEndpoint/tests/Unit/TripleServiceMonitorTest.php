<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Monitors\TripleServiceMonitor;
use InvalidArgumentException;

class TripleServiceMonitorTest extends TestCase
{
    public function test_validateInputs_withInvalidArgumentType()
    {
        $monitor = new TripleServiceMonitor();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid argument type passed to testMethod: Only strings and arrays are allowed.");

        $monitor->validateInputs('testMethod', [123]);
    }

    public function test_validateEntity_withDuplicateEntityNames()
    {
        $monitor = new TripleServiceMonitor();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Duplicate entity name passed to testMethod: Entity names must be unique.");

        $monitor->validateInputs('testMethod', ['entity1', 'entity1']);
    }

    public function test_validateEntity_withOntologyUriPrefix()
    {
        $monitor = new TripleServiceMonitor();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid entity name passed to testMethod: Entity names must not be the same as the identifier.");

        $monitor->validateInputs('testMethod', ['http://example.org#Entity']);
    }
}