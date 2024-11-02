<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Triple;
use App\Repositories\TripleRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use App\Services\SparqlService;

class TriplesControllerTest extends TestCase
{
    // use RefreshDatabase;

    // protected $tripleRepository;
    // protected $sparqlService;

    // public function __construct(SparqlService $sparqlService)
    // {
    //     $this->sparqlService = $sparqlService;
    // }

    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     //   $this->tripleRepository = Mockery::mock(TripleRepositoryInterface::class);
    //     //  $this->app->instance(TripleRepositoryInterface::class, $this->tripleRepository);
    // }

    // /** @test */
    // public function it_can_list_triples()
    // {
    //     $triples = Triple::factory()->count(2)->make();

    //     $this->tripleRepository->shouldReceive('all')
    //         ->once()
    //         ->andReturn($triples);

    //     $this->get('/triples')
    //         ->assertStatus(200)
    //         ->assertJsonCount(2);
    // }

    // /** @test */
    // public function it_can_create_a_triple()
    // {
    //     $data = [
    //         'subject' => 'https://www.wikidata.org/wiki/Q42', // Example subject: Douglas Adams
    //         'predicate' => 'http://www.wikidata.org/prop/direct/P106', // Example predicate: instance of
    //         'object' => 'https://www.wikidata.org/wiki/Q9143', // Example object: writer
    //     ];

    //     $this->tripleRepository->shouldReceive('create')
    //         ->once()
    //         ->with($data)
    //         ->andReturn(new Triple($data));

    //     $this->post('/triples', $data)
    //         ->assertStatus(201)
    //         ->assertJson($data);
    // }

    // /** @test */
    // public function it_can_show_a_triple()
    // {
    //     $triple = Triple::factory()->create();

    //     $this->tripleRepository->shouldReceive('find')
    //         ->once()
    //         ->with($triple->id)
    //         ->andReturn($triple);

    //     $this->get('/triples/' . $triple->id)
    //         ->assertStatus(200)
    //         ->assertJson(['subject' => $triple->subject]);
    // }

    // /** @test */
    // public function it_can_update_a_triple()
    // {
    //     $triple = Triple::factory()->create(['subject' => 'Old Subject']);

    //     $data = [
    //         'subject' => 'Updated Subject',
    //         'predicate' => 'http://xmlns.com/foaf/0.1/knows', // knows
    //         'object' => 'https://www.w3.org/People/Berners-Lee/card#me', // A reference to himself
    //     ];
    //     $this->tripleRepository->shouldReceive('update')
    //         ->once()
    //         ->with($triple->id, $data)
    //         ->andReturn(new Triple($data));

    //     $this->put('/triples/' . $triple->id, $data)
    //         ->assertStatus(200)
    //         ->assertJson($data);
    // }

    // /** @test */
    // public function it_can_delete_a_triple()
    // {
    //     $triple = Triple::factory()->create();

    //     $this->tripleRepository->shouldReceive('delete')
    //         ->once()
    //         ->with($triple->id);

    //     $this->delete('/triples/' . $triple->id)
    //         ->assertStatus(204);

    //     $this->assertDatabaseMissing('triples', ['id' => $triple->id]);
    // }

    // /** @test */
    // public function it_requires_subject_predicate_and_object_when_creating_a_triple()
    // {
    //     $response = $this->post('/triples', []);

    //     $response->assertStatus(422)
    //         ->assertJsonValidationErrors(['subject', 'predicate', 'object']);
    // }
}