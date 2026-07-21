---
title: Upgrade guide
---
# Upgrading to Scout 6

Scout 6 upgrades the [Algolia PHP API client](https://github.com/algolia/algoliasearch-client-php) from v2/v3 to **v4**.

Algolia is retiring the API endpoints that v2 and v3 of the client talk to on **August 14, 2026**, so moving to v4 is required to keep search working. See Algolia's [SDK versions notice](https://www.algolia.com/doc/libraries/sdk/v1/versions#php).

## For most sites

If you configure your indices through Scout and let it keep them in sync, this upgrade is transparent — the plugin abstracts the Algolia client away for you. Update as usual:

```bash
composer update studioespresso/craft-scout
```

Your existing index configuration, settings and front-end templates keep working unchanged. There are no project config or database migrations to run.

## Composer conflicts

Scout now requires `algolia/algoliasearch-client-php:^4.0` and no longer allows v2 or v3. If another package in your project still requires an older version of that client, Composer won't be able to resolve the update:

```
Problem 1
    - studioespresso/craft-scout 6.0.0 requires algolia/algoliasearch-client-php ^4.0
    - some/other-package 1.2.3 requires algolia/algoliasearch-client-php ^3.0
```

Update or remove the conflicting package so it allows v4 before upgrading Scout.

## Custom engines

This section only applies if you set a custom `engine` in your Scout config (the default `AlgoliaEngine` is already migrated). Custom engines need two changes.

### 1. Update the client type-hint

The `SearchClient` class moved namespaces in v4. Update the import in your engine (and anywhere else you type-hint it):

```php
// Before
use Algolia\AlgoliaSearch\SearchClient;

// After
use Algolia\AlgoliaSearch\Api\SearchClient;
```

Because Scout injects the client into your engine's constructor through the container, an out-of-date type-hint will cause the engine to fail to instantiate.

### 2. Replace `initIndex()` with index-name-scoped calls

v4 removes the `$client->initIndex()` object. Every operation now lives on the client itself, with the index name as the first argument:

```php
// Before (v3)
$index = $this->algolia->initIndex($indexName);
$index->saveObjects($objects);

// After (v4)
$this->algolia->saveObjects($indexName, $objects);
```

The full mapping used by Scout's own engine:

| v3 | v4 |
| --- | --- |
| `$c->initIndex($name)->saveObjects($objects)` | `$c->saveObjects($name, $objects)` |
| `$c->initIndex($name)->deleteObjects($ids)` | `$c->deleteObjects($name, $ids)` |
| `$c->initIndex($name)->deleteBy(['filters' => …])` | `$c->deleteBy($name, ['filters' => …])` |
| `$c->initIndex($name)->clearObjects()` | `$c->clearObjects($name)` |
| `$c->initIndex($name)->setSettings($s, ['forwardToReplicas' => $b])` | `$c->setSettings($name, $s, $b)` |
| `$c->initIndex($name)->getSettings()` | `$c->getSettings($name)` |
| `$c->initIndex($name)->search('', $params)` | `$c->searchSingleIndex($name, $params)` |

All of these still accept plain arrays and return plain arrays, exactly like v3.

## Resolving the client directly

If any of your own code resolves the Algolia client from Craft's container, update the class name to the new namespace:

```php
// Before
Craft::$container->get(\Algolia\AlgoliaSearch\SearchClient::class);

// After
Craft::$container->get(\Algolia\AlgoliaSearch\Api\SearchClient::class);
```

## Exceptions are unchanged

Exception classes stayed at `Algolia\AlgoliaSearch\Exceptions\*` (for example `NotFoundException`), so any `try`/`catch` around Scout or Algolia calls keeps working as-is.
