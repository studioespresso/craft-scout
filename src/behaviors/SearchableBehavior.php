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
use craft\helpers\ElementHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use rias\scout\engines\Engine;
use rias\scout\events\ShouldBeSearchableEvent;
use rias\scout\jobs\MakeSearchable;
use rias\scout\Scout;
use rias\scout\ScoutIndex;
use rias\scout\serializer\AlgoliaSerializer;
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
    public const  EVENT_SHOULD_BE_SEARCHABLE = 'shouldBeSearchableEvent';

    public function validatesCriteria(ScoutIndex $scoutIndex): bool
    {
        if (is_array($scoutIndex->criteria)) {
            foreach ($scoutIndex->criteria as $query) {

                $criteria = clone $query;
                if ($criteria->id($this->owner->id)->exists()) {
                    return true;
                }
                continue;
            }
            return false;

        }

        $criteria = clone $scoutIndex->criteria;

        return $criteria
            ->id($this->owner->id)
            ->exists();
    }

    public function getIndices(): Collection
    {
        return Scout::$plugin
            ->getSettings()
            ->getIndices()
            ->filter(function (ScoutIndex $scoutIndex) {
                if (is_array($scoutIndex->criteria)) {
                    $criteriaSiteIds = collect($scoutIndex->criteria)->map(function ($criteria) {
                        return Arr::wrap($criteria->siteId);
                    })->flatten()->unique()->values()->toArray();


                } else {
                    $criteriaSiteIds = Arr::wrap($scoutIndex->criteria->siteId);
                }

                $siteIds = array_map(function ($siteId) {
                    return (int)$siteId;
                }, $criteriaSiteIds);

                if (is_array($scoutIndex->criteria)) {
                    return in_array(get_class($this->owner), $scoutIndex->getElementType())
                        && ($criteriaSiteIds[0] === '*' || in_array((int)$this->owner->siteId, $siteIds));
                }

                return $scoutIndex->getElementType() === get_class($this->owner)
                    && ($criteriaSiteIds[0] === '*' || in_array((int)$this->owner->siteId, $siteIds));
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
        if (!$this->shouldBeSearchable()) {
            return;
        }

        $this->searchableUsing()->each(function (Engine $engine) use ($propagate) {
            if (!$this->validatesCriteria($engine->scoutIndex)) {
                return $engine->delete($this->owner);
            }

            if (Scout::$plugin->getSettings()->getQueue()) {
                return Craft::$app->getQueue()
                    ->ttr(Scout::$plugin->getSettings()->ttr)
                    ->priority(Scout::$plugin->getSettings()->priority)
                    ->push(
                        new MakeSearchable([
                            'id' => $this->owner->id,
                            'siteId' => $this->owner->siteId,
                            'indexName' => $engine->scoutIndex->indexName,
                            'propagate' => $propagate,
                        ])
                    );
            } elseif ($propagate) {
                $this->searchableRelations();
            }
            return $engine->update($this->owner);
        });
    }

    public function unsearchable(): void
    {
        if (!Scout::$plugin->getSettings()->sync) {
            return;
        }

        $this->searchableUsing()->each->delete($this->owner);
    }

    public function toSearchableArray(ScoutIndex $scoutIndex): array
    {
        return (new Manager())
            ->setSerializer(new AlgoliaSerializer())
            ->createData(new Item($this->owner, $scoutIndex->getTransformer()))
            ->toArray();
    }

    public function searchableRelations(): void
    {
        if (!Scout::$plugin->getSettings()->indexRelations) {
            return;
        }

        $this->getRelatedElements()->each(function (Element $relatedElement) {
            /* @var SearchableBehavior $relatedElement */
            $relatedElement->searchable(false);
        });
    }

    public function getRelatedElements(): Collection
    {
        $settings = Scout::$plugin->getSettings();

        if (!$settings->sync) {
            return new Collection();
        }

        if (!empty($settings->relatedElementTypes)) {
            return Collection::make($settings->relatedElementTypes)
                ->flatMap(function ($className) {
                    return $className::find()->relatedTo($this->owner)->site('*')->all();
                });
        }

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
        }
        if (class_exists(Variant::class)) {
            $variants = Variant::find()->relatedTo($this->owner)->site('*')->all();
        }
        // @codeCoverageIgnoreEnd

        return new Collection(array_merge(
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

    public function shouldBeSearchable(): bool
    {
        if (!Scout::$plugin->getSettings()->sync) {
            return false;
        }

        if ($this->owner->propagating) {
            return false;
        }

        if (ElementHelper::isDraftOrRevision($this->owner)) {
            return false;
        }

        if (Event::hasHandlers(SearchableBehavior::class, self::EVENT_SHOULD_BE_SEARCHABLE)) {
            $event = new ShouldBeSearchableEvent([
                'element' => $this->owner,
                'shouldBeSearchable' => true,
            ]);
            Event::trigger(SearchableBehavior::class, self::EVENT_SHOULD_BE_SEARCHABLE, $event);

            return $event->shouldBeSearchable;
        }
        return true;
    }
}
