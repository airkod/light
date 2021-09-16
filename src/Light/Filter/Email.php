<?php

declare(strict_types=1);

namespace Light\Filter;

/**
 * Class Email
 * @package Light\Filter
 */
class Email extends FilterAbstract
{
  /**
   * @param $value
   * @return mixed|string
   */
  public function filter($value)
  {
    return trim(strtolower($value));
  }
}
