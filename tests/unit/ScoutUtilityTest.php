<?php

namespace yournamespace\tests;

use Algolia\AlgoliaSearch\SearchClient;
use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use FakeEngine;
use FakeSearchClient;
use InvalidEngine;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use rias\scout\utilities\ScoutUtility;
use UnitTester;

class ScoutUtilityTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();

        $scout = new Scout('scout');
        $scout->edition = Scout::EDITION_PRO;
        $scout->setSettings([
            'engine' => FakeEngine::class,
            'indices' => [
                ScoutIndex::create('Blog')
                    ->criteria(function ($query) {
                        return $query;
                    }),
            ]
        ]);
        $scout->init();
    }

    /** @test * */
    public function it_has_a_name()
    {
        $this->assertEquals('Scout Indices', ScoutUtility::displayName());
    }

    /** @test * */
    public function it_has_an_icon()
    {
        $this->assertNotNull(ScoutUtility::iconPath());
    }

    /** @test * */
    public function it_returns_html()
    {
        $this->assertNotNull(ScoutUtility::contentHtml());
    }

    /** @test * */
    public function it_has_an_id()
    {
        $this->assertEquals('scout-indices', ScoutUtility::id());

        /** @var \rias\scout\utilities\ScoutUtility $utility */
        $utility = Craft::$app->getUtilities()->getUtilityTypeById('scout-indices');
        $this->assertEquals(ScoutUtility::class, $utility);
    }
}