<?php

namespace yournamespace\tests;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use FakeEngine;
use InvalidEngine;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use UnitTester;

class ScoutVariableTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @test * */
    public function it_returns_the_application_id()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'application_id' => '1234',
        ]);
        $scout->init();

        $template = "{{ craft.scout.algoliaApplicationId }}";

        $output = Craft::$app->getView()->renderString($template);

        $this->assertEquals('1234', $output);
    }

    /** @test * */
    public function it_returns_the_admin_api_key()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'admin_api_key' => '1234',
        ]);
        $scout->init();

        $template = "{{ craft.scout.algoliaAdminApiKey }}";

        $output = Craft::$app->getView()->renderString($template);

        $this->assertEquals('1234', $output);
    }

    /** @test * */
    public function it_returns_the_search_api_key()
    {
        $scout = new Scout('scout');
        $scout->setSettings([
            'search_api_key' => '1234',
        ]);
        $scout->init();

        $template = "{{ craft.scout.algoliaSearchApiKey }}";

        $output = Craft::$app->getView()->renderString($template);

        $this->assertEquals('1234', $output);
    }

    /** @test * */
    public function it_can_get_the_plugin_name()
    {
        $scout = new Scout('scout');
        $scout->init();

        $template = "{{ craft.scout.pluginName }}";

        $output = Craft::$app->getView()->renderString($template);

        $this->assertEquals('Scout', $output);
    }
}