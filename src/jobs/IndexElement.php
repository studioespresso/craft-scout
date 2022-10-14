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

        if (! $element) {
          return;
        }

        $element->searchable();
    }

    protected function defaultDescription(): string
    {
        return 'Indexing element';
    }
}
