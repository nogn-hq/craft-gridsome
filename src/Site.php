<?php
/**
 * @link      bensheedy.com
 * @copyright Copyright (c) 2020 Ben Sheedy
 */

namespace nogn\craftgridsome;

use Craft;
use craft\base\Model;

/**
 * Site model
 *
 * @author Ben Sheedy
 * @since 1.0
 */

class Site extends Model
{
  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $url;

  /**
   * @var array
   */
  public $sectionIds = [];

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['name', 'url'],'trim'],
      [['name', 'url'],'required'],
      [
        ['sectionIds'],
        function() {
            $this->sectionIds = $this->sectionIds ? array_values($this->sectionIds) : [];
        }
    ],
    ];
  }
  
}