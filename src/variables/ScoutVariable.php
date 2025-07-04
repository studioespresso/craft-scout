<?php

namespace rias\scout\variables;

use Craft;
use rias\scout\Scout;

class ScoutVariable
{
    public function algoliaApplicationId(?int $siteId = null): string
    {
        $siteId = $siteId ?: Craft::$app->getSites()->getCurrentSite()->id;
        return Scout::$plugin->getSettings()->getApplicationId($siteId);
    }

    public function algoliaAdminApiKey(?int $siteId = null): string
    {
        $siteId = $siteId ?: Craft::$app->getSites()->getCurrentSite()->id;
        return Scout::$plugin->getSettings()->getAdminApiKey($siteId);
    }

    public function algoliaSearchApiKey(?int $siteId = null): string
    {
        $siteId = $siteId ?: Craft::$app->getSites()->getCurrentSite()->id;
        return Scout::$plugin->getSettings()->getSearchApiKey($siteId);
    }

    public function getPluginName(): string
    {
        return Scout::$plugin->getSettings()->pluginName;
    }
}
