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

namespace rias\scout\jobs;

use Craft;
use craft\queue\BaseJob;
use Exception;
use rias\scout\Scout;

/**
 * @author    Rias
 *
 * @since     0.1.0
 */
class IndexElement extends BaseJob
{
    // Properties
    // =========================================================================

    /* @var array */
    public $element;

    /* @var string */
    public $indexName;

    /**
     * @param craft\queue\QueueInterface $queue The queue the job belongs to
     *
     * @throws Exception
     */
    public function execute($queue)
    {
        $index = Scout::$plugin->scoutService->getClient()->initIndex($this->indexName);
        $index->saveObject($this->element);
    }

    // Protected Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    protected function defaultDescription(): string
    {
        return Craft::t('scout', sprintf('Adding element %s to index', $this->element['objectID']));
    }
}
