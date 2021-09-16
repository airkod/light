<?php

declare(strict_types=1);

namespace Light\Form\Element;

/**
 * Class Text
 * @package Light\Form\Element
 */
class Text extends ElementAbstract
{
  /**
   * @var string
   */
  public $elementTemplate = 'element/text';

  /**
   * @return string
   */
  public function getValue(): string
  {
    $value = parent::getValue();

    if ($value === null) {
      return '';
    }

    return $value;
  }
}