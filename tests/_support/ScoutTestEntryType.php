<?php

use craft\elements\Entry;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\helpers\StringHelper;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;

/**
 * Test helper that creates (and saves) an entry type whose field layout
 * includes the entry title field.
 *
 * Since Craft 5, `EntryType::$hasTitleField` is derived on save from whether the
 * field layout includes the `title` field (see `craft\services\Entries`). An
 * entry type whose layout has no title field therefore ends up with
 * `hasTitleField = false`, which means entry titles are never stored - breaking
 * any test that filters on (`->title()`) or asserts the value of a title.
 */
class ScoutTestEntryType
{
    public static function create(string $handle = 'article', string $name = 'Article'): EntryType
    {
        $entryType = new EntryType([
            'name' => $name,
            'handle' => $handle,
            'titleFormat' => null,
            'uid' => StringHelper::UUID(),
        ]);

        $fieldLayout = new FieldLayout(['type' => Entry::class]);
        $tab = new FieldLayoutTab(['name' => 'Content', 'layout' => $fieldLayout]);
        $tab->setElements([new EntryTitleField()]);
        $fieldLayout->setTabs([$tab]);
        $entryType->setFieldLayout($fieldLayout);

        Craft::$app->getEntries()->saveEntryType($entryType);

        return Craft::$app->getEntries()->getEntryTypeByHandle($handle);
    }
}
