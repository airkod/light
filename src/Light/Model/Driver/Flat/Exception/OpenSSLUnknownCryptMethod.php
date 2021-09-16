<?php

namespace Light\Model\Driver\Flat\Exception;

use Exception;

/**
 * Class OpenSSLUnknownCryptMethod
 * @package Light\Model\Driver\Flat\Exception
 */
class OpenSSLUnknownCryptMethod extends Exception
{
  /**
   * OpenSSLUnknownCryptMethod constructor.
   *
   * @param string $method
   */
  public function __construct(string $method)
  {
    parent::__construct("OpenSSLUnknownCryptMethod: " . $method);
  }
}