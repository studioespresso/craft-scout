<?php

namespace rias\scout\jobs;

use Craft;
use craft\base\Element;
use craft\queue\BaseJob;
use rias\scout\engines\Engine;

class MakeSearchable extends BaseJob
{
    /** @var int */
    public $id;

    /** @var int */
    public $siteId;

    /** @var string */
    public $indexName;

    public function execute($queue)
    {
        if (!$element = $this->getElement()) {
            return;
        }

        $engine = $element->searchableUsing()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        $engine->update($element);
    }

    protected function defaultDescription()
    {
        if (!$element = $this->getElement()) {
            return '';
        }

        return sprintf(
            'Indexing “%s” in “%s”',
            ($element->title ?? $element->id),
            $this->indexName
        );
    }

    private function getElement(): ?Element
    {
        return Craft::$app->getElements()->getElementById($this->id, null, $this->siteId);
    }
}
