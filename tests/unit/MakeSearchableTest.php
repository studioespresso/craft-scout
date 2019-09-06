<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\queue\Queue;
use FakeEngine;
use InvalidEngine;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\jobs\MakeSearchable;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use rias\scout\utilities\ScoutUtility;
use UnitTester;

class MakeSearchableTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();

        $scout = new Scout('scout');
        $scout->init();
    }

    /** @test * */
    public function it_doesnt_crash_when_it_cant_find_the_element_anymore()
    {
        $job = new MakeSearchable([
            'id' => 100,
            'indexName' => 'Blog',
            'siteId' => 1,
        ]);

        $job->execute(new Queue());

        $this->assertEquals('', $job->getDescription());
    }
}