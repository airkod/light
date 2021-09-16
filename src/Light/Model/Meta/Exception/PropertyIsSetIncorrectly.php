<?php

declare(strict_types=1);

namespace Light\Model\Meta\Exception;

use Exception;
use Light\Model;

/**
 * Class PropertyIsSetIncorrectly
 * @package Meta\Exception
 */
class PropertyIsSetIncorrectly extends Exception
{
  /**
   * PropertyIsSetIncorrectly constructor.
   * @param Model $model
   * @param string $line
   */
  public function __construct(Model $model, string $line)
  {
    parent::__construct("PropertyIsSetIncorrectly: line: '" . $line . "', model: " . var_export($model, true));
  }
}