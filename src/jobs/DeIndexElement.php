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
use craft\base\Element;
use craft\queue\BaseJob;
use rias\scout\Scout;

/**
 * @author    Rias
 *
 * @since     0.1.0e
 */
class DeIndexElement extends BaseJob
{
    // Properties
    // =========================================================================

    /* @var array */
    public $elements;

    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    public function execute($queue)
    {
        if (!is_array($this->elements)) {
            $this->elements = [$this->elements];
        }

        $total = count($this->elements);
        $step = 100 / $total;
        $progress = 0;

        foreach ($this->elements as $element) {
            /* @var Element $element */
            Scout::$plugin->scoutService->deindexElement($element);

            $progress += $step;
            $queue->setProgress($progress);
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    protected function defaultDescription(): string
    {
        return Craft::t('scout', 'Removing element(s) to index');
    }
}
