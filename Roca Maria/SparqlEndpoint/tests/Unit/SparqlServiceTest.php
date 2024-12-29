<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\SparqlService;
use Exception;

class SparqlServiceTest extends TestCase
{
    protected $sparqlService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock environment variables for endpoints and authentication
        putenv('FUSEKI_QUERY_ENDPOINT=http://localhost:3030/jobHunterDataset/query');
        putenv('FUSEKI_UPDATE_ENDPOINT=http://localhost:3030/jobHunterDataset/update');
        putenv('SPARQL_USER=test_user');
        putenv('SPARQL_PASSWORD=test_password');

        $this->sparqlService = new SparqlService();
    }

    /**
     * @covers \App\Services\SparqlService::query
     */
    public function testQuerySuccess()
    {
        $query = 'SELECT * WHERE {?s ?p ?o}';
        $expectedResponse = ['results' => ['bindings' => []]];

        Http::fake([
            'http://localhost:3030/jobHunterDataset/query' => Http::response($expectedResponse, 200),
        ]);

        $response = $this->sparqlService->query($query);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testQueryFailure()
    {
        $query = 'SELECT * WHERE {?s ?p ?o}';

        Http::fake([
            'http://localhost:3030/jobHunterDataset/query' => Http::response('Error', 500),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SPARQL query failed:');

        $this->sparqlService->query($query);
    }

    public function testExecuteUpdateSuccess()
    {
        $sparqlQuery = 'INSERT DATA { <http://example.org> <http://example.org> <http://example.org> }';
        $expectedResponse = 'Update successful';

        Http::fake([
            'http://localhost:3030/jobHunterDataset/update' => Http::response($expectedResponse, 200),
        ]);

        $response = $this->sparqlService->executeUpdate($sparqlQuery);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testExecuteUpdateFailure()
    {
        $sparqlQuery = 'INSERT DATA { <http://example.org> <http://example.org> <http://example.org> }';

        Http::fake([
            'http://localhost:3030/jobHunterDataset/update' => Http::response('Error', 500),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SPARQL update failed:');

        $this->sparqlService->executeUpdate($sparqlQuery);
    }
}
