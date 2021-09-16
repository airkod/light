<?php

declare(strict_types=1);

namespace Light;

/**
 * Class Map
 * @package Light
 */
class Map
{
  /**
   * @param $data
   * @param $mapper
   * @param array|null $userData
   *
   * @return array|null
   */
  public static function execute($data, $mapper, array $userData = null)
  {
    if (!$data) {
      return null;
    }

    if (is_array($mapper)) {
      return self::executeAssoc($data, $mapper, $userData);
    }

    if (is_string($mapper)) {
      return self::executeLine($data, $mapper, $userData);
    }

    if ($mapper instanceof \Closure) {
      return self::executeLineClosure($data, $mapper, $userData);
    }
  }

  /**
   * @param $data
   * @return bool
   */
  private static function isSingle($data)
  {
    return $data instanceof \Light\Model\ModelInterface || (!isset($data[0]) && !($data instanceof \Light\Model\Driver\Mongodb\Cursor));
  }

  /**
   * @param $data
   * @param array $mapper
   * @param array|null $userData
   *
   * @return array|null
   */
  private static function executeAssoc($data, array $mapper, array $userData = null)
  {
    if (self::isSingle($data)) {
      return self::executeSingle($data, $mapper, $userData);
    }

    $mapped = [];
    foreach ($data as $row) {
      $mapped[] = self::executeSingle($row, $mapper, $userData);
    }
    return $mapped;
  }

  /**
   * @param $data
   * @param string $mapper
   * @param array|null $userData
   *
   * @return array|null
   */
  private static function executeLine($data, string $mapper, array $userData = null)
  {
    if (self::isSingle($data)) {
      return self::executeSingle($data, [$mapper], $userData)[$mapper];
    }

    $mapped = [];
    foreach ($data as $row) {
      $mapped[] = self::executeSingle($row, [$mapper], $userData)[$mapper];
    }
    return $mapped;
  }

  /**
   * @param $data
   * @param \Closure $mapper
   * @param array|null $userData
   *
   * @return array|mixed
   */
  private static function executeLineClosure($data, \Closure $mapper, array $userData = null)
  {
    if (self::isSingle($data)) {
      return self::transform($data, null, $mapper, $userData);
    }

    $mapped = [];
    foreach ($data as $row) {
      $mapped[] = self::transform($row, null, $mapper, $userData);
    }
    return $mapped;
  }

  /**
   * @param $data
   * @param array $mapper
   * @param array|null $userData
   *
   * @return array
   */
  private static function executeSingle($data, array $mapper, array $userData = null)
  {
    $mapped = [];

    foreach ($mapper as $dest => $value) {
      if (is_string($dest)) {
        $mapped[$dest] = self::transform($data, $dest, $value, $userData ?? []);
      } else {
        $mapped[$value] = self::transform($data, $dest, $value, $userData ?? []);
      }
    }

    return $mapped;
  }

  /**
   * @param $data
   * @param $dest
   * @param $value
   * @param array $userData
   *
   * @return mixed
   */
  private static function transform($data, $dest, $value, array $userData = null)
  {
    if ($value instanceof \Closure) {
      return $value($data, $userData ?: []);
    }

    if ($data instanceof \Light\Model) {
      return $data->getMeta()->hasProperty($value)
        ? $data->{$value}
        : $data->{$dest};
    }

    if (is_array($data)) {
      return $data[$value] ?? $data[$dest];
    }
  }
}
