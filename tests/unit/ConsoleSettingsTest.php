<?php

namespace yournamespace\tests;

use Craft;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\test\console\ConsoleTest;
use FakeEngine;
use rias\scout\IndexSettings;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;
use yii\console\ExitCode;
use yii\helpers\VarDumper;

class ConsoleSettingsTest extends ConsoleTest
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();

        Craft::$app->getPlugins()->installPlugin('scout');

        $scout = Craft::$app->getPlugins()->getPlugin('scout');
        $scout->setSettings([
            'engine' => FakeEngine::class,
            'indices' => [
                ScoutIndex::create('blog_nl')->indexSettings(
                    IndexSettings::create()
                        ->minWordSizefor1Typo(10)
                        ->minWordSizefor2Typos(20)
                ),
                ScoutIndex::create('blog_fr')->indexSettings(
                    IndexSettings::create()
                        ->minWordSizefor1Typo(30)
                        ->minWordSizefor2Typos(40)
                ),
            ]
        ]);

        Craft::$app->getCache()->flush();
    }

    /** @test * */
    public function it_can_update_settings()
    {
        /** @var FakeEngine[] $engines */
        $engines = Scout::$plugin->getSettings()->getEngines();

        $this->assertEquals([], $engines[0]->getSettings());
        $this->assertEquals([], $engines[1]->getSettings());

        $this->consoleCommand('scout/settings/update')
            ->stdOut("Updated index settings for blog_nl\n")
            ->stdOut("Updated index settings for blog_fr\n")
            ->exitCode(ExitCode::OK)
            ->run();

        $this->assertEquals([
            'minWordSizefor1Typo' => 10,
            'minWordSizefor2Typos' => 20,
        ], Craft::$app->getCache()->get('indexSettings-blog_nl'));
        $this->assertEquals([
            'minWordSizefor1Typo' => 30,
            'minWordSizefor2Typos' => 40,
        ], Craft::$app->getCache()->get('indexSettings-blog_fr'));
    }

    /** @test * */
    public function it_can_update_settings_for_a_specific_index()
    {
        /** @var FakeEngine[] $engines */
        $engines = Scout::$plugin->getSettings()->getEngines();

        $this->assertEquals([], $engines[0]->getSettings());
        $this->assertEquals([], $engines[1]->getSettings());

        $this->consoleCommand('scout/settings/update', ['blog_nl'])
            ->stdOut("Updated index settings for blog_nl\n")
            ->exitCode(ExitCode::OK)
            ->run();

        $this->assertEquals([
            'minWordSizefor1Typo' => 10,
            'minWordSizefor2Typos' => 20,
        ], Craft::$app->getCache()->get('indexSettings-blog_nl'));
        $this->assertEquals(false, Craft::$app->getCache()->get('indexSettings-blog_fr'));
    }

    /** @test * */
    public function it_can_dump_settings()
    {
        $this->consoleCommand('scout/settings/update')
            ->stdOut("Updated index settings for blog_nl\n")
            ->stdOut("Updated index settings for blog_fr\n")
            ->exitCode(ExitCode::OK)
            ->run();

        $this->consoleCommand('scout/settings/dump')
            ->stdOut(VarDumper::dumpAsString([
                'blog_nl' => [
                    'minWordSizefor1Typo' => 10,
                    'minWordSizefor2Typos' => 20,
                ],
                'blog_fr' => [
                    'minWordSizefor1Typo' => 30,
                    'minWordSizefor2Typos' => 40,
                ]
            ]))
            ->exitCode(ExitCode::OK)
            ->run();
    }

    /** @test * */
    public function it_can_dump_settings_of_a_specific_index()
    {
        $this->consoleCommand('scout/settings/update')
            ->stdOut("Updated index settings for blog_nl\n")
            ->stdOut("Updated index settings for blog_fr\n")
            ->exitCode(ExitCode::OK)
            ->run();

        $this->consoleCommand('scout/settings/dump', ['blog_nl'])
            ->stdOut(VarDumper::dumpAsString([
                'blog_nl' => [
                    'minWordSizefor1Typo' => 10,
                    'minWordSizefor2Typos' => 20,
                ],
            ]))
            ->exitCode(ExitCode::OK)
            ->run();
    }
}