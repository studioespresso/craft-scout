<?php

namespace rias\scout\tests;

use Codeception\Test\Unit;
use rias\scout\IndexSettings;
use UnitTester;

class IndexSettingsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @test * */
    public function it_can_accept_settings_in_the_create_method()
    {
        $indexSettings = IndexSettings::create([
            'forwardToReplicas' => false,
            'settings' => [
                'minWordSizefor1Typo' => 4,
                'minWordSizefor2Typos' => 10,
            ],
        ]);

        $this->assertEquals(false, $indexSettings->forwardToReplicas);
        $this->assertEquals([
            'minWordSizefor1Typo' => 4,
            'minWordSizefor2Typos' => 10,
        ], $indexSettings->settings);
    }

    /** @test * */
    public function it_fluently_sets_settings()
    {
        $indexSettings = IndexSettings::create();

        $indexSettings
            ->forwardToReplicas(false)
            ->minWordSizefor1Typo(4)
            ->minWordSizefor2Typos(10);

        $this->assertEquals(false, $indexSettings->forwardToReplicas);
        $this->assertEquals([
            'minWordSizefor1Typo' => 4,
            'minWordSizefor2Typos' => 10,
        ], $indexSettings->settings);
    }

    /** @test * */
    public function it_can_set_arbitrary_settings_like_relevancy_strictness()
    {
        $indexSettings = IndexSettings::create()
            ->relevancyStrictness(100);

        $this->assertEquals([
            'relevancyStrictness' => 100,
        ], $indexSettings->settings);
    }

    /** @test * */
    public function it_can_set_multiple_settings_at_once_with_set_settings()
    {
        $indexSettings = IndexSettings::create()
            ->setSettings([
                'relevancyStrictness' => 100,
                'customRanking' => ['desc(cat_status.title)'],
            ]);

        $this->assertEquals([
            'relevancyStrictness' => 100,
            'customRanking' => ['desc(cat_status.title)'],
        ], $indexSettings->settings);
    }

    /** @test * */
    public function set_settings_merges_with_and_overrides_existing_settings()
    {
        $indexSettings = IndexSettings::create()
            ->minWordSizefor1Typo(4)
            ->relevancyStrictness(50)
            ->setSettings([
                'relevancyStrictness' => 100,
                'customRanking' => ['desc(price)'],
            ]);

        $this->assertEquals([
            'minWordSizefor1Typo' => 4,
            'relevancyStrictness' => 100,
            'customRanking' => ['desc(price)'],
        ], $indexSettings->settings);
    }
}
