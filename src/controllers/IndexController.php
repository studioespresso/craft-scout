<?php

namespace rias\scout\controllers;

use Craft;
use craft\helpers\Json;
use craft\helpers\Queue;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use rias\scout\engines\Engine;
use rias\scout\jobs\ImportIndex;
use rias\scout\Scout;
use rias\scout\utilities\ScoutUtility;

class IndexController extends Controller
{
    public function actionFlush()
    {
        $this->requirePostRequest();

        $engine = $this->getEngine();
        $engine->flush();

        Craft::$app->getSession()->setNotice("Flushed index {$engine->scoutIndex->indexName}");

        return $this->redirect(UrlHelper::url('utilities/' . ScoutUtility::id()));
    }

    public function actionImport()
    {
        $this->requirePostRequest();

        $engine = $this->getEngine();

        if (Scout::$plugin->getSettings()->queue) {
            Queue::push(new ImportIndex([
                'indexName' => $engine->scoutIndex->indexName,

            ]),
                Scout::$plugin->getSettings()->priority,
                null,
                Scout::$plugin->getSettings()->ttr
            );
            Craft::$app->getSession()->setNotice("Queued job to update element(s) in {$engine->scoutIndex->indexName}");

            return $this->redirect(UrlHelper::url('utilities/' . ScoutUtility::id()));
        }

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
            $elementsCount = $engine->scoutIndex->criteria->count();

            $elementsUpdated = 0;
            $batch = $engine->scoutIndex->criteria->batch(
                Scout::$plugin->getSettings()->batch_size
            );

            foreach ($batch as $elements) {
                $engine->update($elements);
            }
        }

        Craft::$app->getSession()->setNotice("Updated {$elementsCount} element(s) in {$engine->scoutIndex->indexName}");

        return $this->redirect(UrlHelper::url('utilities/' . ScoutUtility::id()));
    }

    public function actionRefresh()
    {
        $this->requirePostRequest();

        $this->actionFlush();
        $this->actionImport();

        return $this->redirect(UrlHelper::url('utilities/' . ScoutUtility::id()));
    }

    public function actionUpdateSettings()
    {
        $this->requirePostRequest();

        $engine = $this->getEngine();
        $engine->updateSettings($engine->scoutIndex->indexSettings);

        Craft::$app->getSession()->setNotice("Updated settings for index {$engine->scoutIndex->indexName}");

        return $this->redirect(UrlHelper::url('utilities/' . ScoutUtility::id()));
    }

    public function actionDumpSettings()
    {
        $engine = $this->getEngine();
        $settings = $engine->getSettings();
        $content = Json::encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $attachmentName = "scout-{$engine->scoutIndex->indexName}-settings.json";

        return $this->response->sendContentAsFile($content, $attachmentName, [
            'mimeType' => 'application/json',
        ]);
    }

    private function getEngine(): Engine
    {
        $index = Craft::$app->getRequest()->getRequiredBodyParam('index');
        $engines = Scout::$plugin->getSettings()->getEngines();

        /* @var \rias\scout\engines\Engine $engine */
        return $engines->first(function(Engine $engine) use ($index) {
            return $engine->scoutIndex->indexName === $index;
        });
    }
}
