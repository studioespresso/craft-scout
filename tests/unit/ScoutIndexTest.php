<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\queue\Queue;
use rias\scout\ElementTransformer;
use rias\scout\IndexSettings;
use rias\scout\ScoutIndex;
use UnitTester;

class ScoutIndexTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @test * */
    public function it_sets_the_name()
    {
        $index = new ScoutIndex('Blog');

        $this->assertEquals('Blog', $index->indexName);
    }

    /** @test * */
    public function it_sets_the_element_type()
    {
        $index = new ScoutIndex('Blog');

        $index->elementType(Category::class);

        $this->assertEquals(Category::class, $index->elementType);
    }

    /** @test * */
    public function it_has_entry_as_a_default_element_type()
    {
        $index = new ScoutIndex('Blog');

        $this->assertEquals(Entry::class, $index->elementType);
    }

    /** @test * */
    public function it_throws_an_error_on_an_invalid_element_type()
    {
        $index = new ScoutIndex('Blog');
        $class = Queue::class;

        $this->expectExceptionMessage("Invalid Element Type {$class}");

        $index->elementType($class);
    }

    /** @test * */
    public function it_sets_the_transformer()
    {
        $index = new ScoutIndex('Blog');

        $this->assertNull($index->transformer);

        $index->transformer(ElementTransformer::class);

        $this->assertEquals(ElementTransformer::class, $index->transformer);
    }

    /** @test * */
    public function it_uses_element_transformer_by_default()
    {
        $index = new ScoutIndex('Blog');

        $this->assertTrue($index->getTransformer() instanceof ElementTransformer);
    }

    /** @test * */
    public function it_can_use_the_container_to_create_a_transformer()
    {
        $index = new ScoutIndex('Blog');
        $index->transformer('\rias\scout\ElementTransformer');

        $this->assertTrue($index->getTransformer() instanceof ElementTransformer);
    }

    /** @test * */
    public function it_sets_index_settings()
    {
        $index = new ScoutIndex('Blog');
        $indexSettings = new IndexSettings();

        $this->assertNull($index->indexSettings);

        $index->indexSettings($indexSettings);

        $this->assertEquals($indexSettings, $index->indexSettings);
    }

    /** @test * */
    public function it_throws_an_exception_if_no_query_is_returned_from_criteria()
    {
        $index = new ScoutIndex('Blog');

        $this->expectExceptionMessage("You must return a valid ElementQuery from the criteria function.");

        $index->criteria(function ($query) {
            return null;
        });
    }

    /** @test * */
    public function it_sets_the_primary_site_id_if_the_criteria_has_no_site_id()
    {
        $index = new ScoutIndex('Blog');

        $index->criteria(function ($query) {
            return $query;
        });

        $this->assertEquals(Craft::$app->getSites()->getPrimarySite()->id, $index->criteria->siteId);
    }
}