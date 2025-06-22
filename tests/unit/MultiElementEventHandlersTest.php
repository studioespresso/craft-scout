<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Entries;
use craft\helpers\StringHelper;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\EntryType;
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

        $type = new EntryType([
            'name' => 'Article',
            'handle' => 'article',
            'hasTitleField' => true,
            'titleFormat' => null,
            'uid' => StringHelper::UUID(),
        ]);

        \Craft::$app->getEntries()->saveEntryType($type);
        $entryType = \Craft::$app->getEntries()->getEntryTypeByHandle('article');

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
            'entryTypes' => [
                $entryType
            ]
        ]);
        Craft::$app->getEntries()->saveSection($section);

        $primarySite = Craft::$app->getSites()->getPrimarySite();
        $catgroup = new CategoryGroup([
            'name' => 'News Categories',
            'handle' => 'newsCategories',
            'maxLevels' => 1,
            'defaultPlacement' => 'end',
            'siteSettings' => [
                new CategoryGroup_SiteSettings([
                    'siteId' => $primarySite->id,
                    'hasUrls' => true,
                    'uriFormat' => 'categories/{slug}',
                    'template' => null,
                ])
            ],
        ]);

        // Set up a proper field layout for the category group
        $fieldLayout = new \craft\models\FieldLayout([
            'type' => \craft\elements\Category::class,
        ]);
        
        // Create field layout tabs to properly handle the title field
        $fieldLayoutTab = new \craft\models\FieldLayoutTab([
            'name' => 'Content',
            'sortOrder' => 1,
        ]);
        $fieldLayout->setTabs([$fieldLayoutTab]);
        
        \Craft::$app->getFields()->saveLayout($fieldLayout);
        $catgroup->fieldLayoutId = $fieldLayout->id;

        $success = Craft::$app->getCategories()->saveGroup($catgroup);
        
        if (!$success) {
            $errors = [];
            foreach ($catgroup->getErrors() as $attribute => $attributeErrors) {
                foreach ($attributeErrors as $error) {
                    $errors[] = "$attribute: $error";
                }
            }
            throw new \Exception('Failed to save CategoryGroup: ' . implode(', ', $errors));
        }

        // Force database to commit and clear query cache to ensure CategoryGroup is available
        Craft::$app->getDb()->getTransaction()?->commit();
        Craft::$app->getElements()->invalidateAllCaches();
        
        // Reload the category group from database to ensure proper field loading
        $this->catgroup = Craft::$app->getCategories()->getGroupById($catgroup->id);
        
        if (!$this->catgroup) {
            throw new \Exception('Failed to reload CategoryGroup with ID: ' . $catgroup->id);
        }
        
        $this->section = $section;

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
        $element->typeId = $entryType->id;
        $element->title = 'A new beginning.';
        $element->slug = 'a-new-beginning';
        Craft::$app->getElements()->saveElement($element);
        $this->element = $element;

        $element2 = new Entry();
        $element2->siteId = 1;
        $element2->sectionId = $this->section->id;
        $element2->typeId = $entryType->id;
        $element2->title = 'Second element.';
        $element2->slug = 'second-element';
        Craft::$app->getElements()->saveElement($element2);
        $this->element2 = $element2;

        $category = new Category();
        $category->siteId = 1;
        $category->groupId = $this->catgroup->id;
        $category->title = "A new category";
        $category->slug = "a-new-category";
        
        $success = Craft::$app->getElements()->saveElement($category, false);
        
        if (!$success) {
            $errors = [];
            foreach ($category->getErrors() as $attribute => $attributeErrors) {
                foreach ($attributeErrors as $error) {
                    $errors[] = "$attribute: $error";
                }
            }
            throw new \Exception('Failed to save first Category: ' . implode(', ', $errors) . ' (GroupId: ' . $this->catgroup->id . ')');
        }
        
        $this->category = $category;

        $category2 = new Category();
        $category2->siteId = 1;
        $category2->groupId = $this->catgroup->id;
        $category2->title = "Second category";
        $category2->slug = "second-category";
        
        $success = Craft::$app->getElements()->saveElement($category2, false);
        
        if (!$success) {
            $errors = [];
            foreach ($category2->getErrors() as $attribute => $attributeErrors) {
                foreach ($attributeErrors as $error) {
                    $errors[] = "$attribute: $error";
                }
            }
            throw new \Exception('Failed to save second Category: ' . implode(', ', $errors) . ' (GroupId: ' . $this->catgroup->id . ')');
        }
        
        $this->category2 = $category2;

    }



    public function _after()
    {
        parent::_after();
        
        $section = Craft::$app->getEntries()->getSectionByHandle('news');
        if ($section) {
            Craft::$app->getEntries()->deleteSection($section);
        }

        $catgroup = Craft::$app->getCategories()->getGroupByHandle('newsCategories');
        if ($catgroup) {
            // Clean up field layout before deleting category group
            if ($catgroup->fieldLayoutId) {
                $fieldLayout = Craft::$app->getFields()->getLayoutById($catgroup->fieldLayoutId);
                if ($fieldLayout) {
                    Craft::$app->getFields()->deleteLayout($fieldLayout);
                }
            }
            Craft::$app->getCategories()->deleteGroup($catgroup);
        }

        $field = Craft::$app->getFields()->getFieldByHandle('entryField');
        if ($field) {
            Craft::$app->getFields()->deleteField($field);
        }
        
        // Clean up entry type
        $entryType = Craft::$app->getEntries()->getEntryTypeByHandle('article');
        if ($entryType) {
            Craft::$app->getEntries()->deleteEntryType($entryType);
        }
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