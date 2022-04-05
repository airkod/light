<?php

declare(strict_types=1);

namespace Light\Model\Driver\Mongodb;

use Exception;
use Light\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

/**
 * Class Driver
 * @package Light\Model\Driver\Mongodb
 */
class Driver extends Model\Driver\DriverAbstract
{
  /**
   * @var Manager[]
   */
  private $_manager = null;

  /**
   * @return int|null
   */
  public function save()
  {
    $bulk = new BulkWrite();

    $data = $this->_replaceIdToObjectId(
      $this->getModel()->getData()
    );

    $data = $this->_normalizeDataTypes($data);

    if ($this->getModel()->id) {
      $cond = $this->_replaceIdToObjectId(['id' => $this->getModel()->id]);
      $bulk->update($cond, ['$set' => $data], ['multi' => true, 'upsert' => false]);
    } else {
      $this->getModel()->id = (string)$bulk->insert($data);
    }

    $writeConcern = new WriteConcern(
      WriteConcern::MAJORITY, 1000
    );

    $result = $this->getManager()->executeBulkWrite(
      $this->getCollectionNamespace(), $bulk, $writeConcern
    );

    if (!$this->getModel()->id) {
      return $result->getInsertedCount();
    }

    return $result->getModifiedCount();
  }

  /**
   * @param array|string|null $cond
   * @return mixed
   */
  private function _replaceIdToObjectId($cond = null)
  {
    if (!is_array($cond)) {
      $cond = [$cond];
    }

    if (is_array($cond) && array_key_exists('id', $cond)) {

      $cond['_id'] = new ObjectId(
        !empty($cond['id']) ? $cond['id'] : null
      );
      unset($cond['id']);
    }

    return $cond;
  }

  /**
   * @param array $data
   * @return array
   */
  private function _normalizeDataTypes(array $data = [])
  {
    foreach ($data as $name => $value) {

      if ($value instanceof Model) {
        $data[$name] = $value->{$value->getMeta()->getPrimary()};
      }

      if ($value instanceof Cursor) {

        $ids = [];
        foreach ($value as $record) {
          $ids[] = $record->{$value->getModel()->getMeta()->getPrimary()};
        }

        $data[$name] = $ids;
      }
    }

    return $data;
  }

  /**
   * @return Manager
   */
  public function getManager(): Manager
  {
    $managerConfigKey = md5(var_export([], true));

    if (empty($this->_manager[$managerConfigKey])) {

      $config = $this->getConfig();

      $credentials = null;
      if (isset($config['username']) && isset($config['password'])) {
        $credentials = implode(':', [$config['username'], $config['password']]) . '@';
      }

      $servers = [];

      foreach ($config['servers'] as $server) {
        $servers[] = implode(':', [$server['host'], $server['port']]);
      }

      $servers = implode(',', $servers) . '/' . $config['db'];

      $replicaSetName = null;
      if (isset($config['replicaSetName']) && !empty($config['replicaSetName'])) {
        $replicaSetName = '?replicaSet=' . $config['replicaSetName'];
      }

      $connection = 'mongodb://' . $credentials . $servers . $replicaSetName;

      $this->_manager[$managerConfigKey] = new Manager($connection);
    }

    return $this->_manager[$managerConfigKey];
  }

  /**
   * @return string
   */
  public function getCollectionNamespace()
  {
    return implode('.', [
      $this->getConfig()['db'],
      $this->getModel()->getMeta()->getCollection()
    ]);
  }

  /**
   * @param array|string|null $cond
   * @param int|null $limit
   *
   * @return int
   */
  public function remove($cond = null, int $limit = null): int
  {
    if ($this->getModel()->id) {

      $cond = $this->_replaceIdToObjectId([
        'id' => $this->getModel()->id
      ]);

      $limit = 1;
    } else {
      list($cond) = $this->_processQuery($cond);
      $cond = $this->_normalizeDataTypes($cond);
    }

    $bulk = new BulkWrite();

    $bulk->delete($cond, ['limit' => $limit]);

    $writeConcern = new WriteConcern(
      WriteConcern::MAJORITY, 100
    );

    $result = $this->getManager()->executeBulkWrite(
      $this->getCollectionNamespace(), $bulk, $writeConcern
    );

    return $result->getDeletedCount();
  }

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   *
   * @return array
   */
  private function _processQuery($cond = null, $sort = null): array
  {
    if ($cond === null) {
      $cond = [];
    }

    $cond = $this->_replaceIdToObjectId($cond);

    if ($sort === null) {
      $sort = [];
    }

    return [$cond, $sort];
  }

  /**
   * @param array $map
   * @return array
   */
  private function _getProjection($map): array
  {
    $projection = [];
    if ($map && is_array($map) && count($map)) {
      foreach ($map as $field) {
        $projection[$field] = 1;
      }
    }

    return $projection;
  }

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   * @param array|null $map
   *
   * @return Model|null
   */
  public function fetchOne($cond = null, $sort = null, $map = null)
  {
    list($cond, $sort) = $this->_processQuery($cond, $sort);

    $cond = $this->_normalizeDataTypes($cond);
    $projection = $this->_getProjection($map);

    $query = new Query($cond, [
      'limit' => 1,
      'sort' => $sort,
      'projection' => $projection
    ]);

    $cursor = new Cursor($this->getModel(), $query, $this->getConfig());

    try {
      return $cursor->offsetGet(0);
    } catch (\Throwable $e) {
    }

    return null;
  }

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   *
   * @param int|null $count
   * @param int|null $offset
   *
   * @param array|string|null $map
   *
   * @return Cursor
   */
  public function fetchAll(
    $cond = null,
    $sort = null,
    int $count = null,
    int $offset = null,
    $map = null
  )
  {
    list($cond, $sort) = $this->_processQuery($cond, $sort);

    $cond = $this->_normalizeDataTypes($cond);
    $projection = $this->_getProjection($map);

    $query = new Query($cond, [
      'sort' => $sort,
      'skip' => $offset,
      'limit' => $count,
      'projection' => $projection
    ]);

    return new Cursor($this->getModel(), $query, $this->getConfig());
  }

  /**
   * @param array|string|null $cond
   * @return int
   */
  public function count($cond = null): int
  {
    list($cond) = $this->_processQuery($cond, null);

    $cond = $this->_normalizeDataTypes($cond);

    if (!count($cond)) {
      $cond = null;
    }

    $command = new Command([
      'count' => $this->getModel()->getMeta()->getCollection(),
      'query' => $cond
    ]);

    try {
      $cursor = $this
        ->getManager()
        ->executeCommand($this->getConfig()['db'], $command);

      $res = current($cursor->toArray());
      return $res->n;
    } catch (Exception $e) {
    }

    return 0;
  }

  /**
   * @param array|null $data
   *
   * @return int
   */
  public function batchInsert(array $data = null): int
  {
    $bulk = new BulkWrite();

    foreach ($data as $dataItem) {

      $modelClassName = $this->getModel()->getModelClassName();

      /** @var Model $model */
      $model = new $modelClassName();
      $model->populate($dataItem);

      $bulk->insert(
        $this->_normalizeDataTypes(
          $model->getData()
        )
      );
    }

    if ($bulk->count()) {

      $collectionNamespace = $this->getCollectionNamespace();

      $writeResults = $this->getManager()->executeBulkWrite($collectionNamespace, $bulk);
      return $writeResults->getInsertedCount();
    }

    return 0;
  }

  /**
   * @param array|null $cond
   * @param array|null $data
   *
   * @return int
   */
  public function update(array $cond = null, array $data = null): int
  {
    list($cond) = $this->_processQuery($cond, null);

    $cond = $this->_normalizeDataTypes($cond);

    if (!count($cond)) {
      $cond = null;
    }

    $modelClassName = $this->getModel()->getModelClassName();

    /** @var Model $model */
    $model = new $modelClassName();
    $model->populate($data);

    $bulk = new BulkWrite();

    $bulk->update(
      $cond,
      [
        '$set' => $this->_normalizeDataTypes(
          $model->getData()
        )
      ],
      ['multi' => true]
    );

    if ($bulk->count()) {
      $collectionNamespace = $this->getCollectionNamespace();

      $writeResults = $this->getManager()->executeBulkWrite($collectionNamespace, $bulk);
      return $writeResults->getInsertedCount();
    }

    return 0;
  }
}
