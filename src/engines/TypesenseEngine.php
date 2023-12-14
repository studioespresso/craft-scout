<?php

namespace rias\scout\engines;

use Typesense\Client as Typesense;
use craft\base\Element;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use rias\scout\IndexSettings;
use rias\scout\ScoutIndex;

class TypesenseEngine extends Engine
{
    /** @var \Typesense\Client */
    protected $typesense;

    /** @var \rias\scout\ScoutIndex */
    public $scoutIndex;

    public function __construct(ScoutIndex $scoutIndex, Typesense $typesense)
    {
        $this->scoutIndex = $scoutIndex;
        $this->typesense = $typesense;
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

        $objects = $this->transformElements($elements);

        if (!empty($objects)) {
            $collection = $this->typesense->collections[$this->scoutIndex->indexName];

            return $collection->documents->import($objects, ['action' => 'upsert']);
        }
    }

    public function delete($elements)
    {
        $elements = new Collection(Arr::wrap($elements));

        $collection = $this->typesense->collections[$this->scoutIndex->indexName];

        $objectIds = $elements->map(function ($object) {
            if ($object instanceof Element) {
                return $object->id;
            }

            return $object['distinctID'] ?? $object['objectID'];
        })->unique()->values()->all();

        if (empty($objectIds)) {
            return;
        }

        // if (empty($this->scoutIndex->splitElementsOn)) {
        //     return $index->deleteObjects($objectIds);
        // }

        return $index->delete([
            'filter_by' => 'objectID:['.implode(", ", $objectIds).']',
        ]);
    }

    public function flush()
    {
        $collection = $this->typesense->collections[$this->scoutIndex->indexName];

        $collection->documents->delete(['filter_by' => 'objectID:>=0']);
    }

    public function updateSettings(IndexSettings $indexSettings)
    {
        // $this->typesense->collections[$this->scoutIndex->indexName]->delete();
        // $this->typesense->collections->create([
        //     'name' => $this->scoutIndex->indexName,
        //     'fields' => [
        //         [ 'name' => 'objectID', 'type' => 'int32' ],
        //         [ 'name' => '.*', 'type' => 'auto' ]
        //     ]
        // ]);

        $collection = $this->typesense->collections[$this->scoutIndex->indexName];

        return $collection->update($indexSettings);
    }

    public function getSettings(): array
    {
        $collection = $this->typesense->collections[$this->scoutIndex->indexName];
        
        return $collection->retrieve();
    }

    public function getTotalRecords(): int
    {
        $collection = $this->typesense->collections[$this->scoutIndex->indexName];

        $response = $collection->retrieve();

        return (int) $response['num_documents'];
    }

    private function transformElements(Collection $elements): array
    {
        $objects = $elements->map(function (Element $element) {
            /** @var \rias\scout\behaviors\SearchableBehavior $element */
            if (empty($searchableData = $element->toSearchableArray($this->scoutIndex))) {
                return;
            }

            return array_merge(
                ['objectID' => $element->id],
                $searchableData,
                ['id' => strval($element->id)]
            );
        })->filter()->values()->all();

        return $objects;

        // if (empty($this->scoutIndex->splitElementsOn)) {
        //     return $objects;
        // }

        // $result = $this->splitObjects($objects);

        // $this->delete($result['delete']);

        // $objects = $result['save'];

        // return $objects;
    }
}
