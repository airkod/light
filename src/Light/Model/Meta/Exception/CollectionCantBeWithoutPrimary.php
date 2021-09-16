<?php

declare(strict_types=1);

namespace Light\Model\Meta\Exception;

use Exception;
use Light\Model;

/**
 * Class CollectionCantBeWithoutPrimary
 * @package Model\Meta\Exception
 */
class CollectionCantBeWithoutPrimary extends Exception
{
  /**
   * CollectionCantBeWithoutPrimary constructor.
   * @param Model $model
   */
  public function __construct(Model $model)
  {
    parent::__construct("CollectionCantBeWithoutPrimary: " . var_export($model, true));
  }
}