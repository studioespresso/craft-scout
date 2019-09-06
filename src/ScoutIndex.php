<?php

namespace rias\scout;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use Exception;
use League\Fractal\TransformerAbstract;

class ScoutIndex
{
    /** @var string */
    public $indexName;

    /** @var IndexSettings */
    public $indexSettings;

    /** @var string */
    public $elementType = Entry::class;

    /** @var ElementQuery */
    public $criteria;

    /** @var callable|string|array|\League\Fractal\TransformerAbstract */
    public $transformer;

    /** @var array */
    public $splitElementsOn = [];

    public function __construct(string $indexName)
    {
        $this->indexName = $indexName;
        $this->criteria = $this->elementType::find();
    }

    public static function create(string $indexName): ScoutIndex
    {
        return new self($indexName);
    }

    public function elementType(string $class): ScoutIndex
    {
        if (! (new $class) instanceof Element) {
            throw new Exception("Invalid Element Type {$class}");
        }

        $this->elementType = $class;

        return $this;
    }

    public function criteria(callable $criteria): ScoutIndex
    {
        $elementQuery = $criteria($this->elementType::find());

        if (! $elementQuery instanceof ElementQuery) {
            throw new Exception("You must return a valid ElementQuery from the criteria function.");
        }

        if (is_null($elementQuery->siteId)) {
            $elementQuery->siteId = Craft::$app->getSites()->getPrimarySite()->id;
        }

        $this->criteria = $elementQuery;

        return $this;
    }

    /*
     * @param $transformer callable|string|array|TransformerAbstract
     */
    public function transformer($transformer): ScoutIndex
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function splitElementsOn(array $splitElementsOn): ScoutIndex
    {
        $this->splitElementsOn = $splitElementsOn;

        return $this;
    }

    /**
     * @return callable|\League\Fractal\TransformerAbstract|object
     * @throws \yii\base\InvalidConfigException
     */
    public function getTransformer()
    {
        if (is_null($this->transformer)) {
            $this->transformer = new ElementTransformer();
        }

        if (is_callable($this->transformer) || $this->transformer instanceof TransformerAbstract) {
            return $this->transformer;
        }

        return Craft::createObject($this->transformer);
    }

    public function indexSettings(IndexSettings $indexSettings): ScoutIndex
    {
        $this->indexSettings = $indexSettings;

        return $this;
    }
}