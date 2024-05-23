<?php

namespace rias\scout;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use Exception;
use League\Fractal\TransformerAbstract;
use yii\base\BaseObject;

/**
 * @property-read ElementQuery $criteria
 */
class ScoutIndex extends BaseObject
{
    /** @var string */
    public $indexName;

    /** @var IndexSettings */
    public $indexSettings;

    /** @var <class-string> */
    public $elementType = Entry::class;

    /** @var callable|string|array|\League\Fractal\TransformerAbstract */
    public $transformer;

    /** @var array */
    public $splitElementsOn = [];

    /** @var bool */
    public $replicaIndex = false;

    /** @var callable|ElementQuery */
    private $_criteria;

    public function __construct(string $indexName, $config = [])
    {
        parent::__construct($config);

        $this->indexName = $indexName;
    }

    public static function create(string $indexName): self
    {
        return new self($indexName);
    }

    public function elementType(string $class): self
    {
        if (!is_subclass_of($class, Element::class)) {
            throw new Exception("Invalid Element Type {$class}");
        }

        $this->elementType = $class;

        return $this;
    }

    public function criteria(callable $criteria): self
    {
        $this->_criteria = $criteria;

        return $this;
    }

    /**
     * Leverage magic method calling to get the $criteria property, allowing
     * lazy calling the Criteria callable.
     *
     * @return \craft\elements\db\ElementQuery
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getCriteria(): ElementQuery
    {
        if (!isset($this->_criteria)) {
            return $this->_criteria = $this->elementType::find();
        }

        if (is_callable($this->_criteria)) {
            $elementQuery = call_user_func(
                $this->_criteria,
                $this->elementType::find()
            );

            if (!$elementQuery instanceof ElementQuery) {
                throw new Exception('You must return a valid ElementQuery from the criteria function.');
            }

            if (is_null($elementQuery->siteId)) {
                $elementQuery->siteId = "*";
            }

            $this->_criteria = $elementQuery;
        }

        return $this->_criteria;
    }

    /*
     * @param $transformer callable|string|array|TransformerAbstract
     */
    public function transformer($transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function splitElementsOn(array $splitElementsOn): self
    {
        $this->splitElementsOn = $splitElementsOn;

        return $this;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     *
     * @return callable|\League\Fractal\TransformerAbstract|object
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

    public function indexSettings(IndexSettings $indexSettings): self
    {
        $this->indexSettings = $indexSettings;

        return $this;
    }

    /**
     * @param bool $replicaIndex Whether to mark this index as a replica index and skip syncing.
     * @return $this
     */
    public function replicaIndex(bool $replicaIndex): self
    {
        $this->replicaIndex = $replicaIndex;

        return $this;
    }
}
