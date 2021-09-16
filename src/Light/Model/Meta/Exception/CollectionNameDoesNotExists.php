<?php

declare(strict_types=1);

namespace Light\Model\Meta\Exception;

use Exception;
use Light\Model;

/**
 * Class CollectionNameDoesNotExists
 * @package Model\Meta\Exception
 */
class CollectionNameDoesNotExists extends Exception
{
  /**
   * CollectionNameDoesNotExists constructor.
   * @param Model $model
   */
  public function __construct(Model $model)
  {
    parent::__construct("CollectionNameDoesNotExists: " . var_export($model, true));
  }
}