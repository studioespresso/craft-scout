<?php
/**
 * Scout plugin for Craft CMS 3.x
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\tasks;

use rias\scout\Scout;

use Craft;
use craft\base\Task;

/**
 * @author    Rias
 * @package   Scout
 * @since     0.1.0
 */
class ScoutTask extends Task
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTotalSteps(): int
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function runStep(int $step)
    {
        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('scout', 'ScoutTask');
    }
}
