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

namespace rias\scout\services;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use AlgoliaSearch\AlgoliaException;
use Craft;
use craft\base\Component;
use Exception;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;
use yii\base\InvalidConfigException;

/**
 * @author    Rias
 *
 * @since     0.1.0
 */
class ScoutService extends Component
{
    // Public Methods
    // =========================================================================

    public $settings;

    /* @var SearchClient */
    public $client;

    /* @var array */
    private $mappings = [];

    public function init()
    {
        $this->settings = Scout::$plugin->getSettings();
    }

    /**
     * Return the Algolia application ID defined in config/scout.php.
     *
     * @return string
     */
    public function getAlgoliaApplicationId() : string
    {
        return $this->settings->application_id;
    }

    /**
     * Return the Algolia admin API key defined in config/scout.php.
     *
     * @return string
     */
    public function getAlgoliaAdminApiKey() : string
    {
        return $this->settings->admin_api_key;
    }

    /**
     * Return the Algolia admin API key defined in config/scout.php.
     *
     * @return string
     */
    public function getAlgoliaSearchApiKey() : string
    {
        return $this->settings->search_api_key;
    }

    /**
     * @throws Exception
     *
     * @return SearchClient
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $config = new SearchConfig();
            $config->setConnectTimeout($this->settings->connect_timeout);
            $config->setApiKey($this->settings->admin_api_key);
            $config->setAppId($this->settings->application_id);

            $this->client = SearchClient::createWithConfig($config);
        }

        return $this->client;
    }

    /**
     * Returns an array of Algolia_IndexModel instances with the unprefixed index names as keys.
     *
     * @return AlgoliaIndex[]
     */
    public function getMappings()
    {
        if (!count($this->mappings)) {
            $mappingsConfig = $this->settings->mappings;
            foreach ($mappingsConfig as $mappingConfig) {
                $this->mappings[] = new AlgoliaIndex($mappingConfig);
            }
        }

        return $this->mappings;
    }

    /**
     * Passes the supplied elements to each configured index.
     *
     * @param $elements array
     *
     * @throws InvalidConfigException
     */
    public function indexElements($elements)
    {
        foreach ($this->getMappings() as $algoliaIndex) {
            $algoliaIndex->indexElements($elements);
        }
    }

    /**
     * Passes the supplied elements to each configured index.
     *
     * @param $elements array
     *
     * @throws Exception
     */
    public function deindexElements($elements)
    {
        foreach ($this->getMappings() as $algoliaIndex) {
            $algoliaIndex->deindexElements($elements);
        }
    }
}
