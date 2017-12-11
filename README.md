# Scout plugin for Craft CMS 3.x

Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require rias/scout

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Scout.

## Setup

To define your Indices, create a new `scout.php` file within your `config` folder. This file should return an array with 3 keys, an `application_id`, your `admin_api_key` (which are both found in your [Algolia](https://www.algolia.com/api-keys) account) and a `mappings` key, which defines your site's mappings.

Within the mappings array, each index is represented by an array, and values are the configuration.

```php
<?php

return [
    "application_id" => "algolia",
    "admin_api_key" => "algolia",
    "mappings" => [
        [
            'indexName' => 'blog',
            'elementType' => \craft\elements\Entry::class,
            'filter' => function (craft\base\Element $element) {
                return $element->section->handle == 'blog';
            },
            'transformer' => function (craft\base\Element $element) {
                return $element->toArray();
            }
        ],
    ],
];
```

### Mapping configuration settings

#### `indexName`
The index name in Algolia, if you don't already have an index created, Scout will create one for you.

#### `elementType`
The element type that this index contains, most of the time this will be `\craft\elements\Entry::class`

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

#### `filter`
The criteria on which to filter the `elementType`, in the example we only want entries from the section `blog`

```php
'filter' => function (craft\base\Element $element) {
    return $element->section->handle == 'blog';
},
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


## Scout Roadmap

Some things to do, and ideas for potential features:

* Use the queue to process Algolia updates
* Add console commands to flush/import indexes

Brought to you by [Rias](https://rias.be)
