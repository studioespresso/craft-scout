<?php 

namespace yournamespace\tests;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

use Codeception\Test\Unit;
use rias\scout\serializer\AlgoliaSerializer;
use UnitTester;
use FractalMockTransformers;

class FractalArraySerializerTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $entries = [[
        'title' => 'The Great Scout',
        'id'    => 123,
    ], [
        'title' => 'The Great Scout Vol 2',
        'id'    => 124,
    ]];

    /** @test **/
    public function nested_transformer_should_be_array()
    {
        $dataSet = (new Manager())->setSerializer(new AlgoliaSerializer())
            ->createData(new Collection($this->entries, new FractalMockTransformers))
            ->toArray();

        $this->assertCount(2, $dataSet);
    }

    /** @test **/
    public function nested_transformer_should_be_object()
    {
        $dataSet = (new Manager())
            ->setSerializer(new AlgoliaSerializer())
            ->createData(new Collection($this->entries, new FractalMockTransformers, 'entries'))
            ->toArray();

        $this->assertArrayHasKey('entries', $dataSet);
    }
}