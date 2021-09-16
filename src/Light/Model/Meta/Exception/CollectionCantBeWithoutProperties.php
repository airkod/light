<?php

declare(strict_types=1);

namespace Light\Model\Meta\Exception;

use Exception;
use Light\Model;

/**
 * Class CollectionNameDoesNotExists
 * @package Model\Meta\Exception
 */
class CollectionCantBeWithoutProperties extends Exception
{
  /**
   * CollectionNameDoesNotExists constructor.
   * @param Model $model
   */
  public function __construct(Model $model)
  {
    parent::__construct("CollectionCantBeWithoutProperties: " . var_export($model, true));
  }
}