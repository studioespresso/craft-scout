<?php

namespace rias\scout\console\controllers\scout;

use Craft;
use craft\helpers\Console;
use rias\scout\console\controllers\BaseController;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use yii\console\ExitCode;

class IndexController extends BaseController
{
    public $defaultAction = 'refresh';

    /** @var bool */
    public $force = false;

    public function options($actionID): array
    {
        return ['force'];
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
        $engines->filter(function (Engine $engine) use ($index) {
            return $index === '' || $engine->scoutIndex->indexName === $index;
        })->each(function (Engine $engine) {
            $engine->flush();
            $this->stdout("Flushed index {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
        });

        return ExitCode::OK;
    }

    public function actionImport($index = '')
    {
        $engines = Scout::$plugin->getSettings()->getEngines();

        $engines->filter(function (Engine $engine) use ($index) {
            return $index === '' || $engine->scoutIndex->indexName === $index;
        })->each(function (Engine $engine) {
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
        });

        return ExitCode::OK;
    }

    public function actionRefresh($index = '')
    {
        $this->actionFlush($index);
        $this->actionImport($index);

        return ExitCode::OK;
    }
}
