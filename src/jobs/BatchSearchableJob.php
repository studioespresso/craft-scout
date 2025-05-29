<?php

namespace rias\scout\jobs;

use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\queue\BaseJob;
use rias\scout\Scout;
use rias\scout\ScoutIndex;

class BatchSearchableJob extends BaseJob
{

    /** @var array */
    public $ids;

    /** @var string */
    public $indexName;

    /** @var bool */
    public $propagate = true;

    public function execute($queue): void
    {
        $collection = collect($this->ids);
        $index = $this->getIndex();
        $criteria = $index->getCriteria();

        if (is_array($index->criteria)) {
            foreach ($index->criteria as $criteria) {
                $collection->each(function ($id) use ($criteria) {
                    $element = $criteria->id($id)->one();
                    $this->handleElement($element);

                });
            }
        } else {
            $collection->each(function ($id) use ($criteria) {
                $element = $criteria->id($id)->one();
                $this->handleElement($element);
            });
        }
    }

    public function handleElement($element)
    {
        if ($element) {
            // Enabled element found
            $this->getEngine()->update($element);

            if ($this->propagate) {
                $element->searchableRelations();
            }
        } else {
            // Element not found, checking if it was disabled and needs to be de-indexed.
            // TODO Check de-indexing
//            $element = $this->getAnyElement();
//            if ($element) {
//                if (is_array($element)) {
//                    collect($element)->each(function ($element) {
//                        if (!$element->shouldBeSearchable()) {
//                            $element->unsearchable();
//                        }
//                    });
//                } else {
//                    if (!$element->shouldBeSearchable()) {
//                        $element->unsearchable();
//                    }
//                }
//            }
        }
    }

    protected function defaultDescription(): string
    {
        return sprintf(
            'Indexing %s items in “%s”',
            is_array($this->ids) ?count($this->ids) : "test",
            $this->indexName
        );
    }


    protected function getEngine()
    {
        return Scout::$plugin->getSettings()->getEngine($this->getIndex());
    }

    protected function getIndex()
    {
        return Scout::$plugin->getSettings()->getIndices()->first(function (ScoutIndex $scoutindex) {
            return $scoutindex->indexName === $this->indexName;
        });
    }
}
