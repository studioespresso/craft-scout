<?php
/**
 * Scout plugin for Craft CMS 3.x.
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 *
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\console\controllers\scout;

use Craft;
use craft\base\Element;
use rias\scout\console\controllers\BaseController;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Default Command.
 *
 * @author    Rias
 *
 * @since     0.1.0
 */
class IndexController extends BaseController
{
    public $defaultAction = 'refresh';

    public $force = false;

    public function options($actionID)
    {
        return ['force'];
    }

    // Public Methods
    // =========================================================================

    /**
     * Flush one or all Algolia indexes.
     *
     * @param string $index
     *
     * @throws Exception
     * @throws \AlgoliaSearch\AlgoliaException
     * @throws \Exception
     *
     * @return mixed
     */
    public function actionFlush($index = '')
    {
        if ($this->force || $this->confirm(Craft::t('scout', 'Are you sure you want to flush Scout?'))) {
            /* @var \rias\scout\models\AlgoliaIndex $mapping */
            foreach ($this->getMappings($index) as $mapping) {
                $index = Scout::$plugin->scoutService->getClient()->initIndex($mapping->indexName);
                $index->clearIndex();
            }

            return ExitCode::OK;
        }

        return ExitCode::OK;
    }

    /**
     * Import your entries into one or all Algolia indexes.
     *
     * @param string $index
     *
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     *
     * @return int
     */
    public function actionImport($index = '')
    {
        /* @var \rias\scout\models\AlgoliaIndex $mapping */
        foreach ($this->getMappings($index) as $mapping) {
            // Get all elements to index
            $elements = $mapping->getElementQuery()->all();

            // Create a job to index each element
            $progress = 0;
            $total = count($elements);
            Console::startProgress(
                $progress,
                $total,
                Craft::t('scout', 'Adding elements from index {index}.', ['index' => $mapping->indexName]),
                0.5
            );

            $algoliaIndex = new AlgoliaIndex($mapping);
            $algoliaIndex->indexElements($elements);

            Console::updateProgress($total, $total);
            Console::endProgress();
        }

        // Run the queue after adding all elements
        $this->stdout(Craft::t('scout', 'Running queue jobs...'.PHP_EOL), Console::FG_GREEN);
        Craft::$app->queue->run();

        // Everything went OK
        return ExitCode::OK;
    }

    /**
     * Refresh one or all indexes.
     *
     * @param string $index
     *
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     *
     * @return int
     */
    public function actionRefresh($index = '')
    {
        $this->actionFlush($index);
        $this->actionImport($index);

        return ExitCode::OK;
    }
}
