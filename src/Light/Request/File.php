<?php

declare(strict_types=1);

namespace Light\Request;

/**
 * Class File
 * @package Light\Request
 */
class File
{
  /**
   * @var string
   */
  public $_name = null;

  /**
   * @var string
   */
  public $_type = null;

  /**
   * @var string
   */
  public $_tmpName = null;

  /**
   * @var int
   */
  public $_error = 0;

  /**
   * @var int
   */
  public $_size = 0;

  /**
   * File constructor.
   * @param array $options
   */
  public function __construct(array $options = [])
  {
    foreach ($options as $key => $value) {
      if (is_callable([$this, 'set' . ucfirst($key)])) {
        $this->{'set' . ucfirst($key)}($value);
      }
    }
  }

  /**
   * @return string
   */
  public function getName(): ?string
  {
    return $this->_name;
  }

  /**
   * @param string $name
   */
  public function setName(?string $name): void
  {
    $this->_name = $name;
  }

  /**
   * @return string
   */
  public function getType(): ?string
  {
    return $this->_type;
  }

  /**
   * @param string $type
   */
  public function setType(?string $type): void
  {
    $this->_type = $type;
  }

  /**
   * @return string
   */
  public function getTmpName(): ?string
  {
    return $this->_tmpName;
  }

  /**
   * @param string $tmpName
   */
  public function setTmpName(?string $tmpName): void
  {
    $this->_tmpName = $tmpName;
  }

  /**
   * @return int
   */
  public function getError(): int
  {
    return $this->_error;
  }

  /**
   * @param int $error
   */
  public function setError(int $error): void
  {
    $this->_error = $error;
  }

  /**
   * @return int
   */
  public function getSize(): int
  {
    return $this->_size;
  }

  /**
   * @param int $size
   */
  public function setSize(int $size): void
  {
    $this->_size = $size;
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    return [
      'name' => $this->getName(),
      'type' => $this->getType(),
      'tmpName' => $this->getTmpName(),
      'error' => $this->getError(),
      'size' => $this->getSize()
    ];
  }
}
