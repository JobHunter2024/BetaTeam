<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Triple;
use Mockery;

class TripleTest extends TestCase
{
    /** @test */
    public function it_belongs_to_a_dataset()
    {
        $triple = new Triple();
        $this->assertTrue(method_exists($triple, 'dataset'));
    }

    /** @test */
    public function it_belongs_to_a_resource()
    {
        $triple = new Triple();
        $this->assertTrue(method_exists($triple, 'resource'));
    }

    /** @test */
    public function it_belongs_to_a_property()
    {
        $triple = new Triple();
        $this->assertTrue(method_exists($triple, 'property'));
    }

    /** @test */
    public function it_belongs_to_an_rdf_node()
    {
        $triple = new Triple();
        $this->assertTrue(method_exists($triple, 'rdfNode'));
    }

    /** @test */
    public function it_can_get_subject()
    {
        $triple = Mockery::mock(Triple::class)->makePartial();
        $triple->resource_id = 1;
        $this->assertEquals(1, $triple->getSubject());
    }

    /** @test */
    public function it_can_get_predicate()
    {
        $triple = Mockery::mock(Triple::class)->makePartial();
        $triple->property_id = 2;
        $this->assertEquals(2, $triple->getPredicate());
    }

    /** @test */
    public function it_can_get_object()
    {
        $triple = Mockery::mock(Triple::class)->makePartial();
        $triple->rdf_node_id = 3;
        $this->assertEquals(3, $triple->getObject());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}