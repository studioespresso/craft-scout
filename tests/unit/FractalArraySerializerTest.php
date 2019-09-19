<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use FractalMockTransformers;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use rias\scout\serializer\AlgoliaSerializer;

class FractalArraySerializerTest extends Unit
{
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
            ->createData(new Collection($this->entries, new FractalMockTransformers(), 'entries'))
            ->toArray();

        $this->assertCount(2, $dataSet);
    }

    /** @test **/
    public function nested_transformer_should_be_object()
    {
        $dataSet = (new Manager())
            ->setSerializer(new AlgoliaSerializer())
            ->createData(new Collection($this->entries, new FractalMockTransformers(), 'entries'))
            ->toArray();

        $this->assertArrayHasKey('entries', $dataSet);
    }
}