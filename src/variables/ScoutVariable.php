<?php

namespace rias\scout\variables;

use rias\scout\Scout;

class ScoutVariable
{
    public function algoliaApplicationId(): string
    {
        return Scout::$plugin->getSettings()->getApplicationId();
    }

    public function algoliaAdminApiKey() : string
    {
        return Scout::$plugin->getSettings()->getAdminApiKey();
    }

    public function algoliaSearchApiKey() : string
    {
        return Scout::$plugin->getSettings()->getSearchApiKey();
    }

    public function getPluginName()
    {
        return Scout::$plugin->getSettings()->pluginName;
    }
}
