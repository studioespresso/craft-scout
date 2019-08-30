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
use yii\web\Response;

class IndexController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Flush one or all Algolia indexes.
     *
     * @return Response
     */
    public function actionFlush()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $index = $request->getBodyParam('flushIndex');

        try {
            $mappings = Scout::$plugin->scoutService->getMappings($index);
            $indexCount = count($mappings);

            /* @var AlgoliaIndex $mapping */
            foreach ($mappings as $mapping) {
                $index = Scout::$plugin->scoutService->getClient()->initIndex($mapping->indexName);
                $index->clearObjects();
            }

            Craft::$app->getSession()->setNotice(
                Craft::t('scout', 'Flushed {indexCount} {indexNoun}.', [
                    'indexCount' => $indexCount,
                    'indexNoun'  => $indexCount === 1 ? 'index' : 'indices',
                ])
            );
        } catch (\Throwable $e) {
            Craft::$app->getSession()->setError($e->getMessage());
        }

        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }

    /**
     * @return Response
     */
    public function actionImport(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $index = $request->getBodyParam('importIndex');

        try {
            $mappings = Scout::$plugin->scoutService->getMappings($index);
            $indexCount = count($mappings);

            /* @var AlgoliaIndex $mapping */
            foreach ($mappings as $mapping) {
                // Get all elements to index
                $elements = $mapping->getElementQuery()->all();

                $algoliaIndex = new AlgoliaIndex($mapping);
                $algoliaIndex->indexElements($elements);
            }

            // Run the queue after adding all elements
            Craft::$app->queue->run();

            Craft::$app->getSession()->setNotice(
                Craft::t('scout', 'Imported {indexCount} {indexNoun}.', [
                    'indexCount' => $indexCount,
                    'indexNoun'  => $indexCount === 1 ? 'index' : 'indices',
                ])
            );
        } catch (\Throwable $e) {
            Craft::$app->getSession()->setError($e->getMessage());
        }

        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }
}
