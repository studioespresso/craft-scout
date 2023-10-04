<?php

namespace rias\scout\utilities;

use Craft;
use craft\base\Utility;
use rias\scout\engines\Engine;
use rias\scout\Scout;

class ScoutUtility extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('scout', 'Scout Indices');
    }

    public static function id(): string
    {
        return 'scout-indices';
    }

    public static function iconPath(): string
    {
        return Craft::getAlias('@app/icons/magnifying-glass.svg');
    }

    public static function contentHtml(): string
    {
        $view = Craft::$app->getView();

        $engines = Scout::$plugin->getSettings()->getEngines();

        $stats = $engines->map(function (Engine $engine) {
            $stats = [
                'name'         => $engine->scoutIndex->indexName,
                'replicaIndex' => $engine->scoutIndex->replicaIndex,
                'hasSettings'  => $engine->scoutIndex->indexSettings ?? null,
            ];
            if (!$engine->scoutIndex->replicaIndex) {
                $stats = array_merge($stats, [
                    'elementType'  => $engine->scoutIndex->elementType,
                    'site'         => $engine->scoutIndex->criteria->siteId === '*' ? 'all' : Craft::$app->getSites()->getSiteById($engine->scoutIndex->criteria->siteId),
                    'indexed'      => $engine->getTotalRecords(),
                    'elements'     => $engine->scoutIndex->criteria->count(),
                ]);
            }
            return $stats;
        });

        return $view->renderTemplate('scout/utility', [
            'stats' => $stats,
        ]);
    }
}
