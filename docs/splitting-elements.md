---
title: Events
---
# Events

[For long documents](https://www.algolia.com/doc/guides/sending-and-managing-data/prepare-your-data/how-to/indexing-long-documents/) it is advised to divide the element into multiple rows to keep each row within row data size. This can be done using `splitElementsOn()`.
> Make sure to return an array in your transformer for these keys.

```php
->splitElementsOn([
    'summary',
    'matrixFieldHandle'
])
```
::: warning *Important*
`distinctID` (available after indexing) must be set up as an attribute for faceting for deletion of objects to work when using splitElementsOn.
:::
