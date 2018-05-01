<?php
/**
 * Scout plugin for Craft CMS 3.x.
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 *
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\models;

use Craft;
use craft\base\Model;
use rias\scout\Scout;

/**
 * @author    Rias
 *
 * @since     0.1.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var boolean
     */
    public $sync = true;

    /**
     * @var string
     */
    public $mappings = [];

    /* @var string */
    public $application_id = '';

    /* @var string */
    public $admin_api_key = '';

    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sync'], 'boolean'],
            [['application_id', 'admin_api_key'], 'string'],
            [['sync', 'application_id', 'admin_api_key'], 'required'],
        ];
    }
}
