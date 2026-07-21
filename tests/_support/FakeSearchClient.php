<?php

use Algolia\AlgoliaSearch\Api\SearchClient;

class FakeSearchClient extends SearchClient
{
    public $indexedModels = [];

    public $settings = [];

    public function saveObjects($indexName, $objects, $waitForTasks = false, $batchSize = 1000, $requestOptions = [], $chunkedOptions = null)
    {
        foreach ($objects as $object) {
            $this->indexedModels[$object['objectID']] = $object;
        }
    }

    public function deleteObjects($indexName, $objectIDs, $waitForTasks = false, $batchSize = 1000, $requestOptions = [], $chunkedOptions = null)
    {
        foreach ($objectIDs as $objectID) {
            unset($this->indexedModels[$objectID]);
        }
    }

    public function deleteBy($indexName, $deleteByParams, $requestOptions = [])
    {
        $filters = $deleteByParams['filters'];

        foreach (explode(' OR ', $filters) as $orfilter) {
            $filter = explode(':', $orfilter);
            foreach ($this->indexedModels as $index => $indexedModel) {
                if (isset($indexedModel[$filter[0]]) && $indexedModel[$filter[0]] == $filter[1]) {
                    unset($this->indexedModels[$index]);
                }
            }
        }
    }

    public function clearObjects($indexName, $requestOptions = [])
    {
        $this->indexedModels = [];
    }

    public function setSettings($indexName, $indexSettings, $forwardToReplicas = null, $requestOptions = [])
    {
        $this->settings = $indexSettings;
    }

    public function getSettings($indexName, $getVersion = null, $requestOptions = [])
    {
        return $this->settings;
    }

    public function searchSingleIndex($indexName, $searchParams = null, $requestOptions = [])
    {
        return [
            'nbHits' => 0,
        ];
    }
}
