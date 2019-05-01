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

use AlgoliaSearch\AlgoliaException;
use Craft;
use rias\scout\console\controllers\BaseController;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\VarDumper;

/**
 * Default Command.
 *
 * @author    Rias
 *
 * @since     1.1.0
 */
class SettingsController extends BaseController
{
    public $defaultAction = 'update';

    // Public Methods
    // =========================================================================

    /**
     * Updates settings for one or all indices.
     *
     * @param string $index
     *
     * @throws Exception
     * @throws \Exception
     *
     * @return mixed
     */
    public function actionUpdate($index = '')
    {
        /* @var AlgoliaIndex $mapping */
        $mappings = $this->getMappings($index);
        $total = count($mappings);
        $progress = 0;

        Console::startProgress(
            $progress,
            $total,
            Craft::t('scout', 'Updating index settings for {index}.', ['index' => $index ?: 'all mapped indices']),
            0.5
        );

        foreach ($mappings as $mapping) {
            $index = Scout::$plugin->scoutService->getClient()->initIndex($mapping->indexName);
            $settings = $mapping->indexSettings['settings'] ?? null;
            $forwardToReplicas = $mapping->indexSettings['forwardToReplicas'] ?? null;

            if ($settings) {
                $index->setSettings($settings, $forwardToReplicas ? ['forwardToReplicas' => $forwardToReplicas] : null);
            }

            $progress++;
            Console::updateProgress($progress, $total);
            Console::endProgress();
        }

        // Everything went OK
        return ExitCode::OK;
    }

    /**
     * Dumps settings for one or all indices.
     *
     * @param string $index
     *
     * @throws Exception
     * @throws AlgoliaException
     * @throws \Exception
     */
    public function actionDump($index = '')
    {
        $dump = [];

        /* @var AlgoliaIndex $mapping */
        foreach ($this->getMappings($index) as $mapping) {
            $index = Scout::$plugin->scoutService->getClient()->initIndex($mapping->indexName);
            $dump[$mapping->indexName] = $index->getSettings();
        }

        VarDumper::dump($dump);
    }
}
