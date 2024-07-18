<?php

namespace rias\scout\jobs;

use craft\base\Element;
use craft\queue\BaseJob;
use rias\scout\Scout;
use rias\scout\ScoutIndex;

class MakeSearchable extends BaseJob
{
    /** @var int */
    public $id;

    /** @var int */
    public $siteId;

    /** @var string */
    public $indexName;

    /** @var bool */
    public $propagate = true;

    public function execute($queue): void
    {
        $element = $this->getElement();
        if ($element) {
            // Enabled element found
            $this->getEngine()->update($element);

            if ($this->propagate) {
                $element->searchableRelations();
            }
        } else {
            // Element not found, checking if it was disabled and needs to be de-indexed.
            $element = $this->getAnyElement();
            if ($element) {
                if (is_array($element)) {
                    collect($element)->each(function($element) {
                        if (!$element->shouldBeSearchable()) {
                            $element->unsearchable();
                        }
                    });
                } else {
                    if (!$element->shouldBeSearchable()) {
                        $element->unsearchable();
                    }
                }
            }
        }
    }

    protected function defaultDescription(): string
    {
        if (!$element = $this->getAnyElement()) {
            return '';
        }

        if (is_array($element)) {
            $element = end($element);
        }

        return sprintf(
            'Indexing “%s” in “%s”',
            ($element->title ?? $element->id),
            $this->indexName
        );
    }

    /**
     * We use this method instead of setting a prop in the constructor,
     * because Yii will serialize the entire class into the queue table,
     * including the gigantic element prop.
     *
     * @return Element
     */
    private function getElement()
    {
        if (is_array($this->getIndex()->criteria)) {
            $element = collect($this->getIndex()->criteria)->first(function(ElementQuery $criteria) {
                return $criteria->id($this->id)->siteId($this->siteId)->exists();
            });
            return $element->one();
        }

        return $this->getIndex()
            ->criteria
            ->id($this->id)
            ->siteId($this->siteId)
            ->one();
    }

    private function getAnyElement()
    {
        if (is_array($this->getIndex()->criteria)) {
            $element = collect($this->getIndex()->criteria)->first(function(ElementQuery $criteria) {
                return $criteria->id($this->id)->siteId($this->siteId)->status(null)->exists();
            });
            return $element->one();
        }
        
        return $this->getIndex()
            ->criteria
            ->id($this->id)
            ->status(null)
            ->siteId($this->siteId)
            ->one();
    }

    protected function getEngine()
    {
        return Scout::$plugin->getSettings()->getEngine($this->getIndex());
    }

    protected function getIndex()
    {
        return Scout::$plugin->getSettings()->getIndices()->first(function(ScoutIndex $scoutindex) {
            return $scoutindex->indexName === $this->indexName;
        });
    }
}
