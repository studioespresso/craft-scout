<?php

namespace rias\scout\engines;

use Algolia\AlgoliaSearch\SearchClient as Algolia;
use craft\base\Element;
use rias\scout\IndexSettings;
use rias\scout\ScoutIndex;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection;

class AlgoliaEngine extends Engine
{
    /** @var \Algolia\AlgoliaSearch\SearchClient */
    protected $algolia;

    /** @var \rias\scout\ScoutIndex */
    public $scoutIndex;

    public function __construct(ScoutIndex $scoutIndex, Algolia $algolia)
    {
        $this->scoutIndex = $scoutIndex;
        $this->algolia = $algolia;
    }

    /**
     * Update the given model in the index.
     *
     * @param array|Element $elements
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function update($elements)
    {
        $elements = new Collection(Arr::wrap($elements));

        $elements = $elements->filter(function (Element $element) {
            return get_class($element) === $this->scoutIndex->elementType;
        });

        if ($elements->isEmpty()) {
            return;
        }

        $objects = $elements->map(function (Element $element) {
            /** @var \rias\scout\behaviors\SearchableBehavior $element */
            if (empty($searchableData = $element->toSearchableArray($this->scoutIndex))) {
                return;
            }

            return array_merge(
                ['objectID' => $element->id],
                $searchableData
            );
        })->filter()->values()->all();

        if (!empty($this->scoutIndex->splitElementsOn)) {
            $result = $this->splitObjects($objects);

            $this->delete($result['delete']);

            $objects = $result['save'];
        }

        if (!empty($objects)) {
            $index = $this->algolia->initIndex($this->scoutIndex->indexName);
            $index->saveObjects($objects);
        }
    }

    public function delete($elements)
    {
        $elements = collect(Arr::wrap($elements));

        $index = $this->algolia->initIndex($this->scoutIndex->indexName);

        $index->deleteObjects($elements->map(function ($element) {
            if ($element instanceof Element) {
                return $element->id;
            }

            return $element['objectID'];
        })->values()->all());
    }

    public function flush()
    {
        $index = $this->algolia->initIndex($this->scoutIndex->indexName);
        $index->clearObjects();
    }

    public function updateSettings(IndexSettings $indexSettings)
    {
        $index = $this->algolia->initIndex($this->scoutIndex->indexName);
        $index->setSettings($indexSettings->settings);
    }

    public function getSettings(): array
    {
        $index = $this->algolia->initIndex($this->scoutIndex->indexName);

        return $index->getSettings();
    }

    public function getTotalRecords(): int
    {
        $index = $this->algolia->initIndex($this->scoutIndex->indexName);
        $response = $index->search('', [
            'attributesToRetrieve' => null,
        ]);

        return (int) $response['nbHits'];
    }
}
