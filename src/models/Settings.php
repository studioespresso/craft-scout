<?php

namespace rias\scout\models;

use Craft;
use craft\base\Model;
use Exception;
use Illuminate\Support\Collection;
use rias\scout\engines\AlgoliaEngine;
use rias\scout\engines\Engine;
use rias\scout\ScoutIndex;

class Settings extends Model
{
    /** @var string */
    public $pluginName = 'Scout';

    /** @var bool */
    public $sync = true;

    /** @var bool */
    public $indexRelations = true;

    /**
     * @var bool
     *
     * @deprecated 4.0.0 Disabling the `queue` option will no longer be supported in the next version of Scout
     */
    public $queue = true;

    /** @var int */
    public $ttr = 300;

    /** @var int */
    public $priority = 1024;

    /** @var string */
    public $engine = AlgoliaEngine::class;

    /** @var ScoutIndex[] */
    public $indices = [];

    /* @var string */
    public $application_id = '';

    /* @var string */
    public $admin_api_key = '';

    /* @var string */
    public $search_api_key = '';

    /* @var int */
    public $connect_timeout = 1;

    /* @var int */
    public $batch_size = 1000;

    /** @var bool */
    public $useOriginalRecordIfSplitValueIsArrayOfOne = true;

    /** @var string[] An array of ::class strings */
    public $relatedElementTypes = [];

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
            [['application_id', 'admin_api_key', 'search_api_key'], 'string'],
            [['application_id', 'admin_api_key', 'connect_timeout'], 'required'],
        ];
    }

    public function getQueue()
    {
        if (!$this->queue) {
            Craft::$app->getDeprecator()->log(__CLASS__.'queue', 'Disabling the `queue` option will no longer be supported in the next version of Scout');
        }

        return $this->queue;
    }

    public function getIndices(): Collection
    {
        return new Collection($this->indices);
    }

    public function getEngines(): Collection
    {
        return $this->getIndices()->map(function (ScoutIndex $scoutIndex) {
            return $this->getEngine($scoutIndex);
        });
    }

    public function getEngine(ScoutIndex $scoutIndex): Engine
    {
        $engine = Craft::$container->get($this->engine, [$scoutIndex]);

        if (!$engine instanceof Engine) {
            throw new Exception("Invalid engine {$this->engine}, must implement ".Engine::class);
        }

        return $engine;
    }

    public function getApplicationId(): string
    {
        return Craft::parseEnv($this->application_id);
    }

    public function getAdminApiKey(): string
    {
        return Craft::parseEnv($this->admin_api_key);
    }

    public function getSearchApiKey(): string
    {
        return Craft::parseEnv($this->search_api_key);
    }
}
