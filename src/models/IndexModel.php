<?php
/**
 * Scout plugin for Craft CMS 3.x
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\models;

use craft\base\Element;
use craft\elements\Entry;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\TransformerAbstract;
use rias\scout\ElementTransformer;
use rias\scout\Scout;

use Craft;
use craft\base\Model;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * @author    Rias
 * @package   Scout
 * @since     0.1.0
 */
class IndexModel extends Model
{
    // Public Properties
    // =========================================================================
    private $index;

    /* @var string */
    public $indexName;

    /* @var string */
    public $elementType;

    /* @var mixed */
    public $filter;

    /**
     * @var callable|string|array|TransformerAbstract The transformer config, or an actual transformer object
     */
    public $transformer = ElementTransformer::class;

    /* @var mixed */
    public $settings;

    /**
     * Returns an Algolia Index instance based on the name.
     *
     * @return \AlgoliaSearch\Index
     * @throws \AlgoliaSearch\AlgoliaException
     */
    public function getIndex()
    {
        if (is_null($this->index)) {
            $this->index = Scout::$plugin->scoutService->getClient()->initIndex($this->indexName);
            if (!empty($this->settings)) {
                $this->index->setSettings($this->settings, true);
            }
        }
        return $this->index;
    }

    /**
     * Determines if the supplied element can be indexed in this index.
     *
     * @param $element Element
     *
     * @return bool
     */
    public function canIndexElement(Element $element)
    {
        return $this->elementType == get_class($element) &&
            call_user_func($this->filter, $element);
    }

    /**
     * Transforms the supplied element using the transformer method in config.
     *
     * @param $element Element
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function transformElement(Element $element)
    {
        $transformer = $this->getTransformer();
        $resource = new Item($element, $transformer);

        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer());
        $data = $fractal->createData($resource)->toArray();

        // Make sure the objectID is set for Algolia
        $data['objectID'] = $element->id;

        return $data;
    }

    /**
     * Adds or removes the supplied element from the index.
     *
     * @param $element Element
     *
     * @return mixed
     * @throws \AlgoliaSearch\AlgoliaException
     * @throws \Exception
     */
    public function indexElement(Element $element)
    {
        if ($this->canIndexElement($element)) {
            if ($element->enabled) {
                return $this->getIndex()->addObject($this->transformElement($element));
            } else {
                return $this->getIndex()->deleteObject($element->id);
            }
        }
        return false;
    }

    /**
     * Removes the supplied element from the index.
     *
     * @param $element Element
     *
     * @return mixed
     * @throws \AlgoliaSearch\AlgoliaException
     * @throws \Exception
     */
    public function deindexElement(Element $element)
    {
        if ($this->canIndexElement($element)) {
            return $this->getIndex()->deleteObject($element->id);
        }
        return true;
    }

    /**
     * Adds or removes the supplied elements from the index.
     *
     * @param $elements array
     *
     * @return mixed
     * @throws \AlgoliaSearch\AlgoliaException
     */
    public function indexElements($elements)
    {
        $toIndex = [];
        $toDelete = [];
        array_map(function ($element) use (&$toIndex, &$toDelete) {
            if ($this->canIndexElement($element)) {
                if ($element->enabled) {
                    $toIndex[] = $this->transformElement($element);
                } else {
                    $toDelete[] = $this->transformElement($element);
                }
            }
        }, $elements);
        if (count($toIndex)) {
            $this->getIndex()->addObjects($toIndex);
        }
        if (count($toDelete)) {
            $this->getIndex()->deleteObjects($toDelete);
        }
        return true;
    }

    /**
     * Returns the transformer based on the given endpoint
     *
     * @return callable|TransformerAbstract|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function getTransformer()
    {
        if (is_callable($this->transformer) || $this->transformer instanceof TransformerAbstract) {
            return $this->transformer;
        }
        return Craft::createObject($this->transformer);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'indexName' => 'string',
            'elementType' => [
                'string',
                'default' => Entry::class,
            ],
            'filter' => [
                'default' => function (Element $element) {
                    return true;
                },
            ],
        ];
    }
}
