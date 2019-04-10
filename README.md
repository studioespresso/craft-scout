![Icon](./src/icon.svg)

[![Latest Version](https://img.shields.io/github/release/rias500/craft-scout.svg?style=flat-square)](https://github.com/rias500/craft-scout/releases)
[![Quality Score](https://img.shields.io/scrutinizer/g/rias500/craft-scout.svg?style=flat-square)](https://scrutinizer-ci.com/g/rias500/craft-scout)
[![StyleCI](https://styleci.io/repos/113917206/shield)](https://styleci.io/repos/113917206)
[![Total Downloads](https://img.shields.io/packagist/dt/rias/craft-scout.svg?style=flat-square)](https://packagist.org/packages/rias/craft-scout)

# Scout plugin for Craft CMS 3

Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.

## Support Open Source. Buy beer.

This plugin is licensed under a MIT license, which means that it's completely free open source software, and you can use it for whatever and however you wish. If you're using it and want to support the development, buy me a beer over at Beerpay!

[![Beerpay](https://beerpay.io/Rias500/craft-scout/badge.svg?style=beer-square)](https://beerpay.io/Rias500/craft-scout)

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require rias/craft-scout

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Scout.

## Setup

To define your indices, create a new `scout.php` file within your `config` folder. This file should return an array with 3 keys, an `application_id`, your `admin_api_key` (which are both found in your [Algolia](https://www.algolia.com/api-keys) account) and a `mappings` key, which defines your site's mappings.

Within the mappings array, each index is represented by a configuration array.

```php
<?php

return [
    'sync' => true,
    'connect_timeout' => 1,
    'application_id' => 'algolia',
    'admin_api_key' => 'algolia',
    'search_api_key' => 'algolia', // optional
    'mappings' => [
        [
            'indexName' => 'blog',
            'indexSettings' => [
                'settings' => [
                    'attributesForFaceting' => ['blogCategory'],
                ],
                'forwardToReplicas' => 'true',
            ],
            'elementType' => \craft\elements\Entry::class,
            'criteria' => [
                'section' => 'blog'
            ],
            'transformer' => function (craft\base\Element $element) {
                return $element->toArray();
            },
        ],
        ...
    ],
];
```

### The Sync option
This config variable determines if Scout should keep your entries in sync automatically. Setting this to `false` disables all of Scout's event listeners.

### Connect timeout
This config variable determines the connect timeout in seconds to Algolia servers. You should only change this if you have a slow server. Standard is 1 second.

### Mapping configuration settings

#### `indexName`
The index name in Algolia, if you don't already have an index created, Scout will create one for you.

#### `indexSettings`
Allows you to have your index settings in config. They need to be manually updated when changed with `./craft scout/settings.update`.

Below are the default settings Algolia provides
```php
'indexSettings' => [
    'forwardToReplicas' => 'true',
    'settings' => [
        'minWordSizefor1Typo' => 4,
        'minWordSizefor2Typos' => 8,
        'hitsPerPage' => 20,
        'maxValuesPerFacet' => 100,
        'version' => 2,
        'attributesToIndex' => null,
        'numericAttributesToIndex' => null,
        'attributesToRetrieve' => null,
        'unretrievableAttributes' => null,
        'optionalWords' => null,
        'attributesForFaceting' => [],
        'attributesToSnippet' => null,
        'attributesToHighlight' => null,
        'paginationLimitedTo' => 1000,
        'attributeForDistinct' => null,
        'exactOnSingleWordQuery' => 'attribute',
        'ranking' => [
            0 => 'typo',
            1 => 'geo',
            2 => 'words',
            3 => 'filters',
            4 => 'proximity',
            5 => 'attribute',
            6 => 'exact',
            7 => 'custom',
        ],
        'customRanking' => null,
        'separatorsToIndex' => '',
        'removeWordsIfNoResults' => 'none',
        'queryType' => 'prefixLast',
        'highlightPreTag' => '<em>',
        'highlightPostTag' => '</em>',
        'snippetEllipsisText' => '',
        'alternativesAsExact' => [
            0 => 'ignorePlurals',
            1 => 'singleWordSynonym',
        ],
    ],
],
```

#### `elementType`
The element type that this index contains, most of the time this will be `craft\elements\Entry::class`

Craft's default element type classes are:

- `craft\elements\Asset`
- `craft\elements\Category`
- `craft\elements\Entry`
- `craft\elements\GlobalSet`
- `craft\elements\MatrixBlock`
- `craft\elements\Tag`
- `craft\elements\User`

```php
'elementType' => craft\elements\Entry::class,
```

#### `criteria`
An array of parameters that should be set on the [Element Query](https://github.com/craftcms/docs/blob/v3/en/element-queries.md) that limits which entries go inside the index. These criteria are also used when importing through the console command.

```php
'criteria' => [
    'section' => 'blog',
],
```

#### `transformer`
The [transformer](http://fractal.thephpleague.com/transformers/) that should be used to define the data that should be sent to Algolia for each element. If you don’t set this, the default transformer will be used, which includes all of the element’s direct attribute values, but no custom field values.

```php
// Can be set to a function
'transformer' => function(craft\elements\Entry $entry) {
    return [
        'title' => $entry->title,
        'id' => $entry->id,
        'url' => $entry->url,
    ];
},

// Or a string/array that defines a Transformer class configuration
'transformer' => 'MyTransformerClassName',

// Or a Transformer class instance
'transformer' => new MyTransformerClassName(),
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

#### `splitElementIndex`
For long documents it is advised to divide the element into multiple rows to keep each row within row data size. This can be done using `splitElementIndex`.
Array items are array indexes returned from the transformer.

```php
'splitElementIndex' => [
    'summary',
    'matrixElement'
]
```

*Important* - distinctId (available after indexing) must be set up as an attribute for faceting for deletion of objects to work when using splitElementIndex.

## Twig variables
You can access the Algolia settings set in your config file through the following Twig variables.

```twig
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

As with the import command, `indexName` is not required, when flushing Scout will ask you to confirm that you really want to clear all the data in your index.

### Refreshing
Does a flush/clear first and then imports the index again.

```
./craft scout/index/refresh <indexName?>
```

## Credits
- [Craft Algolia](https://github.com/aaronwaldon/craft-algolia) by aaronwaldon as a base to start from
- @larsboldt for the Split Element Index option

Brought to you by [Rias](https://rias.be)
