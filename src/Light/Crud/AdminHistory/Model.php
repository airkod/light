<?php

declare(strict_types=1);

namespace Light\Crud\AdminHistory;

/**
 * @collection LightAdminHistory
 *
 * @property string $id
 * @property array $admin
 * @property integer $dateTime
 * @property string $type
 * @property string $section
 * @property array $entity
 * @property array $was
 * @property array $became
 *
 * @property string $search
 */
class Model extends \Light\Model
{
  const TYPE_READ_TABLE = 'read-table';
  const TYPE_READ_ENTITY = 'read-entity';
  const TYPE_CREATE_ENTITY = 'create-entity';
  const TYPE_WRITE_ENTITY = 'write-entity';
}