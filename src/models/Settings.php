<?php

namespace rias\scout\models;

use Craft;
use craft\base\Model;
use craft\helpers\App;
use craft\helpers\ConfigHelper;
use Exception;
use Illuminate\Support\Collection;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\ScoutIndex;

class Settings extends Model
{
    /** @var string */
    public string $pluginName = 'Scout';

    /** @var bool */
    public bool $sync = true;

    /** @var bool */
    public bool $indexRelations = true;

    /** @var bool */
    public bool $queue = true;

    /** @var int */
    public int $ttr = 300;

    /** @var int */
    public int $priority = 1024;

    /** @var string */
    public $engine = AlgoliaEngine::class;

    /** @var ScoutIndex[] */
    public array $indices = [];

    /* @var string|array */
    public string|array $application_id = '';

    /* @var string|array */
    public string|array $admin_api_key = '';

    /* @var string|array */
    public string|array $search_api_key = '';

    /* @var int */
    public int $connect_timeout = 1;

    /* @var int */
    public int $batch_size = 1000;

    /** @var bool */
    public bool $useOriginalRecordIfSplitValueIsArrayOfOne = true;

    /** @var string[] An array of ::class strings */
    public array $relatedElementTypes = [];

    public bool $allowPropagation = false;

    public function fields(): array
    {
        $fields = parent::fields();

        // don't include indices by default
        unset($fields['indices']);

        return $fields;
    }

    public function extraFields(): array
    {
        return [
            'indices',
            'engines',
        ];
    }

    public function rules(): array
    {
        return [
            [['connect_timeout', 'batch_size', 'ttr', 'priority'], 'integer'],
            [['sync', 'queue', 'useOriginalRecordIfSplitValueIsArrayOfOne'], 'boolean'],
            [['application_id', 'admin_api_key', 'search_api_key'], 'safe'],
            [['application_id', 'admin_api_key', 'connect_timeout'], 'required'],
        ];
    }

    public function getQueue()
    {
        if (!$this->queue) {
            Craft::$app->getDeprecator()->log(__CLASS__ . 'queue', 'Disabling the `queue` option will no longer be supported in the next version of Scout');
        }

        return $this->queue;
    }

    public function getIndices(): Collection
    {
        return new Collection($this->indices);
    }

    public function getEngines(): Collection
    {
        return $this->getIndices()->map(function(ScoutIndex $scoutIndex) {
            return $this->getEngine($scoutIndex);
        });
    }

    public function getEngine(ScoutIndex $scoutIndex, ?int $siteId = null): Engine
    {
        $engine = Craft::$container->get($this->engine, [$scoutIndex, $siteId]);

        if (!$engine instanceof Engine) {
            throw new Exception("Invalid engine {$this->engine}, must implement " . Engine::class);
        }

        return $engine;
    }

    public function getApplicationId(?int $siteId = null): string
    {
        $siteHandle = $siteId ? Craft::$app->getSites()->getSiteById($siteId)->handle : null;
        $value = ConfigHelper::localizedValue($this->application_id, $siteHandle);
        $parsedValue = App::parseEnv($value);
        
        if (empty($parsedValue)) {
            throw new Exception("Scout application_id is empty for site " . ($siteHandle ?: 'default') . " (siteId: $siteId). Check your Scout plugin settings.");
        }
        
        return $parsedValue;
    }

    public function getAdminApiKey(?int $siteId = null): string
    {
        $siteHandle = $siteId ? Craft::$app->getSites()->getSiteById($siteId)->handle : null;
        $value = ConfigHelper::localizedValue($this->admin_api_key, $siteHandle);
        $parsedValue = App::parseEnv($value);
        
        if (empty($parsedValue)) {
            throw new Exception("Scout admin_api_key is empty for site " . ($siteHandle ?: 'default') . " (siteId: $siteId). Check your Scout plugin settings.");
        }
        
        return $parsedValue;
    }

    public function getSearchApiKey(?int $siteId = null): string
    {
        $siteHandle = $siteId ? Craft::$app->getSites()->getSiteById($siteId)->handle : null;
        $value = ConfigHelper::localizedValue($this->search_api_key, $siteHandle);
        return App::parseEnv($value);
    }
}
