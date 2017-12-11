<?php
/**
 * Scout plugin for Craft CMS 3.x
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries.
 * Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout;

use craft\base\Element;
use craft\events\ModelEvent;
use craft\records\Entry;
use rias\scout\services\ScoutService as ScoutServiceService;
use rias\scout\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use yii\base\Event;
use yii\db\AfterSaveEvent;

/**
 * Class Scout
 *
 * @author    Rias
 * @package   Scout
 * @since     0.1.0
 *
 * @property  ScoutServiceService $scoutService
 */
class Scout extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Scout
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'rias\scout\console\controllers';
        }

        /*
         * Add or update an element to the index
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                /* @var Element $event->sender */
                $this->scoutService->indexElement($event->sender);
            }
        );

        /*
         * Order can be important for search indexes, so update when this changes
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (ModelEvent $event) {
                /* @var Element $event->sender */
                $this->scoutService->indexElement($event->sender);
            }
        );

        /*
         * Delete an element from the index
         */
        Event::on(
            Element::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                /* @var Element $event->sender */
                $this->scoutService->deindexElement($event->sender);
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'scout/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
