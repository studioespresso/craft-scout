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

namespace rias\scout\variables;

use rias\scout\Scout;

/**
 * @author    Rias
 *
 * @since     1.1.4
 */
class ScoutVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the configured Algolia Application ID if set.
     *
     * @return string
     */
    public function algoliaApplicationId() : string
    {
        return Scout::$plugin->scoutService->getAlgoliaApplicationId();
    }

    /**
     * Returns the configured Algolia Admin API key if set.
     *
     * @return string
     */
    public function algoliaAdminApiKey() : string
    {
        return Scout::$plugin->scoutService->getAlgoliaAdminApiKey();
    }

    /**
     * Returns the configured Algolia search API key if set.
     *
     * @return string
     */
    public function algoliaSearchApiKey() : string
    {
        return Scout::$plugin->scoutService->getAlgoliaSearchApiKey();
    }
}
