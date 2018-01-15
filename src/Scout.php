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
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\MatrixBlock;
use craft\elements\Tag;
use craft\elements\User;
use craft\events\ModelEvent;
use rias\scout\jobs\DeIndexElement;
use rias\scout\jobs\IndexElement;
use rias\scout\services\ScoutService as ScoutServiceService;
use rias\scout\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use yii\base\Event;

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
                Craft::$app->queue->push(new IndexElement([
                    'elements' => $event->sender,
                ]));
            }
        );

        /*
         * Order can be important for search indexes, so update when this changes
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (ModelEvent $event) {
                Craft::$app->queue->push(new IndexElement([
                    'elements' => $event->sender,
                ]));
            }
        );

        /*
         * Delete an element from the index
         */
        Event::on(
            Element::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                Craft::$app->queue->push(new DeIndexElement([
                    'elements' => $event->sender,
                ]));
            }
        );

        /*
         * When a Category is saved, reindex the related Entries
         */
        Event::on(
            Category::class,
            Category::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                // Only do this when the category isn't new
                if (! $event->isNew) {
                    Craft::$app->queue->push(new IndexElement([
                        'elements' => $this->getElementsRelatedTo($event->sender),
                    ]));
                }
            }
        );

        /*
         * When a Category is deleted, reindex the related Entries
         */
        Event::on(
            Category::class,
            Category::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                Craft::$app->queue->push(new IndexElement([
                    'elements' => $this->getElementsRelatedTo($event->sender),
                ]));
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Get all possible elements related to another element
     *
     * @param mixed $element
     *
     * @return \craft\base\ElementInterface[]
     */
    protected function getElementsRelatedTo($element)
    {
        $assets = Asset::find()->relatedTo($element)->all();
        $categories = Category::find()->relatedTo($element)->all();
        $entries = Entry::find()->relatedTo($element)->all();
        $tags = Tag::find()->relatedTo($element)->all();
        $users = User::find()->relatedTo($element)->all();
        $globalSets = GlobalSet::find()->relatedTo($element)->all();
        $matrixBlocks = MatrixBlock::find()->relatedTo($element)->all();

        return array_merge($assets, $categories, $entries, $tags, $users, $globalSets, $matrixBlocks);
    }

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
