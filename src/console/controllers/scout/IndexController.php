<?php

namespace rias\scout\console\controllers\scout;

use Craft;
use craft\helpers\Console;
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
        $engines = Scout::$plugin->getSettings()->getEngines();

        $engines->filter(function(Engine $engine) use ($index) {
            return !$engine->scoutIndex->replicaIndex && ($index === '' || $engine->scoutIndex->indexName === $index);
        })->each(function(Engine $engine) {
            if ($this->queue) {
                Craft::$app->getQueue()
                    ->ttr(Scout::$plugin->getSettings()->ttr)
                    ->priority(Scout::$plugin->getSettings()->priority)
                    ->push(new ImportIndex([
                        'indexName' => $engine->scoutIndex->indexName,
                    ]));
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
                        $batch = $query->batch(
                            Scout::$plugin->getSettings()->batch_size
                        );

                        foreach ($batch as $elements) {
                            $engine->update($elements);
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
        $this->actionFlush($index);
        $this->actionImport($index);

        return ExitCode::OK;
    }

    public function actionDebug($index, $id)
    {
        $engine = Scout::$plugin->getSettings()->getEngines()->first();

        $indexes = Scout::$plugin->getSettings()->getIndices()->where('indexName', $index)->where('replicaIndex', false);
        if(!$indexes->all()) {
            $this->stdout("Index not found\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $index = $indexes->first();
        if(!in_array($id, $index->criteria->ids())) {
            $this->stdout("Element not found in index\n", Console::FG_PURPLE);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $element = $index->criteria->id($id)->one();
        $data = $engine->transformElements(collect([$element]));



        dd(json_encode($data));



//        $engines->filter(function (Engine $engine) use ($index) {
//            return !$engine->scoutIndex->replicaIndex && ($index === '' || $engine->scoutIndex->indexName === $index);
//        })->each(function (Engine $engine) use ($id) {
//            $element = $engine->scoutIndex->criteria->where(['id' => $id])->one();
//            $engine->update([$element]);
//            $this->stdout("Updated element {$id} in {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
//        });

        return ExitCode::OK;
    }
}
