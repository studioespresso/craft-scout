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

namespace rias\scout\controllers;

use Craft;
use craft\web\Controller;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;

class PluginController extends Controller
{
    // Public Methods
    // =========================================================================
    public function actionSettings()
    {
        $settings = Scout::$plugin->getSettings();

        // Build options list of known indexes for select fields in settings
        /* @var AlgoliaIndex $mapping */
        $indexOptions = array_map(function($mapping) {
            return [
                'label' => $mapping['indexName'],
                'value' => $mapping['indexName'],
            ];
        }, $settings->mappings);

        array_unshift($indexOptions, [
            'label' => Craft::t('scout', 'All indices'),
            'value' => '',
        ]);

        return $this->renderTemplate('scout/settings', [
            'indexOptions' => $indexOptions,
            'settings' => $settings,
        ]);
    }
}
