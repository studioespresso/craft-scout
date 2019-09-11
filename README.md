![Icon](./src/icon.svg)

[![Latest Version](https://img.shields.io/github/release/riasvdv/craft-scout.svg?style=flat-square)](https://github.com/riasvdv/craft-scout/releases)
[![Build Status](https://travis-ci.org/riasvdv/craft-scout.svg?branch=v2)](https://travis-ci.org/riasvdv/craft-scout)
[![Coverage](https://codecov.io/gh/riasvdv/craft-scout/branch/v2/graph/badge.svg)](https://codecov.io/gh/riasvdv/craft-scout/branch/v2)
[![Quality Score](https://img.shields.io/scrutinizer/g/rias500/craft-scout.svg?style=flat-square)](https://scrutinizer-ci.com/g/rias500/craft-scout)
[![StyleCI](https://styleci.io/repos/113917206/shield)](https://styleci.io/repos/113917206)
[![Total Downloads](https://img.shields.io/packagist/dt/rias/craft-scout.svg?style=flat-square)](https://packagist.org/packages/rias/craft-scout)

# Scout plugin for Craft CMS 3

Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.

## Support Open Source. Buy beer.

[![Beerpay](https://beerpay.io/Rias500/craft-scout/badge.svg?style=beer-square)](https://beerpay.io/Rias500/craft-scout)

## Requirements

This plugin requires Craft CMS 3.2 or later and PHP 7.1 or later.

## Installation

Go to the Plugin Store in your project’s Control Panel and search for “Scout”. Then click on the “Install” button in its modal window.

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
     * By default Scout handles all indexing in a queued job, you can disable
     * this so the indices are updated as soon as the elements are updated
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
     * The Algolia Application ID, this id can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This id is used to update records.
     */
    'application_id' => 'algolia',

    /*
     * The Algolia Admin API key, this key can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This key is used to update records.
     */
    'admin_api_key'  => 'algolia',

    /*
     * The Algolia search API key, this key can be found in your Algolia Account
     * https://www.algolia.com/api-keys. This search key is not used in Scout
     * but can be used through the Scout variable in your template files.
     */
    'search_api_key' => 'algolia', //optional
    
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
This function accept an `ElementQuery` and should also return an `ElementQuery`

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
For long documents it is advised to divide the element into multiple rows to keep each row within row data size. This can be done using `splitElementsOn()`.
Array items are array indexes returned from the transformer.

```php
->splitElementsOn([
    'summary',
    'matrixElement'
])
```

> *Important* - distinctId (available after indexing) must be set up as an attribute for faceting for deletion of objects to work when using splitElementsOn.

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

Brought to you by [Rias](https://rias.be)
