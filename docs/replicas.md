---
title: Replicas
---
# Replicas

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