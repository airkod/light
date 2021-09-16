<?php

declare(strict_types=1);

namespace Light\Form\Element;

/**
 * Class MultiSelectKeyValue
 * @package App\Module\Admin\Form\Element\MultiSelectKeyValue
 */
class MultiSelectKeyValue extends ElementAbstract
{
  /**
   * @var string
   */
  public $elementTemplate = 'element/multi-select-key-value';

  /**
   * @var string
   */
  public $keyLabel = 'Key';

  /**
   * @var string
   */
  public $valueLabel = 'Value';

  /**
   * @return string
   */
  public function getKeyLabel(): string
  {
    return $this->keyLabel;
  }

  /**
   * @param string $keyLabel
   */
  public function setKeyLabel(string $keyLabel): void
  {
    $this->keyLabel = $keyLabel;
  }

  /**
   * @return string
   */
  public function getValueLabel(): string
  {
    return $this->valueLabel;
  }

  /**
   * @param string $valueLabel
   */
  public function setValueLabel(string $valueLabel): void
  {
    $this->valueLabel = $valueLabel;
  }

  /**
   * @return array|bool|int|object|string|null
   */
  public function getValue()
  {
    $value = parent::getValue();

    if ($value === null) {
      return [];
    }

    return $value;
  }
}
