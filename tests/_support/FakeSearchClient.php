<?php

use Algolia\AlgoliaSearch\SearchClient;

class FakeSearchClient extends SearchClient
{
    public $indexedModels = [];

    public $settings = [];

    public function __construct()
    {
    }

    public function initIndex($indexName)
    {
        return $this;
    }

    public function saveObjects(array $objects)
    {
        foreach ($objects as $object) {
            $this->indexedModels[$object['objectID']] = $object;
        }
    }

    public function deleteObjects(array $objects)
    {
        foreach ($objects as $object) {
            unset($this->indexedModels[$object]);
        }
    }

    public function clearObjects()
    {
        $this->indexedModels = [];
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function search()
    {
        return [
            'nbHits' => 0,
        ];
    }
}
