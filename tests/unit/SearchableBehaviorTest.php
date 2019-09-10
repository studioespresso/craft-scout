<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Category;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\fields\Entries;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use FakeEngine;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use Tightenco\Collect\Support\Collection;
use UnitTester;

class SearchableBehaviorTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @var \craft\models\Section */
    private $section;

    /** @var \rias\scout\behaviors\SearchableBehavior */
    private $element;

    /** @var Scout */
    private $scout;

    protected function _before()
    {
        parent::_before();

        $section = new Section([
            'name'         => 'News',
            'handle'       => 'news',
            'type'         => Section::TYPE_CHANNEL,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId'           => Craft::$app->getSites()->getPrimarySite()->id,
                    'enabledByDefault' => true,
                    'hasUrls'          => true,
                    'uriFormat'        => 'foo/{slug}',
                    'template'         => 'foo/_entry',
                ]),
            ],
        ]);

        Craft::$app->getSections()->saveSection($section);

        $this->section = $section;

        $scout = new Scout('scout');
        $scout->setSettings([
            'engine'  => FakeEngine::class,
            'sync'    => true,
            'queue'   => false,
            'indices' => [
                ScoutIndex::create('Blog')
                    ->elementType(Entry::class)
                    ->criteria(function (EntryQuery $query) {
                        return $query->sectionId($this->section->id);
                    }),
                ScoutIndex::create('no-blog')
                    ->elementType(Entry::class)
                    ->criteria(function (EntryQuery $query) {
                        return $query->sectionId(100);
                    }),
                ScoutIndex::create('categories')
                    ->elementType(Category::class)
                    ->criteria(function (CategoryQuery $query) {
                        return $query->siteId(2);
                    }),
            ],
        ]);
        $scout->init();
        $this->scout = $scout;

        $element = new Entry();
        $element->siteId = 1;
        $element->sectionId = $this->section->id;
        $element->typeId = $this->section->getEntryTypes()[0]->id;
        $element->title = 'A new beginning.';
        $element->slug = 'a-new-beginning';

        Craft::$app->getElements()->saveElement($element);

        $this->element = $element;

        Craft::$app->getCache()->flush();
    }

    /** @test * */
    public function it_can_get_related_elements()
    {
        $relationField = new Entries([
            'name'   => 'Entry field',
            'handle' => 'entryField',
        ]);
        Craft::$app->getFields()->saveField($relationField);

        Craft::$app->getRelations()->saveRelations($relationField, $this->element, [$this->element->id]);

        $this->assertInstanceOf(Collection::class, $this->element->getRelatedElements());
        $this->assertEquals($this->element->id, $this->element->getRelatedElements()[0]->id);
    }

    /** @test * */
    public function it_can_get_indices_that_it_applies_to()
    {
        $indices = $this->element->getIndices();

        $this->assertEquals(2, $indices->count());
        $this->assertEquals('Blog', $indices->first()->indexName);
    }

    /** @test * */
    public function it_can_test_if_it_validates_an_index()
    {
        $indices = $this->element->getIndices();

        $indices = $indices->filter(function (ScoutIndex $scoutIndex) {
            return $this->element->validatesCriteria($scoutIndex);
        });

        $this->assertEquals(1, $indices->count());
        $this->assertEquals('Blog', $indices->first()->indexName);
    }

    /** @test * */
    public function it_can_get_initialized_engines_that_it_applies_to()
    {
        $engines = $this->element->searchableUsing();
        /** @var \rias\scout\engines\Engine $engine */
        $engine = $engines->first();

        $this->assertEquals(2, $engines->count());
        $this->assertInstanceOf(Engine::class, $engine);
        $this->assertInstanceOf(FakeEngine::class, $engine);
    }

    /** @test * */
    public function it_can_index_itself()
    {
        $this->assertFalse(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));

        $this->element->searchable();

        $this->assertTrue(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));
    }

    /** @test * */
    public function it_can_index_itself_queued()
    {
        $this->assertFalse(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));

        $this->scout->setSettings(['queue' => true]);

        $this->element->searchable();

        $this->assertFalse(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));

        $this->tester->assertPushedToQueue(sprintf(
            'Indexing “%s” in “Blog”',
            ($this->element->title ?? $this->element->id)
        ));

        Craft::$app->getQueue()->run();

        $this->assertTrue(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));
    }

    /** @test * */
    public function it_can_unindex_itself()
    {
        Craft::$app->getCache()->set("scout-Blog-{$this->element->id}", true);

        $this->assertTrue(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));

        $this->element->unsearchable();

        $this->assertFalse(Craft::$app->getCache()->exists("scout-Blog-{$this->element->id}"));
    }

    /** @test * */
    public function it_can_transform_to_a_searchable_array()
    {
        $this->assertContains([
            'title' => 'A new beginning.',
        ], $this->element->toSearchableArray($this->element->getIndices()->first()));
    }
}
