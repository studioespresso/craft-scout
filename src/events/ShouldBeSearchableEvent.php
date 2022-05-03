<?php

namespace rias\scout\events;

use craft\base\Element;
use yii\base\Event;

/**
 * This event fires as apart of the chain that checks wether or not an element should be marked as searchable.
 * By settings `shouldBeSearchable` to false, you can change default behavior for certain elements or element types.
 */
class ShouldBeSearchableEvent extends Event
{
    /**
     * Element that is being saved.
     *
     * @var Element
     */
    public Element $element;

    /**
     * Wether or not the element should be marked as searchable.
     *
     * @var bool
     */
    public bool $shouldBeSearchable = true;
}
