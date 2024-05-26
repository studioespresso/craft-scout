<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Entries;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use FakeEngine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;

class MultiElementEventHandlersTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @var \craft\models\Section */
    private $section;

    /** @var \craft\models\CategoryGroup */
    private $catgroup;

    /** @var \craft\elements\Entry */
    private $element;

    /** @var \craft\elements\Entry */
    private $element2;

    /** @var \craft\elements\Category */
    private $category;

    /** @var \craft\elements\Category */
    private $category2;

    /** @var \rias\scout\Scout */
    private $scout;

    protected function _before()
    {
        parent::_before();

        $section = new Section([
            'name' => 'News',
            'handle' => 'news',
            'type' => Section::TYPE_CHANNEL,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => 'foo/{slug}',
                    'template' => 'foo/_entry',
                ]),
            ],
        ]);
        Craft::$app->getSections()->saveSection($section);

        $catgroup = new CategoryGroup([
            'name' => 'News Categories',
            'handle' => 'newsCategories',
            'maxLevels' => 1,
            'defaultPlacement' => 'end',
            'siteSettings' => [
                Craft::$app->getSites()->getPrimarySite()->id =>
                    new CategoryGroup_SiteSettings([
                        'hasUrls' => true,
                        'uriFormat' => 'categories/{slug}',
                    ])
            ],
        ]);

        Craft::$app->getCategories()->saveGroup($catgroup);

        $this->section = $section;
        $this->catgroup = $catgroup;

        $scoutIndex = new ScoutIndex('Items');
        $scoutIndex->elementType(Entry::class);
        $scoutIndex->getElements(function () {
            return [
                Entry::find()->section('news'),
                Category::find()->group('newsCategories')
            ];
        });

        $scoutIndex->transformer = function ($entry) {
            return [
                'title' => $entry->title,
            ];
        };
        $scout = Scout::getInstance();
        $scout->setSettings([
            'indices' => [$scoutIndex],
            'engine' => FakeEngine::class,
            'queue' => false,
        ]);

        $this->scout = $scout;

        $element = new Entry();
        $element->siteId = 1;
        $element->sectionId = $this->section->id;
        $element->typeId = $this->section->getEntryTypes()[0]->id;
        $element->title = 'A new beginning.';
        $element->slug = 'a-new-beginning';
        Craft::$app->getElements()->saveElement($element);
        $this->element = $element;

        $element2 = new Entry();
        $element2->siteId = 1;
        $element2->sectionId = $this->section->id;
        $element2->typeId = $this->section->getEntryTypes()[0]->id;
        $element2->title = 'Second element.';
        $element2->slug = 'second-element';
        Craft::$app->getElements()->saveElement($element2);
        $this->element2 = $element2;

        $category = new Category();
        $category->siteId = 1;
        $category->groupId = $this->catgroup->id;
        $category->title = "A new category";
        $category->slug = "a-new-category";
        Craft::$app->getElements()->saveElement($category);
        $this->category = $category;

        $category2 = new Category();
        $category2->siteId = 1;
        $category2->groupId = $this->catgroup->id;
        $category2->title = "Second category";
        $category2->slug = "second-category";
        Craft::$app->getElements()->saveElement($category2);
        $this->category2 = $category2;

    }



    public function _after()
    {
        $section = Craft::$app->getSections()->getSectionByHandle('news');
        Craft::$app->getSections()->deleteSection($section);

        $catgroup = Craft::$app->getCategories()->getGroupByHandle('newsCategories');
        Craft::$app->getCategories()->deleteGroup($catgroup);

        $field = Craft::$app->getFields()->getFieldByHandle('entryField');
        if ($field) {
            Craft::$app->getFields()->deleteField($field);
        }
        parent::_after();
    }

    /** @test * */
    public function it_can_test_if_it_validates_an_index()
    {
        $indices = $this->element->getIndices();

        $indices = $indices->filter(function(ScoutIndex $scoutIndex) {
            return $this->element->validatesCriteria($scoutIndex);
        });

        $this->assertEquals(1, $indices->count());
        $this->assertEquals('Items', $indices->first()->indexName);
    }

    /** @test * */
    public function it_indexes_entries_and_categories()
    {
        Craft::$app->getCache()->set("scout-Items-{$this->element->id}-updateCalled", 0);
        Craft::$app->getCache()->set("scout-Items-{$this->category->id}-updateCalled", 0);

        $this->assertEquals(0, Craft::$app->getCache()->get("scout-Items-{$this->element->id}-updateCalled"));
        $this->assertEquals(0, Craft::$app->getCache()->get("scout-Items-{$this->category->id}-updateCalled"));

        Craft::$app->getElements()->saveElement($this->element);
        Craft::$app->getElements()->saveElement($this->category);

        $this->assertEquals(1, Craft::$app->getCache()->get("scout-Items-{$this->element->id}-updateCalled"));
        $this->assertEquals(1, Craft::$app->getCache()->get("scout-Items-{$this->category->id}-updateCalled"));
    }

}
