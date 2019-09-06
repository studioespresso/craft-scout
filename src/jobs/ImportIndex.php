<?php

namespace rias\scout\jobs;

use Craft;
use craft\base\Element;
use craft\queue\BaseJob;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use Tightenco\Collect\Support\Collection;

class ImportIndex extends BaseJob
{
    /** @var string */
    public $indexName;

    public function execute($queue)
    {
        /** @var Engine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        if (! $engine) {
            return;
        }

        $elementsCount = $engine->scoutIndex->criteria->count();
        $elementsUpdated = 0;
        $batch = $engine->scoutIndex->criteria->batch(
            Scout::$plugin->getSettings()->batch_size
        );

        foreach ($batch as $elements) {
            $engine->update($elements);
            $elementsUpdated+= count($elements);
            $this->setProgress($queue, $elementsUpdated/$elementsCount);
        }
    }

    protected function defaultDescription()
    {
        return sprintf(
            'Indexing element(s) in “%s”',
            $this->indexName
        );
    }
}