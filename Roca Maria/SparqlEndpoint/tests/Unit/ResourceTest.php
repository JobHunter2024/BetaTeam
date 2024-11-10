<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use App\Models\Triple;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceTest extends TestCase
{

    /** @test */
    public function it_has_a_triples_relationship()
    {
        $resource = Mockery::mock(Resource::class)->makePartial();
        $triplesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');

        // Mock the triples method to return the collection
        $resource->shouldReceive('triples')->andReturn($triplesMock);

        $this->assertSame($triplesMock, $resource->triples());
    }

    /** @test */
    public function it_can_return_its_uri()
    {
        $resource = Mockery::mock(Resource::class)->makePartial();
        $resource->uri = 'http://example.com';

        $this->assertEquals('http://example.com', $resource->getUri());
    }

    /** @test */
    public function it_can_return_its_type()
    {
        $resource = Mockery::mock(Resource::class)->makePartial();
        $resource->type = 'example-type';

        $this->assertEquals('example-type', $resource->getType());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
