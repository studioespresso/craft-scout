<?php
/**
 * Scout plugin for Craft CMS 3.x.
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries.
 * Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 *
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\MatrixBlock;
use craft\elements\Tag;
use craft\elements\User;
use craft\events\ModelEvent;
use rias\scout\models\Settings;
use rias\scout\services\ScoutService as ScoutServiceService;
use yii\base\Event;

/**
 * Class Scout.
 *
 * @author    Rias
 *
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
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'rias\scout\console\controllers\scout';
        }

        /*
         * Add or update an element to the index
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if ($this->settings->sync) {
                    $this->indexElements($event->sender);
                }
            }
        );

        /*
         * Order can be important for search indexes, so update when this changes
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (ModelEvent $event) {
                if ($this->settings->sync) {
                    $this->indexElements($event->sender);
                }
            }
        );

        /*
         * Delete an element from the index
         */
        Event::on(
            Element::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                if ($this->settings->sync) {
                    $this->deindexElements($event->sender);
                }
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
                if (!$event->isNew && $this->settings->sync) {
                    $this->indexElements($this->getElementsRelatedTo($event->sender));
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
                if ($this->settings->sync) {
                    $this->indexElements($this->getElementsRelatedTo($event->sender));
                }
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $elements
     *
     * @throws \AlgoliaSearch\AlgoliaException
     * @throws \Exception
     */
    protected function indexElements($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        self::$plugin->scoutService->indexElements($elements);
    }

    /**
     * @param $elements
     *
     * @throws \AlgoliaSearch\AlgoliaException
     * @throws \Exception
     */
    protected function deindexElements($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        self::$plugin->scoutService->deindexElements($elements);
    }

    /**
     * Get all possible elements related to another element.
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
     * {@inheritdoc}
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * {@inheritdoc}
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'scout/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
