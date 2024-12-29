<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Http\Controllers\TriplesController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TripleService;
use App\Services\SparqlService;

class TriplesControllerTest extends TestCase
{
    protected $tripleServiceMock;
    protected $sparqlServiceMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tripleServiceMock = Mockery::mock(TripleService::class);
        $this->sparqlServiceMock = Mockery::mock(SparqlService::class);

        $this->controller = new TriplesController($this->tripleServiceMock, $this->sparqlServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndex()
    {
        $response = $this->controller->index();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTest()
    {
        $response = $this->controller->test();
        $this->assertEquals("Hello, World!", $response);
    }

    public function testStoreJobTriplesSuccess()
    {
        $request = Request::create('/store-job-triples', 'POST', [], [], [], [], json_encode(['key' => 'value']));

        $this->tripleServiceMock->shouldReceive('executeScript')
            ->once()
            ->with(['key' => 'value'])
            ->andReturn(['data']);

        $this->tripleServiceMock->shouldReceive('validateJobData')
            ->once()
            ->with(['data']);

        $this->tripleServiceMock->shouldReceive('prepareTriples')
            ->once()
            ->with(['data'])
            ->andReturn(['triples']);

        $this->tripleServiceMock->shouldReceive('insertTriples')
            ->once()
            ->with(['triples'])
            ->andReturn('response');

        $response = $this->controller->storeJobTriples($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Triples inserted successfully.', 'response' => 'response']),
            $response->getContent()
        );
    }

    public function testStoreJobTriplesFailure()
    {
        $request = Request::create('/store-job-triples', 'POST', [], [], [], [], json_encode(['key' => 'value']));

        $this->tripleServiceMock->shouldReceive('executeScript')
            ->once()
            ->with(['key' => 'value'])
            ->andThrow(new \Exception('Error'));

        $response = $this->controller->storeJobTriples($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Error']),
            $response->getContent()
        );
    }

    public function testGetSparqlData()
    {
        $this->sparqlServiceMock->shouldReceive('query')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn(['results']);

        $response = $this->controller->getSparqlData();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['results']),
            $response->getContent()
        );
    }

    // Additional tests for other methods can be added similarly
}