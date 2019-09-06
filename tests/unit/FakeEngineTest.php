<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use FakeEngine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;

class FakeEngineTest extends Unit
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
    public function it_can_index_an_element()
    {
        $scoutIndex = new ScoutIndex('Blog');
        $engine = Craft::$container->get(FakeEngine::class, [$scoutIndex]);

        $model = new Entry();
        $model->id = 1;
        $model->siteId = 1;

        $engine->update($model);

        $this->assertTrue(Craft::$app->getCache()->exists("scout-{$scoutIndex->indexName}-{$model->id}"));
    }

    /** @test * */
    public function it_can_delete_an_element_from_the_index()
    {
        $scoutIndex = new ScoutIndex('Blog');
        $engine = Craft::$container->get(FakeEngine::class, [$scoutIndex]);
        $model = new Entry();
        $model->id = 1;
        $model->siteId = 1;

        Craft::$app->getCache()->set("scout-{$scoutIndex->indexName}-{$model->id}", $model);

        $engine->delete($model);

        $this->assertEquals(false, Craft::$app->getCache()->get("scout-{$scoutIndex->indexName}-{$model->id}"));
    }

    /** @test * */
    public function it_can_flush_the_index()
    {
        $scoutIndex = new ScoutIndex('Blog');
        $engine = Craft::$container->get(FakeEngine::class, [$scoutIndex]);
        $model = new Entry();
        $model->id = 1;
        $model->siteId = 1;

        Craft::$app->getCache()->set("{$model->id}", $model);

        $engine->flush();

        $this->assertEquals(false, Craft::$app->getCache()->get("scout-{$scoutIndex->indexName}-{$model->id}"));
    }
}