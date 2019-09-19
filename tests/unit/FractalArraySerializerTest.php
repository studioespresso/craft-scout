<?php 

namespace yournamespace\tests;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;

use Codeception\Test\Unit;
use rias\scout\serializer\AlgoliaSerializer;
use UnitTester;

class FractalArraySerializerTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $dataSet;
    
    protected function _before()
    {
        $manager = new Manager();
        $this->dataSet = $manager->setSerializer(new AlgoliaSerializer())
            ->createData(new Item($book, new yournamespace\transformer\BlogTransformer))
            ->toArray();
    }

    // protected function _after()
    // {
    // }

    // tests
    public function transformer_output_should_not_have_nested_data_attribute()
    {
        $manager = new Manager();
        $book = [
            'title' => 'The Great Scout',
            'id'    => 123,
        ];

        var_dump("\n-----\n");
        var_dump($this->dataSet);
        var_dump("\n-----\n");
        $this->assertNull($this->dataSet['bookCategories']['data']);
    }

    // public function Transformer_Output_should_have_nested_data_attribute()
    // {

    // }
}