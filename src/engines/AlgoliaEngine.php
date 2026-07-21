<?php

namespace rias\scout\engines;

use Algolia\AlgoliaSearch\Api\SearchClient as Algolia;
use craft\base\Element;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use rias\scout\IndexSettings;
use rias\scout\ScoutIndex;

class AlgoliaEngine extends Engine
{
    /** @var \Algolia\AlgoliaSearch\Api\SearchClient */
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
        if ($this->scoutIndex->replicaIndex) {
            return;
        }

        $elements = new Collection(Arr::wrap($elements));

        if ($this->scoutIndex->enforceElementType) {
            $elements = $elements->filter(function(Element $element) {
                return get_class($element) === $this->scoutIndex->elementType;
            });
        }

        if ($elements->isEmpty()) {
            return;
        }
        $objects = $this->transformElements($elements);

        if (!empty($objects)) {
            $this->algolia->saveObjects($this->scoutIndex->indexName, $objects);
        }
    }

    public function delete($elements)
    {
        if ($this->scoutIndex->replicaIndex) {
            return;
        }

        $elements = new Collection(Arr::wrap($elements));

        $objectIds = $elements->map(function($object) {
            if ($object instanceof Element) {
                return $object->id;
            }

            return $object['distinctID'] ?? $object['objectID'];
        })->unique()->values()->all();

        if (empty($objectIds)) {
            return;
        }

        if (empty($this->scoutIndex->splitElementsOn)) {
            return $this->algolia->deleteObjects($this->scoutIndex->indexName, $objectIds);
        }

        return $this->algolia->deleteBy($this->scoutIndex->indexName, [
            'filters' => 'distinctID:' . implode(' OR distinctID:', $objectIds),
        ]);
    }

    public function flush()
    {
        if ($this->scoutIndex->replicaIndex) {
            return;
        }

        $this->algolia->clearObjects($this->scoutIndex->indexName);
    }

    public function updateSettings(IndexSettings $indexSettings)
    {
        $this->algolia->setSettings(
            $this->scoutIndex->indexName,
            $indexSettings->settings,
            $indexSettings->forwardToReplicas
        );
    }

    public function getSettings(): array
    {
        return $this->algolia->getSettings($this->scoutIndex->indexName);
    }

    public function getTotalRecords(): int
    {
        $response = $this->algolia->searchSingleIndex($this->scoutIndex->indexName, [
            'query' => '',
            'attributesToRetrieve' => [],
        ]);

        return (int) $response['nbHits'];
    }

    private function transformElements(Collection $elements): array
    {
        $objects = $elements->map(function(Element $element) {
            /** @var \rias\scout\behaviors\SearchableBehavior $element */
            if (empty($searchableData = $element->toSearchableArray($this->scoutIndex))) {
                return;
            }

            return array_merge(
                ['objectID' => $element->id],
                $searchableData
            );
        })->filter()->values()->all();

        if (empty($this->scoutIndex->splitElementsOn)) {
            return $objects;
        }

        $result = $this->splitObjects($objects);

        $this->delete($result['delete']);

        $objects = $result['save'];

        return $objects;
    }
}
