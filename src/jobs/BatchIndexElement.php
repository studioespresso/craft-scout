<?php

namespace rias\scout\jobs;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\helpers\Queue;
use craft\queue\BaseJob;
use rias\scout\behaviors\SearchableBehavior;
use rias\scout\Scout;
use rias\scout\ScoutIndex;

class BatchIndexElement extends BaseJob
{
    /** @var int */
    public array|null $elements = null;

    public function execute($queue): void
    {
        $indexes = $this->getIndexes();
        $indexes->each(function (ScoutIndex $index) {
            if (is_array($index->getCriteria())) {
                foreach ($index->getCriteria() as $criteria) {
                    $matched = $this->matchedElements($criteria);
                    if($matched) {
                        $this->addBatchedJob($matched, $index);
                    }
                }
            } else {
                $matched = $this->matchedElements($index->getCriteria());
                if($matched) {
                    $this->addBatchedJob($matched, $index);
                }
            }

        });

    }

    protected function defaultDescription(): string
    {
        return 'Checking which elements to index after bulk operation';
    }

    private function addBatchedJob($ids, ScoutIndex $index)
    {
        Queue::push(new BatchSearchableJob([
            'ids' => $ids,
            'indexName' => $index->indexName,
            'propagate' => true,
        ]));
    }

    private function matchedElements(ElementQuery $criteria): array|false
    {
        $matched = array_intersect($criteria->ids(), $this->elements);
        return count($matched) ? $matched : false;
    }

    private function getIndexes()
    {
        return Scout::$plugin->getSettings()->getIndices();
    }
}
