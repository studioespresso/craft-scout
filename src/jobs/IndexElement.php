<?php
/**
 * Scout plugin for Craft CMS 3.x
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\jobs;

use craft\base\Element;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use rias\scout\Scout;

use Craft;

/**
 * @author    Rias
 * @package   Scout
 * @since     0.1.0
 */
class IndexElement extends BaseJob
{
    // Properties
    // =========================================================================

    /* @var Element */
    public $element;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        /* @var Element $event->sender */
        Scout::$plugin->scoutService->indexElement($this->element);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('scout', 'Adding element to index');
    }
}
