<?php

namespace Tests\Unit\Aspects;

use Ray\Aop\Joinpoint;
use PHPUnit\Framework\TestCase;
use App\Aspects\TripleServiceMonitoringAspect;

class TripleServiceMonitoringAspectTest extends TestCase
{
    private TripleServiceMonitoringAspect $aspect;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aspect = new TripleServiceMonitoringAspect();
    }

    public function testValidateInputsThrowsExceptionForInvalidStringArgument()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $joinPoint->method('getArgs')->willReturn(['invalid_string']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("String argument must start with the ontology URI: http://www.semanticweb.org/ana/ontologies/");

        $this->aspect->validateInputs($joinPoint);
    }

    public function testValidateInputsThrowsExceptionForInvalidArrayElement()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $joinPoint->method('getArgs')->willReturn([['http://www.semanticweb.org/ana/ontologies/validElement', 'invalidElement']]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("All string elements in the array must start with the ontology URI: http://www.semanticweb.org/ana/ontologies/");

        $this->aspect->validateInputs($joinPoint);
    }

    public function testValidateInputsHandlesEmptyStringArguments()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $joinPoint->method('getArgs')->willReturn(['']);
        $joinPoint->expects($this->once())->method('proceed');

        $result = $this->aspect->validateInputs($joinPoint);

        $this->assertNull($result);
    }

    public function testValidateInputsHandlesEmptyArrayArguments()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $joinPoint->method('getArgs')->willReturn([[]]);
        $joinPoint->expects($this->once())->method('proceed');

        $result = $this->aspect->validateInputs($joinPoint);

        $this->assertNull($result);
    }

    public function testValidateInputsMaintainsOriginalFunctionalityForValidInputs()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $validArgs = [
            'http://www.semanticweb.org/ana/ontologies/validString',
            ['http://www.semanticweb.org/ana/ontologies/validArrayElement1', 'http://www.semanticweb.org/ana/ontologies/validArrayElement2']
        ];
        $joinPoint->method('getArgs')->willReturn($validArgs);
        $joinPoint->expects($this->once())->method('proceed')->willReturn('expectedResult');

        $result = $this->aspect->validateInputs($joinPoint);

        $this->assertEquals('expectedResult', $result);
    }

    public function testValidateInputsMaintainsOriginalFunctionalityForExecuteScriptMethod()
    {
        $joinPoint = $this->createMock(Joinpoint::class);
        $validArgs = [
            'http://www.semanticweb.org/ana/ontologies/validScriptArgument',
            ['http://www.semanticweb.org/ana/ontologies/validArrayElement1', 'http://www.semanticweb.org/ana/ontologies/validArrayElement2']
        ];
        $joinPoint->method('getArgs')->willReturn($validArgs);
        $joinPoint->expects($this->once())->method('proceed')->willReturn('scriptExecutionResult');

        $result = $this->aspect->validateInputs($joinPoint);

        $this->assertEquals('scriptExecutionResult', $result);
    }
}
