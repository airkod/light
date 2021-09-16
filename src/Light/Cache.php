<?php

declare(strict_types=1);

namespace Light;

use MongoDB\BSON\Regex;

/**
 * Class Cache
 * @package Light
 */
class Cache
{
  /**
   * @param array $title
   * @return array|mixed|null
   */
  public static function get(array $title)
  {
    if (!(Front::getInstance()->getConfig()['light']['cache']['enabled'] ?? false)) {
      return null;
    }

    $cache = \Light\Cache\Model::one([
      'title' => json_encode($title)
    ]);

    if (!$cache) {
      return null;
    }

    if ($cache->expire < time()) {
      $cache->remove();
    }

    return $cache->data;
  }

  /**
   * @param array $title
   * @param array|null $data
   */
  public static function set(array $title, $data, int $lifetime = null)
  {
    $title = json_encode($title);

    Cache\Model::remove([
      'title' => $title
    ]);

    Cache\Model::batchInsert([[
      'expire' => time() + ($lifetime ?: Front::getInstance()->getConfig()['light']['cache']['lifetime'] ?? 3600),
      'title' => $title,
      'data' => $data
    ]]);
  }

  /**
   * @return int
   */
  public static function clear(array $filter = null)
  {
    $filter = $filter ?: [];
    return Cache\Model::remove($filter);
  }

  /**
   * @param array|null $tags
   */
  public static function clean(array $tags = null)
  {
    if (!count($tags)) {
      return;
    }
    $cond = ['$or' => []];
    foreach ($tags as $tag) {
      $cond['$or'][] = [
        'title' => new Regex(quotemeta($tag), 'i')
      ];
    }
    self::clear($cond);
  }

  /**
   * @param array $title
   * @param callable $callable
   *
   * @return array|mixed|null
   */
  public static function profile(array $title, callable $callable)
  {
    if ($data = self::get($title)) {
      return $data;
    }

    $data = $callable();
    self::set($title, $data);
    return $data;
  }
}
