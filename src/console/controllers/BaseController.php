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

namespace rias\scout\console\controllers;

use Craft;
use craft\base\Element;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;
use yii\console\Controller;
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
class BaseController extends Controller
{
    /**
     * @param string $index
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getMappings($index = '')
    {
        $mappings = Scout::$plugin->scoutService->getMappings();

        // If we have an argument, only get indexes that match it
        if (!empty($index)) {
            $mappings = array_filter($mappings, function ($mapping) use ($index) {
                return $mapping->indexName == $index;
            });
        }

        if (!count($mappings)) {
            throw new Exception(Craft::t('scout', 'Index {index} not found.', ['index' => $index]));
        }

        return $mappings;
    }
}
