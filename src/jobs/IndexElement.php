<?php

namespace rias\scout\jobs;

use Craft;
use craft\queue\BaseJob;

class IndexElement extends BaseJob
{
    /** @var int */
    public $id;

    public function execute($queue): void
    {
        $element = Craft::$app->getElements()->getElementById($this->id);

        if (!$element) {
            return;
        }

        $element->searchable();

        // Only process the related elements if Scout is directed to
        if (!Scout::$plugin->getSettings()->indexRelations) {
            return;
        }

        $element->getRelatedElements()->each(function (Element $relatedElement) {
            /* @var SearchableBehavior $relatedElement */
            $relatedElement->searchable(false);
        });
    }

    protected function defaultDescription(): string
    {
        return 'Indexing element';
    }
}
