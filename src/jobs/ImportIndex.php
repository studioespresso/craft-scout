<?php

namespace rias\scout\jobs;

use craft\base\Event;
use craft\queue\BaseJob;
use rias\scout\engines\Engine;
use rias\scout\events\AfterIndexImport;
use rias\scout\Scout;

class ImportIndex extends BaseJob
{
    /** @var string */
    public $indexName;

    public const EVENT_AFTER_INDEX_IMPORT = "afterIndexImport";

    public function execute($queue): void
    {
        /** @var Engine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        if (!$engine) {
            return;
        }

        // check if $engine->scoutIndex->criteria is iterable
        if (is_iterable($engine->scoutIndex->criteria)) {
            // use array_reduce to get the count of elements
            $elementsCount = array_reduce($engine->scoutIndex->criteria, function ($carry, $query) {
                return $carry + $query->count();
            }, 0);

            $elementsUpdated = 0;

            foreach($engine->scoutIndex->criteria as $query) {
                $batch = $query->batch(
                    Scout::$plugin->getSettings()->batch_size
                );

                foreach ($batch as $elements) {
                    $engine->update($elements);
                    $elementsUpdated += count($elements);
                    $this->setProgress($queue, $elementsUpdated / $elementsCount);
                }
            }

        } else {
            $elementsCount = $engine->scoutIndex->criteria->count();

            $elementsUpdated = 0;
            $batch = $engine->scoutIndex->criteria->batch(
                Scout::$plugin->getSettings()->batch_size
            );

            foreach ($batch as $elements) {
                $engine->update($elements);
                $elementsUpdated += count($elements);
                $this->setProgress($queue, $elementsUpdated / $elementsCount);
            }
        }

        $event = new AfterIndexImport([
            'indexName' => $this->indexName
        ]);

        Event::trigger(self::class, self::EVENT_AFTER_INDEX_IMPORT, $event);
    }

    protected function defaultDescription(): string
    {
        return sprintf(
            'Indexing element(s) in “%s”',
            $this->indexName
        );
    }
}
