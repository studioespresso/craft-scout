<?php

namespace rias\scout\jobs;

use Craft;
use craft\base\Element;
use craft\queue\BaseJob;

class DeindexElement extends BaseJob
{
    /** @var int */
    public $id;

    /** @var int|null */
    public $siteId;

    public function execute($queue)
    {
        $element = Craft::$app->getElements()->getElementById($this->id, null, $this->siteId, [
            'trashed' => null,
        ]);

        if (!$element) {
            return;
        }

        $relatedElements = $element->getRelatedElements();

        $element->unsearchable();

        $relatedElements->each(function (Element $relatedElement) {
            /* @var SearchableBehavior $relatedElement */
            if ($relatedElement->hasMethod('searchable')) {
                $relatedElement->searchable(false);
            }
        });
    }

    protected function defaultDescription()
    {
        return 'Deindexing element';
    }
}
