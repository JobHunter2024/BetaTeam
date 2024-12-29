<?php

namespace Tests\Unit;

use App\Services\TripleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripleServiceTest extends TestCase
{
    use RefreshDatabase; // Use this if you need to reset the database for each test

    public function testInsertTriplesWithValidInput()
    {
        // Create an instance of the TripleService
        $tripleService = new TripleService();

        // Prepare valid data
        $validTriples = "
            _:job rdf:type <http://example.org/JobPosting> ;
                :hasTitle \"http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#Company\" ;
                :hasCompany \"http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#Company\" ;
                :datePosted \"2024-10-01\" ;
                :employmentType \"FullTime\" ;
                :experienceRequired \"2\" ;
                :locationType \"Office\" ;
        ";

        // Call the insertTriples method with valid triples
        $response = $tripleService->insertTriples($validTriples);

        // Assert the response
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('The triples already exist in the dataset.', $response->getData()->message);

        // $this->assertEquals(201, $response->getStatusCode());

        // $this->assertEquals('Triples inserted successfully.', $response->getData()->message);


    }

    public function testInsertTriplesWithInvalidInput()
    {
        // Create an instance of the TripleService
        $tripleService = new TripleService();

        // Prepare invalid data (does not start with the ontology URI)
        $invalidTriples = "
            _:job rdf:type <http://example.org/JobPosting> ;
                :hasTitle \"http://invalid.uri/Company\" ; // Invalid URI
                :hasCompany \"http://invalid.uri/Company\" ;
                :datePosted \"2024-10-01\" ;
                :employmentType \"FullTime\" ;
                :experienceRequired \"2\" ;
                :locationType \"Office\" ;
        ";

        // Expect a general Exception to be thrown due to invalid URI format
        $this->expectException(\Exception::class);  // Expecting a generic Exception
        $this->expectExceptionMessage('Failed to query Fuseki:');  // Expect part of the error message

        // Call the insertTriples method with invalid triples
        $tripleService->insertTriples($invalidTriples);
    }

}
