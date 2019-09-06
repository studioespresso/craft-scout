<?php

namespace rias\scout\jobs;

use Craft;
use craft\queue\BaseJob;
use rias\scout\engines\Engine;

class MakeSearchable extends BaseJob
{
    /** @var int */
    public $id;

    /** @var int */
    public $siteId;

    /** @var string */
    public $indexName;

    /** @var \rias\scout\behaviors\SearchableBehavior */
    private $element;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->element = Craft::$app->getElements()->getElementById($this->id, null, $this->siteId);
    }

    public function execute($queue)
    {
        if (! $this->element) {
            return;
        }

        $engine = $this->element->searchableUsing()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        $engine->update($this->element);
    }

    protected function defaultDescription()
    {
        if (! $this->element) {
            return '';
        }

        return sprintf(
            'Indexing “%s” in “%s”',
            ($this->element->title ?? $this->element->id),
            $this->indexName
        );
    }
}