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
            'settings'          => [
                'minWordSizefor1Typo'  => 4,
                'minWordSizefor2Typos' => 10,
            ],
        ]);

        $this->assertEquals(false, $indexSettings->forwardToReplicas);
        $this->assertEquals([
            'minWordSizefor1Typo'  => 4,
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
            'minWordSizefor1Typo'  => 4,
            'minWordSizefor2Typos' => 10,
        ], $indexSettings->settings);
    }
}
