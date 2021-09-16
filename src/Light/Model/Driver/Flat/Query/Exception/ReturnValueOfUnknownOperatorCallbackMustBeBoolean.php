<?php

namespace Light\Model\Driver\Flat\Query\Exception;

use Exception;

/**
 * Class ReturnValueOfUnknownOperatorCallbackMustBeBoolean
 * @package Light\Model\Driver\Flat\Query\Exception
 */
class ReturnValueOfUnknownOperatorCallbackMustBeBoolean extends Exception
{
  /**
   * ReturnValueOfUnknownOperatorCallbackMustBeBoolean constructor.
   * @param mixed $actual
   */
  public function __construct($actual)
  {
    parent::__construct("ReturnValueOfUnknownOperatorCallbackMustBeBoolean: " . $actual);
  }
}