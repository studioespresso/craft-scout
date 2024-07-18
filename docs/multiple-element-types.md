---
title: Events
---
# Multiple element types in 1 index


#### `->getElements(callable $queries)`
This function can be used to query multiple different Element types. It should return an array of ElementQuery objects.

```php
->getElements(function () {
    return [
        Entry::find()->section('blog'),
        Category::find()->group('blogCategories'),
    ];
});
```

::: warning *Important*
When `->getElements()` is used, `->criteria()` and `->elementType()` are ignored. 
Combining elementTypes also changes the `->transformer()` function - that should be addapted to use `\craft\base\Element $element` as an argument:
:::

```php
->transformer(function (\craft\base\Element $element) {
    return [
        'title' => $element->title
    ];
})
```