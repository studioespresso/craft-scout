<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use craft\queue\Queue;
use rias\scout\jobs\MakeSearchable;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
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
        $scout->setSettings([
            'queue' => false,
            'engine' => FakeEngine::class,
            'indices' => [
                ScoutIndex::create('Blog')
                    ->criteria(function($query) {
                        return $query;
                    }),
            ],
        ]);
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
