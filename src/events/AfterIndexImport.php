<?php

namespace rias\scout\events;

use craft\base\Element;
use yii\base\Event;

/**
 * This event fires right at the end of je ImportIndexJob, after every element has been imported.
 */
class AfterIndexImport extends Event
{
    /**
     * The index that has been imported
     *
     * @var string
     */
    public string $indexName;
}
