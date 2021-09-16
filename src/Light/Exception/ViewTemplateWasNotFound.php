<?php

declare(strict_types=1);

namespace Light\Exception;

use Exception;

/**
 * Class ViewTemplateWasNotFound
 * @package Light\Exception
 */
class ViewTemplateWasNotFound extends Exception
{
  /**
   * ViewTemplateWasNotFound constructor.
   * @param string $template
   */
  public function __construct(string $template)
  {
    parent::__construct('ViewTemplateWasNotFound: ' . $template, 500);
  }
}