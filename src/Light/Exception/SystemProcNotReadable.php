<?php

declare(strict_types=1);

namespace Light\Exception;

/**
 * Class SystemProcNotReadable
 * @package Light\Exception
 */
class SystemProcNotReadable extends \Exception
{
  /**
   * SystemProcNotReadable constructor.
   * @param string $proc
   */
  public function __construct(string $proc)
  {
    parent::__construct('SystemProcNotReadable: ' . $proc, 500);
  }
}