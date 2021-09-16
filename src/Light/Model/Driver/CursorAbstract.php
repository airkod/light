<?php

declare(strict_types=1);

namespace Light\Model\Driver;

use ArrayAccess;
use Countable;
use Iterator;
use Light\Model;

/**
 * Interface CursorAbstract
 * @package Light\Driver
 */
abstract class CursorAbstract implements Iterator, ArrayAccess, Countable
{
  /**
   * @var Model[]
   */
  protected $_documents = [];

  /**
   * @var Model
   */
  private $_model = null;

  /**
   * @var int
   */
  private $_cursorIndex = 0;

  /**
   * @var array
   */
  private $_cursorData = [];

  /**
   * @var array
   */
  private $_config = null;

  /**
   * CursorAbstract constructor.
   *
   * @param Model $model
   * @param array $data
   * @param array $config
   */
  public function __construct(Model $model, array $data, array $config = [])
  {
    $this->_model = $model;
    $this->_cursorData = $data;
    $this->_config = $config;
  }

  /**
   * @return array
   */
  public function getConfig()
  {
    return $this->_config;
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    $arrayData = [];

    foreach ($this as $document) {
      $arrayData[] = $document->toArray();
    }

    return $arrayData;
  }

  /**
   * @return int
   */
  public function getCursorIndex(): int
  {
    return $this->_cursorIndex;
  }

  /**
   * @param int $cursorIndex
   */
  public function setCursorIndex(int $cursorIndex)
  {
    $this->_cursorIndex = $cursorIndex;
  }

  /**
   * @return int
   */
  public function save(): int
  {
    $savedCount = 0;

    foreach ($this->_documents as $document) {
      $savedCount += $document->save();
    }

    return $savedCount;
  }

  /**
   * @param int $offset
   * @return bool
   */
  public function offsetExists($offset)
  {
    if (is_numeric($offset)) {
      $data = $this->getCursorData();
      return isset($data[$offset]);
    }

    return false;
  }

  /**
   * @return array
   */
  public function getCursorData(): array
  {
    return $this->_cursorData;
  }

  /**
   * @param array $cursorData
   */
  public function setCursorData(array $cursorData)
  {
    $this->_cursorData = $cursorData;
  }

  /**
   * @param mixed $offset
   * @return Model
   */
  public function offsetGet($offset)
  {
    return $this->getRowWithIndex($this->getCursorData(), $offset);
  }

  /**
   * @param array $data
   * @param int $index
   *
   * @return Model
   */
  public function getRowWithIndex(array $data, int $index)
  {
    if (isset($this->_documents[$index])) {
      return $this->_documents[$index];
    }

    if (isset($data[$index])) {

      $modelClassName = $this->getModel()->getModelClassName();

      /** @var Model $model */
      $model = new $modelClassName();
      $model->populate(static::processDataRow($data[$index]), false);

      $this->_documents[$index] = $model;

      return $model;
    }

    return null;
  }

  /**
   * @return Model
   */
  public function getModel(): Model
  {
    return $this->_model;
  }


  /*************** \ArrayIterator implementation ***********/

  /**
   * @param Model $model
   */
  public function setModel(Model $model)
  {
    $this->_model = $model;
  }

  /**
   * Can be overi
   *
   * @param array $data
   * @return array
   */
  public function processDataRow(array $data): array
  {
    return $data;
  }

  /**
   * @param null $offset
   * @param null $value
   * @throws Exception\UnsupportedCursorOperation
   */
  public function offsetSet($offset = null, $value = null)
  {
    throw new Exception\UnsupportedCursorOperation("offsetSet - " . $offset);
  }

  /**
   * @param null $offset
   * @throws Exception\UnsupportedCursorOperation
   */
  public function offsetUnset($offset = null)
  {
    throw new Exception\UnsupportedCursorOperation("offsetUnset - " . $offset);
  }


  /*************** \Iterator implementation ***********/

  /**
   * @return Model
   */
  public function current()
  {
    return $this->getRowWithIndex($this->getCursorData(), $this->_cursorIndex);
  }

  /**
   * return null
   */
  public function next()
  {
    $this->_cursorIndex++;
  }

  /**
   * @return int|null
   */
  public function key()
  {
    if (isset($this->_cursorData[$this->_cursorIndex])) {
      return $this->_cursorIndex;
    }

    return null;
  }

  /**
   * @return bool
   */
  public function valid()
  {
    return isset($this->_cursorData[$this->_cursorIndex]);
  }

  /**
   *
   */
  public function rewind()
  {
    $this->_cursorIndex = 0;
  }


  /*************** \Countable implementation ***********/

  /**
   * @return int
   */
  public function count(): int
  {
    return count($this->_cursorData);
  }
}
