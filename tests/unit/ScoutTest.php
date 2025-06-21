<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use InvalidEngine;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;

class ScoutTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @var \craft\models\Section */
    private $section;

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

        Craft::$app->getEntries()->saveSection($section);

        $this->section = $section;
    }

    public function _after()
    {
        parent::_after();
        $section = Craft::$app->getEntries()->getSectionByHandle('news');
        if ($section) {
            Craft::$app->getEntries()->deleteSection($section);
        }
    }

    /** @test */
    public function it_attaches_the_searchable_behavior_to_element_on_init()
    {
        $element = new Entry();
        new Scout('scout');

        $this->assertNotNull($element->getBehavior('searchable'));
    }

    /** @test * */
    public function it_can_get_the_configured_engine()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'engine' => AlgoliaEngine::class,
        ]);
        $scoutIndex = new ScoutIndex('Blog');
        $engine = $scout->getSettings()->getEngine($scoutIndex);

        $this->assertTrue($engine instanceof Engine);
    }

    /** @test * */
    public function it_throws_an_exception_with_an_invalid_engine()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'engine' => InvalidEngine::class,
        ]);
        $scoutIndex = new ScoutIndex('Blog');

        $this->expectExceptionMessage('Invalid engine ' . InvalidEngine::class . ', must implement ' . Engine::class);

        $scout->getSettings()->getEngine($scoutIndex);
    }

    /** @test * */
    public function it_throws_an_exception_when_index_names_are_not_unique()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'indices' => [
                ScoutIndex::create('Blog'),
                ScoutIndex::create('Blog'),
            ],
        ]);

        $this->expectExceptionMessage('Index names must be unique in the Scout config.');

        $scout->init();
    }
    
    /** @test * */
    public function it_registers_utility()
    {
        Craft::$app->getPlugins()->installPlugin('scout');

        Scout::getInstance()->init();

        $this->assertNotNull(Craft::$app->getUtilities()->getUtilityTypeById('scout-indices'));
    }
}
