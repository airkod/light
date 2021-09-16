<?php

declare(strict_types=1);

namespace Light\Model\Driver\Flat;

use Light\Model\Driver\DocumentAbstract;

/**
 * Class Document
 * @package Light\Model\Driver\Flat
 */
class Document extends DocumentAbstract
{
  public function getTimestamp(): int
  {
    $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

    if (!$primaryValue) {
      return 0;
    }

    return intval(substr($primaryValue, Driver::ID_UNIQUE_LENGTH));
  }
}