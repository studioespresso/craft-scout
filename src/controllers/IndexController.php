<?php

namespace rias\scout\controllers;

use Craft;
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

        return $this->redirect(UrlHelper::url('utilities/'.ScoutUtility::id()));
    }

    public function actionImport()
    {
        $this->requirePostRequest();

        $engine = $this->getEngine();

        if (Scout::$plugin->getSettings()->queue) {
            Craft::$app->getQueue()->push(new ImportIndex([
                'indexName' => $engine->scoutIndex->indexName,
            ]));

            Craft::$app->getSession()->setNotice("Queued job to update element(s) in {$engine->scoutIndex->indexName}");

            return $this->redirect(UrlHelper::url('utilities/'.ScoutUtility::id()));
        }

        $elementsCount = $engine->scoutIndex->criteria->count();
        $batch = $engine->scoutIndex->criteria->batch(
            Scout::$plugin->getSettings()->batch_size
        );

        foreach ($batch as $elements) {
            $engine->update($elements);
        }

        Craft::$app->getSession()->setNotice("Updated {$elementsCount} element(s) in {$engine->scoutIndex->indexName}");

        return $this->redirect(UrlHelper::url('utilities/'.ScoutUtility::id()));
    }

    public function actionRefresh()
    {
        $this->requirePostRequest();

        $this->actionFlush();
        $this->actionImport();

        return $this->redirect(UrlHelper::url('utilities/'.ScoutUtility::id()));
    }

    private function getEngine(): Engine
    {
        $index = Craft::$app->getRequest()->getRequiredBodyParam('index');
        $engines = Scout::$plugin->getSettings()->getEngines();

        /* @var \rias\scout\engines\Engine $engine */
        return $engines->first(function (Engine $engine) use ($index) {
            return $engine->scoutIndex->indexName === $index;
        });
    }
}
