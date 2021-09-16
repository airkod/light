<?php

declare(strict_types=1);

namespace Light\Model\Driver;

use ArrayAccess;
use Light\Model;
use Light\Model\Meta\Property;
use MongoDB\BSON\ObjectId;

/**
 * Interface DocumentInterface
 * @package Light\Driver
 */
abstract class DocumentAbstract implements ArrayAccess
{
  /**
   * @var Model|null
   */
  private $_model = null;

  /**
   * @var array
   */
  private $_data = [];

  /**
   * Document constructor.
   * @param Model $model
   */
  public function __construct(Model $model)
  {
    $this->_model = $model;
  }

  /**
   * Populate model with data
   *
   * @param array $data
   */
  public function populate(array $data, bool $fromSet = true)
  {
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {

      if (isset($data[$property->getName()])) {

        $this->setProperty($property, $data[$property->getName()], $fromSet);
      }
    }
  }

  /**
   * @param array $data
   */
  public function populateWithoutQuerying(array $data)
  {
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {
      if (isset($data[$property->getName()])) {
        $this->setProperty($property, $data[$property->getName()], false, true);
      }
    }
  }

  /**
   * @return Model|null
   */
  public function getModel()
  {
    return $this->_model;
  }

  /**
   * @param Model $model
   */
  public function setModel(Model $model)
  {
    $this->_model = $model;
  }

  /**
   * @param Property $property
   * @param $value
   * @param bool $fromSet
   */
  public function setProperty(Property $property, $value, bool $fromSet = false, $isPopulateWithoutQuerying = false)
  {
    if ($isPopulateWithoutQuerying) {
      $this->_data[$property->getName()] = $value;
      return;
    }
    $this->_data[$property->getName()] = $this->_castDataType($property, $value, $fromSet);
  }

  /**
   * @param Property $property
   * @param $value
   * @param bool $isSet
   * @param bool $toArray
   *
   * @return array|bool|float|int|Model|null|string
   * @throws Exception\PropertyHasDifferentType
   */
  protected function _castDataType(Property $property, $value, bool $isSet = true, bool $toArray = false)
  {
    if (in_array($property->getType(), ['integer', 'array', 'string', 'boolean', 'NULL'])) {
      settype($value, $property->getType());
      return $value;
    }

    if (
      substr($property->getType(), -2) === '[]'
      && class_exists(substr($property->getType(), 0, -2), true)
      && is_subclass_of(substr($property->getType(), 0, -2), '\\Light\\Model')
    ) {
      if (is_array($value)) {

        /** @var Model $modelClassName */
        $modelClassName = substr($property->getType(), 0, -2);

        /** @var Model $modelClassObject */
        $modelClassObject = new $modelClassName;

        switch ($modelClassObject->getMeta()->getPrimary()) {

          case 'id':
            $objects = $modelClassName::fetchAll(['_id' => ['$in' => array_map(function ($id) {

              /** @var Model $id */
              return new ObjectId(
                is_object($id) ?
                  $id->{$id->getMeta()->getPrimary()} :
                  $id
              );

            }, $value)]]);
            break;

          default:
            $objects = $modelClassName::fetchAll([
              $modelClassObject->getMeta()->getPrimary() => ['$in' => $value]
            ]);
        }

        if ($toArray) {
          return $objects->toArray();
        }

        return $objects;
      }

      if (!$isSet) {

        if ($toArray) {
          return $value ? $value->toArray() : [];
        }

        return $value;
      }

      $records = [];
      foreach ($value as $record) {
        $records[] = $record->{$record->getMeta()->getPrimary()};
      }

      return $records;
    }

    if (
      class_exists($property->getType(), true)
      && is_subclass_of($property->getType(), '\\Light\\Model')
    ) {
      if (is_string($value)) {

        /** @var Model $modelClassName */
        $modelClassName = $property->getType();

        /** @var Model $modelClassObject */
        $modelClassObject = new $modelClassName;

        $object = $modelClassName::fetchOne([
          $modelClassObject->getMeta()->getPrimary() => $value
        ]);

        if ($toArray) {
          return $object->toArray();
        }

        return $object;
      } else if (is_null($value)) {
        return null;
      }

      if (!$isSet) {

        if ($toArray) {
          return $value->toArray();
        }

        return $value;
      }

      return $value->{$value->getMeta()->getPrimary()};
    }

    throw new Exception\PropertyHasDifferentType(
      $this->getModel()->getMeta()->getCollection(),
      $property->getName(),
      $property->getType(),
      gettype($value)
    );
  }

  /**
   * @param $name
   * @return bool
   */
  public function __isset($name)
  {
    return isset($this->_data[$name]);
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    $arrayData = [];

    foreach ($this->getModel()->getMeta()->getProperties() as $property) {
      $arrayData[$property->getName()] = $this->getProperty($property->getName(), true);
    }

    return $arrayData;
  }

  /**
   * @param string $name
   * @param bool $toArray
   *
   * @return mixed
   *
   * @throws Exception\PropertyWasNotFound
   */
  public function getProperty(string $name, bool $toArray = false)
  {
    $data = $this->getData();

    foreach ($this->getModel()->getMeta()->getProperties() as $property) {

      if ($property->getName() == $name) {

        $value = isset($data[$name]) ? $data[$name] : null;
        return $this->_castDataType($property, $value, false, $toArray);
      }
    }

    throw new Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
  }

  /**
   * @return array
   */
  public function getData(): array
  {
    return $this->_data;
  }

  /**
   * @param array $data
   */
  public function setData(array $data)
  {
    $this->_data = $data;
  }

  /**
   * @param mixed $offset
   * @return bool
   */
  public function offsetExists($offset)
  {
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {

      if ($property->getName() == $offset) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param mixed $offset
   * @return mixed
   */
  public function offsetGet($offset)
  {
    return $this->__get($offset);
  }

  /**
   * @param string $name
   * @return mixed
   */
  public function __get(string $name)
  {
    return $this->getProperty($name);
  }

  /**
   * @param string $name
   * @param $value
   *
   * @throws Exception\PropertyWasNotFound
   */
  public function __set(string $name, $value)
  {
    $isSet = false;

    foreach ($this->getModel()->getMeta()->getProperties() as $property) {

      if ($property->getName() == $name) {
        $this->setProperty($property, $value, true);
        $isSet = true;
      }
    }

    if (!$isSet) {
      throw new Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
    }
  }

  /**
   * @param mixed $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value)
  {
    $this->__set($offset, $value);
  }

  /**
   * @param mixed $offset
   */
  public function offsetUnset($offset)
  {
    $this->__set($offset, null);
  }

  /**
   * @return int
   */
  abstract public function getTimestamp(): int;
}
