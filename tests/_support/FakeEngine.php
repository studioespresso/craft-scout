<?php

use Algolia\AlgoliaSearch\SearchClient;
use rias\scout\engines\Engine;
use rias\scout\IndexSettings;
use rias\scout\ScoutIndex;
use Tightenco\Collect\Support\Arr;

class FakeEngine extends Engine
{
    /** @var \rias\scout\ScoutIndex */
    public $scoutIndex;

    public function __construct(ScoutIndex $scoutIndex, SearchClient $algolia)
    {
        $this->scoutIndex = $scoutIndex;
    }

    public function update($models)
    {
        $previousUpdates = Craft::$app->getCache()->get("scout-{$this->scoutIndex->indexName}-updateCalled") ?? 0;
        Craft::$app->getCache()->set("scout-{$this->scoutIndex->indexName}-updateCalled", $previousUpdates + 1);

        foreach (Arr::wrap($models) as $model) {
            $previousUpdates = Craft::$app->getCache()->get("scout-{$this->scoutIndex->indexName}-{$model->id}-updateCalled") ?? 0;
            Craft::$app->getCache()->set("scout-{$this->scoutIndex->indexName}-{$model->id}-updateCalled", $previousUpdates + 1);

            Craft::$app->getCache()->set("scout-{$this->scoutIndex->indexName}-{$model->id}", $model);
        }
    }

    public function delete($models)
    {
        foreach (Arr::wrap($models) as $model) {
            $previousDeletes = Craft::$app->getCache()->get("scout-{$this->scoutIndex->indexName}-{$model->id}-deleteCalled") ?? 0;
            Craft::$app->getCache()->set("scout-{$this->scoutIndex->indexName}-{$model->id}-deleteCalled", $previousDeletes + 1);

            if (Craft::$app->getCache()->get("scout-{$this->scoutIndex->indexName}-{$model->id}")) {
                Craft::$app->getCache()->delete("scout-{$this->scoutIndex->indexName}-{$model->id}");
            }
        }
    }

    public function flush()
    {
        $previousFlushes = Craft::$app->getCache()->get("scout-{$this->scoutIndex->indexName}-flushCalled") ?? 0;
        Craft::$app->getCache()->set("scout-{$this->scoutIndex->indexName}-flushCalled", $previousFlushes + 1);
    }

    public function updateSettings(IndexSettings $indexSettings)
    {
        Craft::$app->getCache()->set("indexSettings-{$this->scoutIndex->indexName}", $indexSettings->settings);
    }

    public function getSettings(): array
    {
        return Craft::$app->getCache()->get("indexSettings-{$this->scoutIndex->indexName}") ?: [];
    }

    public function getTotalRecords(): int
    {
        return 0;
    }
}
