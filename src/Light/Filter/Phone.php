<?php

declare(strict_types=1);

namespace Light\Filter;

/**
 * Class Phone
 * @package Light\Filter
 */
class Phone extends FilterAbstract
{
  /**
   * @param $value
   * @return mixed|string
   */
  public function filter($value)
  {
    return '+' . trim(str_replace(['+', '-', ' ', '-', '(', ')', '.', ','], '', $value));
  }
}
