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
use rias\scout\console\controllers\BaseController;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;
use yii\base\InvalidConfigException;
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
     * @throws \Exception
     *
     * @return mixed
     */
    public function actionFlush($index = '')
    {
        if ($this->force || $this->confirm(Craft::t('scout', 'Are you sure you want to flush Scout?'))) {
            /* @var AlgoliaIndex $mapping */
            foreach ($this->getMappings($index) as $mapping) {
                $index = Scout::$plugin->scoutService->getClient()->initIndex($mapping->indexName);
                $index->clearObjects();
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
     * @throws InvalidConfigException
     *
     * @return int
     */
    public function actionImport($index = '')
    {
        /* @var AlgoliaIndex $mapping */
        foreach ($this->getMappings($index) as $mapping) {
            $this->stdout(Craft::t('scout', "Starting import for {$mapping->indexName}...".PHP_EOL), Console::FG_GREEN);

            // Get all elements to index
            $elementsQuery = $mapping->getElementQuery();

            $lastId = null;
            $chunkSize = 500;

            do {
                $clone = clone $elementsQuery;
                // We'll execute the query for the given page and get the results. If there are
                // no results we can just break and return from here. When there are results
                // we will call the callback with the current chunk of these results here.
                $results = $clone->where(['>=', 'id', $lastId])->limit($chunkSize)->all();
                $countResults = count($results);

                if ($countResults == 0) {
                    break;
                }

                $algoliaIndex = new AlgoliaIndex($mapping);
                $algoliaIndex->indexElements($results);

                $lastId = end($results)->id;
                $this->stdout(Craft::t('scout', "Imported up to {$lastId}...".PHP_EOL), Console::FG_GREEN);
                unset($results);
            } while ($countResults == $chunkSize);
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
     * @throws InvalidConfigException
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
