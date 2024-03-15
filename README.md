# Scout for Craft CMS

Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indices in sync with your entries.

## Requirements

This plugin requires Craft CMS 4.x or later and PHP 8.0.2 or later.

## Installation

Open your terminal and go to your Craft project:

```bash
# go to the project directory
cd /path/to/my-craft-project.dev

# tell Composer to install the plugin
composer require studioespresso/craft-scout

# tell Craft to install the plugin
php craft plugin/install scout
```

## Setup

To define your indices, copy the [scout.php](config/scout.php) file to your `config` folder.

```php
<?php

return [
    /*
     * Scout listens to numerous Element events to keep them updated in
     * their respective indices. You can disable these and update
     * your indices manually using the commands.
     */
    'sync' => true,

    /*
     * 
     * @depcretio
     * By default Scout handles all indexing in a queued job, you can disable
     * this so the indices are updated as soon as the elements are updated
     * 
     * Disabling the `queue` option will no longer be supported in the next version of Scout
     * 
     */
    'queue' => true,

    /*
     * The connection timeout (in seconds), increase this only if necessary
     */
    'connect_timeout' => 1,

    /*
     * The batch size Scout uses when importing a large amount of elements
     */
    'batch_size' => 1000,
    
    /*
     * By default Scout will index elements related to the element being save (that are in the same index). 
     * Disabling this can improve performance on larger sites that have lots of relations.
     */
    'indexRelations' => true,
    
    /**
     * The element types to look for when indexRelations is enabled.
     * By default, all Craft elements are checked for relations.
     * Use this to avoid unnecessary queries to Elements that aren't
     * used by your indices or to check custom Elements that may be
     * related to your indices
     */
    'relatedElementTypes' => [],

    /*
     * The Algolia Application ID, this id can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This id is used to update records.
     */
    'application_id' => '$ALGOLIA_APPLICATION_ID',

    /*
     * The Algolia Admin API key, this key can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This key is used to update records.
     */
    'admin_api_key'  => '$ALGOLIA_ADMIN_API_KEY',

    /*
     * The Algolia search API key, this key can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This search key is not used in Scout
     * but can be used through the Scout variable in your template files.
     */
    'search_api_key' => '$ALGOLIA_SEARCH_API_KEY', //optional
    
    /*
     * A collection of indices that Scout should sync to, these can be configured
     * by using the \rias\scout\ScoutIndex::create('IndexName') command. Each
     * index should define an ElementType, criteria and a transformer.
     */
    'indices'       => [],
];
```

### Example Index Configuration

```php
<?php

return [
    'indices'       => [
        \rias\scout\ScoutIndex::create('Blog')
            // Scout uses this by default, so this is optional
            ->elementType(\craft\elements\Entry::class)
            // If you don't define a siteId, the primary site is used
            ->criteria(function (\craft\elements\db\EntryQuery $query) {
                return $query->section('blog');
            })
            /*
             * The element gets passed into the transform function, you can omit this
             * and Scout will use the \rias\scout\ElementTransformer class instead
            */
            ->transformer(function (\craft\elements\Entry $entry) {
                return [
                    'title' => $entry->title,
                    'body' => $entry->body,
                ];
            })
            /*
             * You can use this to define index settings that get synced when you call
             * the ./craft scout/settings/update console command. This way you can
             * keep your index settings in source control. The IndexSettings
             * object provides autocompletion for all Algolia's settings
            */
            ->indexSettings(
                \rias\scout\IndexSettings::create()
                    ->minWordSizefor1Typo(4)
            )
    ],
];
```

#### `->elementType(string $class)`
The element type that this index contains, by default Scout uses `craft\elements\Entry::class`

Craft's default element type classes are:

- `craft\elements\Asset`
- `craft\elements\Category`
- `craft\elements\Entry`
- `craft\elements\GlobalSet`
- `craft\elements\MatrixBlock`
- `craft\elements\Tag`
- `craft\elements\User`

#### `->criteria(callable $query)`
This function accepts an `ElementQuery` and should also return an `ElementQuery`

```php
->criteria(function (ElementQuery $query) {
    return $query->section('blog');
});
```

#### `->transformer(callable|string|array|TransformerAbstract $transformer)`
The [transformer](http://fractal.thephpleague.com/transformers/) that should be used to define the data that should be sent to Algolia for each element. If you don’t set this, the default transformer will be used, which includes all of the element’s direct attribute values, but no custom field values.

```php
// Can be set to a function
->transformer(function(craft\elements\Entry $entry) {
    return [
        'title' => $entry->title,
        'id' => $entry->id,
        'url' => $entry->url,
    ];
}),

// Or a string/array that defines a Transformer class configuration
->transformer('MyTransformerClassName'),

// Or a Transformer class instance
->transformer(new MyTransformerClassName()),

```
Your custom transformer class would look something like this:
```php
<?php

use craft\elements\Entry;
use League\Fractal\TransformerAbstract;

class MyTransformerClassName extends TransformerAbstract
{
    public function transform(Entry $entry)
    {
        return [
            // ...
        ];
    }
}
```

#### `->splitElementsOn(array $keys)`
[For long documents](https://www.algolia.com/doc/guides/sending-and-managing-data/prepare-your-data/how-to/indexing-long-documents/) it is advised to divide the element into multiple rows to keep each row within row data size. This can be done using `splitElementsOn()`.
> Make sure to return an array in your transformer for these keys.

```php
->splitElementsOn([
    'summary',
    'matrixElement'
])
```

> *Important* - `distinctID` (available after indexing) must be set up as an attribute for faceting for deletion of objects to work when using splitElementsOn.

#### `->indexSettings(IndexSettings $settings)`

You can use this to define index settings that get synced when you call the `./craft scout/settings/update` console command. 
This way you can keep your index settings in source control. 
The IndexSettings object provides autocompletion for all Algolia's settings

> [!NOTE]  
> Note that settings are not synced automatically, but only when the `./craft scout/settings/update` console command is run.

```php
->indexSettings(
    \rias\scout\IndexSettings::create()
        ->minWordSizefor1Typo(4)
)
```

### Replicas

Replicas can be created with the `replicas` function on `IndexSettings`. To configure replicas, include them in the `indices` array and set their `replicaIndex` to `true` so that they are not included in any syncing operations.

Replica indices can have their configuration updated using the `./craft scout/settings/update` console command.

```php
<?php

return [
    'indices' => [
        \rias\scout\ScoutIndex::create('Products')
            // ...
            ->indexSettings(
                \rias\scout\IndexSettings::create()
                    ->minWordSizefor1Typo(4)
                    ->replicas(['virtual(Products_desc)'])
            )
    ],
    [
        \rias\scout\ScoutIndex::create('Products_desc')
            ->replicaIndex(true)
            ->indexSettings(IndexSettings::create()->customRanking(['desc(price)'])),
    ],
];
```

## Twig variables
You can access the Algolia settings set in your config file through the following Twig variables.

```twig
{{ craft.scout.pluginName }}
{{ craft.scout.algoliaApplicationId }}
{{ craft.scout.algoliaAdminApiKey }}
{{ craft.scout.algoliaSearchApiKey }}
```

## Console commands
Scout provides two easy console commands for managing your indices.

### Importing
To import one or all indices you can run the following console command

```
./craft scout/index/import <indexName?>
```

The `indexName` argument is not required, all your mappings will be imported when you omit it.

### Flushing/Clearing
Clearing an index is as easy as running a command in your console.

```
./craft scout/index/flush <indexName?>
```

As with the import command, `indexName` is not required.

When flushing, Scout will ask you to confirm that you really want to clear all the data in your index. You can bypass the confirmation by appending a `--force` flag.

### Refreshing
Does a flush/clear first and then imports the index again.

```
./craft scout/index/refresh <indexName?>
```

## Skipping an Element

You can omit an element from being indexed by returning an **empty array** from the `transform` method:

```php
ScoutIndex::create()
    ->transform(function (Entry $entry) {
        // Check if entry is valid for indexing
        $isValid = yourCustomValidation($entry);
        
        // If entry fails validation, return empty array
        if (! $isValid) {
            return [];
        }
    
        // Return normal data attributes
        return [
            'name' => $entry->title,
            ...
            'lorem' => $entry->lorem,
            'ipsum' => $entry->ipsum,
        ];
    });
```

## Events

### ShouldBeSearchableEvent

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

An example use-case for this would be to check the type of the element that's beind saved and settings `shouldBeSearchable` to `false` when it's a Matrix block.

### AfterIndexImport

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


---

## Upgrading from 1.x

The configuration has changed from how Scout v1 did its configuration. Please see the [setup](#setup) section below on how to configure Scout.

The following changes are the most notable:
- The `mappings` configuration key has been changed to `indices` and there is a new way to configure the indices
- The `criteria` is now a callable that returns an `ElementQuery`. Every Index should define a `siteId` in the criteria, otherwise the primary site is chosen.
- New `queue` and `batch` options added to the settings
- Old confuguration keys on the mappings have been replaced by functions on the `rias\scout\ScoutIndex` object.

----

Brought to you by [Studio Espresso](https://www.studioespresso.dev/) and [Rias](https://rias.be)
