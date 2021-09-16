<?php

declare(strict_types=1);

namespace Light\Model\Driver\Mongodb;

use Light\Model\Driver\DocumentAbstract;
use MongoDB\BSON\ObjectID;

/**
 * Class Document
 * @package Light\Model\Driver\Mongodb
 */
class Document extends DocumentAbstract
{
  /**
   * @return int
   */
  public function getTimestamp(): int
  {
    $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

    if (!$primaryValue) {
      return 0;
    }

    $objectId = new ObjectID($primaryValue);
    return $objectId->getTimestamp();
  }
}