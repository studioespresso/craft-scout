<?php

namespace rias\scout\behaviors;

use Craft;
use craft\base\Element;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\MatrixBlock;
use craft\elements\Tag;
use craft\elements\User;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use rias\scout\engines\Engine;
use rias\scout\jobs\MakeSearchable;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use Tightenco\Collect\Support\Collection;
use yii\base\Behavior;
use yii\base\Event;

/**
 * @mixin Element
 *
 * @property Element $owner
 * @property int $id
 */
class SearchableBehavior extends Behavior
{
    /** @var Collection */
    private $beforeDeleteRelated;

    public function events()
    {
        return [
            Element::EVENT_AFTER_SAVE              => 'eventUpdate',
            Element::EVENT_AFTER_RESTORE           => 'eventUpdate',
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE => 'eventUpdate',
            Element::EVENT_BEFORE_DELETE           => 'eventBeforeDelete',
            Element::EVENT_AFTER_DELETE            => 'eventAfterDelete',
        ];
    }

    public function eventUpdate(Event $event)
    {
        if (!Scout::$plugin->getSettings()->sync) {
            return;
        }

        $event->sender->searchable();
    }

    public function eventBeforeDelete()
    {
        if (!Scout::$plugin->getSettings()->sync) {
            return;
        }

        $this->beforeDeleteRelated = $this->getRelatedElements();
    }

    public function eventAfterDelete(Event $event)
    {
        if (!Scout::$plugin->getSettings()->sync) {
            return;
        }

        $event->sender->unsearchable();
    }

    public function validatesCriteria(ScoutIndex $scoutIndex): bool
    {
        return $scoutIndex->criteria
            ->id($this->owner->id)
            ->exists();
    }

    public function getIndices(): Collection
    {
        if ($this->owner->getIsDraft() || $this->owner->getIsRevision()) {
            return collect();
        }

        return Scout::$plugin
            ->getSettings()
            ->getIndices()
            ->filter(function (ScoutIndex $scoutIndex) {
                return $scoutIndex->elementType === get_class($this->owner)
                    && (int) $scoutIndex->criteria->siteId === (int) $this->owner->siteId;
            });
    }

    public function searchableUsing(): Collection
    {
        return $this->getIndices()->map(function (ScoutIndex $scoutIndex) {
            return Scout::$plugin->getSettings()->getEngine($scoutIndex);
        });
    }

    public function searchable(bool $propagate = true)
    {
        $this->searchableUsing()->each(function (Engine $engine) {
            if (!$this->validatesCriteria($engine->scoutIndex)) {
                return $engine->delete($this->owner);
            }

            if (Scout::$plugin->getSettings()->queue) {
                return Craft::$app->getQueue()->push(
                    new MakeSearchable([
                        'id'        => $this->owner->id,
                        'siteId'    => $this->owner->siteId,
                        'indexName' => $engine->scoutIndex->indexName,
                    ])
                );
            }

            return $engine->update($this->owner);
        });

        if ($propagate) {
            $this->getRelatedElements()->each(function (Element $relatedElement) {
                /* @var SearchableBehavior $relatedElement */
                $relatedElement->searchable(false);
            });
        }
    }

    public function unsearchable(bool $propagate = true)
    {
        $this->searchableUsing()->each->delete($this->owner);

        if ($propagate && $this->beforeDeleteRelated) {
            $this->beforeDeleteRelated->each(function (Element $relatedElement) {
                /* @var SearchableBehavior $relatedElement */
                $relatedElement->searchable(false);
            });
        }
    }

    public function toSearchableArray(ScoutIndex $scoutIndex): array
    {
        return (new Manager())
            ->setSerializer(new ArraySerializer())
            ->createData(new Item($this->owner, $scoutIndex->getTransformer()))
            ->toArray();
    }

    public function getRelatedElements(): Collection
    {
        $assets = Asset::find()->relatedTo($this->owner)->site('*')->all();
        $categories = Category::find()->relatedTo($this->owner)->site('*')->all();
        $entries = Entry::find()->relatedTo($this->owner)->site('*')->all();
        $tags = Tag::find()->relatedTo($this->owner)->site('*')->all();
        $users = User::find()->relatedTo($this->owner)->site('*')->all();
        $globalSets = GlobalSet::find()->relatedTo($this->owner)->site('*')->all();
        $matrixBlocks = MatrixBlock::find()->relatedTo($this->owner)->site('*')->all();

        $products = [];
        $variants = [];
        // @codeCoverageIgnoreStart
        if (class_exists(Product::class)) {
            $products = Product::find()->relatedTo($this->owner)->site('*')->all();
            $variants = Variant::find()->relatedTo($this->owner)->site('*')->all();
        }
        // @codeCoverageIgnoreEnd

        return collect(array_merge(
            $assets,
            $categories,
            $entries,
            $tags,
            $users,
            $globalSets,
            $matrixBlocks,
            $products,
            $variants
        ));
    }
}
