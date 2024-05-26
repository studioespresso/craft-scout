<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\queue\Queue;
use FakeEngine;
use rias\scout\jobs\ImportIndex;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;

class ImportIndexTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @test * */
    public function it_doesnt_crash_when_scout_doesnt_find_the_index()
    {
        $scout = new Scout('scout');
        $scout->init();

        $job = new ImportIndex([
            'indexName' => 'Blog',
        ]);

        $job->execute(new Queue());

        $this->assertEquals('Indexing element(s) in “Blog”', $job->getDescription());

        Craft::$app->getCache()->flush();
    }

    /** @test * */
    public function it_calls_update_on_the_engine()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'engine' => FakeEngine::class,
            'indices' => [
                ScoutIndex::create('Blog'),
            ],
        ]);
        $scout->init();

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

        $element = new Entry();
        $element->siteId = 1;
        $element->sectionId = $section->id;
        $element->typeId = $section->getEntryTypes()[0]->id;
        $element->title = 'A new beginning.';
        $element->slug = 'a-new-beginning';

        Craft::$app->getElements()->saveElement($element);

        $job = new ImportIndex([
            'indexName' => 'Blog',
        ]);

        $job->execute(Craft::$app->getQueue());

        $this->assertEquals(1, Craft::$app->getCache()->get('scout-Blog-updateCalled'));
    }
}
