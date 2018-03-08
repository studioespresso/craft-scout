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
use craft\console\Application as ConsoleApplication;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\MatrixBlock;
use craft\elements\Tag;
use craft\elements\User;
use craft\events\ModelEvent;
use rias\scout\jobs\DeIndexElement;
use rias\scout\jobs\IndexElement;
use rias\scout\models\Settings;
use rias\scout\services\ScoutService as ScoutServiceService;
use yii\base\Event;
use yii\queue\serializers\PhpSerializer;

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
                $this->indexElement($event->sender);
            }
        );

        /*
         * Order can be important for search indexes, so update when this changes
         */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (ModelEvent $event) {
                $this->indexElement($event->sender);
            }
        );

        /*
         * Delete an element from the index
         */
        Event::on(
            Element::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                $this->deIndexElement($event->sender);
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
                if (!$event->isNew) {
                    $this->indexElement($this->getElementsRelatedTo($event->sender));
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
                $this->deIndexElement($this->getElementsRelatedTo($event->sender));
            }
        );
    }

    // Protected Methods
    // =========================================================================

    protected function deIndexElement($elements)
    {
        if ($this->elementsCanBeSerialized($elements)) {
            Craft::$app->queue->push(new DeIndexElement([
                'elements' => $elements,
            ]));
        } else {
            if (!is_array($elements)) {
                $elements = [$elements];
            }

            foreach ($elements as $element) {
                self::$plugin->scoutService->deindexElement($element);
            }
        }
    }

    protected function indexElement($elements)
    {
        if ($this->elementsCanBeSerialized($elements)) {
            Craft::$app->queue->push(new IndexElement([
                'elements' => $elements,
            ]));
        } else {
            if (!is_array($elements)) {
                $elements = [$elements];
            }

            foreach ($elements as $element) {
                self::$plugin->scoutService->indexElement($element);
            }
        }
    }

    protected function elementsCanBeSerialized($elements)
    {
        try {
            $serializer = new PhpSerializer();
            $serializer->serialize($elements);

            return true;
        } catch (\Exception $e) {
            return false;
        }
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
