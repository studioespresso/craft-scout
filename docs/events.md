---
title: Events
---
# Events

## ShouldBeSearchableEvent

This event allows you to customize which elements or element types get checked on save (or more specifically, every time the `SearchableBehaviour` is triggered).

```php 

use rias\scout\behaviors\SearchableBehavior;
use rias\scout\events\ShouldBeSearchableEvent;

Event::on(
    SearchableBehavior::class, 
    SearchableBehavior::EVENT_SHOULD_BE_SEARCHABLE, 
    function (ShouldBeSearchableEvent $event) {
        $event->shouldBeSearchable = false;
});
```

The event has a properties:
- $element (the element being saved)
- $shouldBeSearchable (wether or not the element should be searchable, defaults to `true`)

An example use-case for this would be to check the type of the element that's being saved and settings `shouldBeSearchable` to `false` when it's a Matrix block.

## AfterIndexImport

This event runs at the end of the `ImportIndex` job, when every item has been processed.

```php 

use rias\scout\events\AfterIndexImport;
use rias\scout\jobs\ImportIndex;

Event::on(
    ImportIndex::class,
    ImportIndex::EVENT_AFTER_INDEX_IMPORT,
    function (AfterIndexImport $event) {
        // Your code here
    });
```

The event has one property:
- $indexName (the name of the index that just finished importing

An example use-case for this would be to keep a log or dateLastImported if you're running imports on a schedule.
