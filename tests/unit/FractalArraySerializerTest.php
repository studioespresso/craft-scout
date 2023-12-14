<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use FractalMockTransformers;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use rias\scout\serializer\Serializer;

class FractalArraySerializerTest extends Unit
{
    protected $entries = [[
        'title' => 'The Great Scout',
        'id' => 123,
    ], [
        'title' => 'The Great Scout Vol 2',
        'id' => 124,
    ]];

    /** @test **/
    public function collection_should_be_index_array()
    {
        $dataSet = (new Manager())->setSerializer(new Serializer())
            ->createData(new Collection($this->entries, new FractalMockTransformers()))
            ->toArray();

        $this->assertArrayHasKey('0', $dataSet);
    }

    /** @test **/
    public function collection_should_be_multidimensional_array()
    {
        $dataSet = (new Manager())
            ->setSerializer(new Serializer())
            ->createData(new Collection($this->entries, new FractalMockTransformers(), 'entries'))
            ->toArray();

        $this->assertArrayHasKey('entries', $dataSet);
    }
}
