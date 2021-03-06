<?php

declare(strict_types=1);

namespace Light\Form\Element;

use Light\Model;

/**
 * Class SelectMulti
 * @package Light\Form\Element
 */
class SelectMulti extends ElementAbstract
{
  /**
   * @var string
   */
  public $elementTemplate = 'element/select-multi';

  /**
   * @var array
   */
  public $fields = [
    'title' => 'title',
    'image' => 'image'
  ];

  /**
   * @var Model
   */
  public $source = null;

  /**
   * @return string
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * @param string $field
   */
  public function setFields(array $fields)
  {
    $this->fields = $fields;
  }

  /**
   * @return Model
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * @param string $source
   */
  public function setSource(string $source)
  {
    $this->source = $source;
  }

  /**
   * @return array|bool|int|object|string|null
   */
  public function getValue()
  {
    $value = parent::getValue();

    if (!$value) {
      return [];
    }

    return $value;
  }
}
