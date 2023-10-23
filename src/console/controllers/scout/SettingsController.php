<?php

namespace rias\scout\console\controllers\scout;

use craft\helpers\Console;
use rias\scout\console\controllers\BaseController;
use rias\scout\engines\Engine;
use rias\scout\Scout;
use yii\console\ExitCode;
use yii\helpers\VarDumper;

class SettingsController extends BaseController
{
    public $defaultAction = 'update';

    public function actionUpdate($index = '')
    {
        $engines = Scout::$plugin->getSettings()->getEngines();
        $engines->filter(function(Engine $engine) use ($index) {
            return $index === '' || $engine->scoutIndex->indexName === $index;
        })->each(function(Engine $engine) {
            $engine->updateSettings($engine->scoutIndex->indexSettings);
            $this->stdout("Updated index settings for {$engine->scoutIndex->indexName}\n", Console::FG_GREEN);
        });

        return ExitCode::OK;
    }

    public function actionDump($index = '')
    {
        $dump = [];

        $engines = Scout::$plugin->getSettings()->getEngines();
        $engines->filter(function(Engine $engine) use ($index) {
            return $index === '' || $engine->scoutIndex->indexName === $index;
        })->each(function(Engine $engine) use (&$dump) {
            $dump[$engine->scoutIndex->indexName] = $engine->getSettings();
        });

        $this->stdout(VarDumper::dumpAsString($dump));

        return ExitCode::OK;
    }
}
