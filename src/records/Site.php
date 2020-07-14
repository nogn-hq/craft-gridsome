<?php
/**
 * Craft Gridsome plugin for Craft CMS 3.x
 *
 * Making headless a dream
 *
 * @link      bensheedy.com
 * @copyright Copyright (c) 2020 Ben Sheedy
 */

namespace nogn\craftgridsome\records;

use craft\db\ActiveRecord;

/**
 * Site record
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string|null $sectionIds
 *
 * @author Ben Sheedy
 * @since 1.0
 */
class Site extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%nogn_sites}}';
    }
}

