<?php

namespace rias\scout\jobs;

use Craft;
use craft\base\Element;
use craft\queue\BaseJob;
use rias\scout\behaviors\SearchableBehavior;
use rias\scout\Scout;

class IndexElement extends BaseJob
{
    /** @var int */
    public int $id;

    /** @var int|null */
    public int|null $siteId;

    public function execute($queue): void
    {
        $element = Craft::$app->getElements()->getElementById($this->id, null, $this->siteId);

        if (!$element) {
            return;
        }

        $element->searchable();

        // Only process the related elements if Scout is directed to
        if (!Scout::$plugin->getSettings()->indexRelations) {
            return;
        }

        $element->getRelatedElements()->each(function(Element $relatedElement) {
            /* @var SearchableBehavior $relatedElement */
            $relatedElement->searchable(false);
        });
    }

    protected function defaultDescription(): string
    {
        return 'Indexing element';
    }
}
