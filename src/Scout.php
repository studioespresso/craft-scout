<?php

namespace rias\scout;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\events\DefineBehaviorsEvent;
use craft\events\ElementEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Elements;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use Exception;
use rias\scout\behaviors\SearchableBehavior;
use rias\scout\jobs\DeindexElement;
use rias\scout\jobs\IndexElement;
use rias\scout\models\Settings;
use rias\scout\utilities\ScoutUtility;
use rias\scout\variables\ScoutVariable;
use yii\base\Event;

class Scout extends Plugin
{
    const EDITION_STANDARD = 'standard';
    const EDITION_PRO = 'pro';

    public static function editions(): array
    {
        return [
            self::EDITION_STANDARD,
            self::EDITION_PRO,
        ];
    }

    /** @var \rias\scout\Scout */
    public static $plugin;

    public bool $hasCpSettings = true;

    /** @var \Illuminate\Support\Collection */
    private $beforeDeleteRelated;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Craft::$container->setSingleton(SearchClient::class, function () {
            $config = SearchConfig::create(
                self::$plugin->getSettings()->getApplicationId(),
                self::$plugin->getSettings()->getAdminApiKey()
            );

            $config->setConnectTimeout($this->getSettings()->connect_timeout);

            return SearchClient::createWithConfig($config);
        });

        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'rias\scout\console\controllers\scout';
        }

        $this->validateConfig();
        $this->registerBehaviors();
        $this->registerVariables();
        $this->registerEventHandlers();
        $this->registerUtility();
    }

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    public function getSettings(): Settings
    {
        return parent::getSettings();
    }

    /** @codeCoverageIgnore */
    protected function settingsHtml(): string
    {
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->getView()->renderTemplate('scout/settings', [
            'settings'  => $this->getSettings(),
            'overrides' => array_keys($overrides),
        ]);
    }

    private function registerUtility(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ScoutUtility::class;
            }
        );
    }

    private function registerBehaviors(): void
    {
        // Register the behavior on the Element class
        Event::on(
            Element::class,
            Element::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors['searchable'] = SearchableBehavior::class;
            }
        );
    }

    private function registerVariables(): void
    {
        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('scout', ScoutVariable::class);
            }
        );
    }

    private function validateConfig(): void
    {
        $indices = $this->getSettings()->getIndices();

        if ($indices->unique('indexName')->count() !== $indices->count()) {
            throw new Exception('Index names must be unique in the Scout config.');
        }
    }

    private function registerEventHandlers(): void
    {
        $events = [
            [Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT],
            [Elements::class, Elements::EVENT_AFTER_RESTORE_ELEMENT],
            [Elements::class, Elements::EVENT_AFTER_UPDATE_SLUG_AND_URI],
        ];

        foreach ($events as $event) {
            Event::on(
                $event[0],
                $event[1],
                function (ElementEvent $event) {
                    /** @var SearchableBehavior $element */
                    $element = $event->element;

                    if (!$element->shouldBeSearchable()) {
                        return;
                    }

                    if (Scout::$plugin->getSettings()->getQueue()) {
                        Craft::$app->getQueue()->push(
                            new IndexElement(['id' => $element->id])
                        );
                    } else {
                        $element->searchable();
                    }
                }
            );
        }

        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_DELETE_ELEMENT,
            function (ElementEvent $event) {
                if (!Scout::$plugin->getSettings()->indexRelations) {
                    $this->beforeDeleteRelated = new Collection();

                    return;
                }

                /** @var SearchableBehavior $element */
                $element = $event->element;

                if (!$element->shouldBeSearchable()) {
                    return;
                }

                // Only run this through the queue if the user has that enabled
                if (Scout::$plugin->getSettings()->getQueue()) {
                    Craft::$app->getQueue()->push(
                        new DeindexElement(['id' => $element->id])
                    );
                }
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_AFTER_DELETE_ELEMENT,
            function (ElementEvent $event) {
                //Skip this step if we already ran the DeIndex function earlier
                if (Scout::$plugin->getSettings()->getQueue()) {
                    return;
                }
                /** @var SearchableBehavior $element */
                $element = $event->element;
                $element->unsearchable();

                if ($this->beforeDeleteRelated) {
                    $this->beforeDeleteRelated->each(function (Element $relatedElement) {
                        /* @var SearchableBehavior $relatedElement */
                        $relatedElement->searchable(false);
                    });
                }
            }
        );
    }
}
