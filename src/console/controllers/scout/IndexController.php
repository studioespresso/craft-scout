<?php

namespace rias\scout\console\controllers\scout;

use Craft;
use craft\helpers\Console;
use craft\helpers\Queue;
use rias\scout\console\controllers\BaseController;
use rias\scout\engines\Engine;
use rias\scout\jobs\ImportIndex;
use rias\scout\Scout;
use yii\console\ExitCode;

class IndexController extends BaseController
{
    public $defaultAction = 'refresh';

    /** @var bool */
    public $force = false;

    /** @var bool */
    public $queue = false;

    public function options($actionID): array
    {
        return ['force', 'queue'];
    }

    public function actionFlush($index = '')
    {
        if (!$this->validateIndex($index)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (
            $this->force === false
            && $this->confirm(Craft::t('scout', 'Are you sure you want to flush Scout?')) === false
        ) {
            return ExitCode::OK;
        }

        $engines = Scout::$plugin->getSettings()->getEngines();
        $engines->filter(function(Engine $engine) use ($index) {
            return !$engine->scoutIndex->replicaIndex && ($index === '' || $engine->scoutIndex->indexName === $index);
        })->each(function(Engine $engine) {
            $engine->flush();
            $this->stdout("Flushed index {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
        });

        return ExitCode::OK;
    }

    public function actionImport($index = '')
    {
        if (!$this->validateIndex($index)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $engines = Scout::$plugin->getSettings()->getEngines();

        $engines->filter(function(Engine $engine) use ($index) {
            return !$engine->scoutIndex->replicaIndex && ($index === '' || $engine->scoutIndex->indexName === $index);
        })->each(function(Engine $engine) {
            if ($this->queue) {
                Queue::push(new ImportIndex([
                    'indexName' => $engine->scoutIndex->indexName,
                ]),
                    Scout::$plugin->getSettings()->priority,
                    null,
                    Scout::$plugin->getSettings()->ttr
                );

                $this->stdout("Added ImportIndex job for '{$engine->scoutIndex->indexName}' to the queue" . PHP_EOL, Console::FG_GREEN);
            } else {
                // check if $engine->scoutIndex->criteria is iterable
                if (is_array($engine->scoutIndex->criteria)) {
                    // use array_reduce to get the count of elements
                    $elementsCount = array_reduce($engine->scoutIndex->criteria, function($carry, $query) {
                        return $carry + $query->count();
                    }, 0);

                    $elementsUpdated = 0;

                    foreach ($engine->scoutIndex->criteria as $query) {
                        $totalElements = $query->count();
                        $elementsUpdated = 0;
                        $batch = $query->batch(
                            Scout::$plugin->getSettings()->batch_size
                        );


                        foreach ($batch as $elements) {
                            $engine->update($elements);
                            $elementsUpdated += count($elements);
                            $this->stdout("Updated {$elementsUpdated}/{$totalElements} element(s) ({$query->elementType}) in {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
                        }
                    }
                } else {
                    $totalElements = $engine->scoutIndex->criteria->count();

                    $elementsUpdated = 0;
                    $batch = $engine->scoutIndex->criteria->batch(
                        Scout::$plugin->getSettings()->batch_size
                    );

                    foreach ($batch as $elements) {
                        $engine->update($elements);
                        $elementsUpdated += count($elements);
                        $this->stdout("Updated {$elementsUpdated}/{$totalElements} element(s) in {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
                    }
                }
            }
        });

        return ExitCode::OK;
    }

    public function actionRefresh($index = '')
    {
        if (!$this->validateIndex($index)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->actionFlush($index);
        $this->actionImport($index);

        return ExitCode::OK;
    }

    private function validateIndex($index): bool
    {
        // An empty index name targets every index, so there's nothing to validate.
        if ($index === '') {
            return true;
        }

        $engines = Scout::$plugin->getSettings()->getEngines();
        $filteredEngines = $engines->filter(function(Engine $engine) use ($index) {
            return $engine->scoutIndex->indexName === $index;
        });

        if ($filteredEngines->isEmpty()) {
            $this->stderr("No index found with name '{$index}'\n", Console::FG_RED);
            return false;
        }

        return true;
    }
}
