<?php

declare(strict_types=1);

namespace Light\Crud\AdminHistory;

use Light\Crud;

/**
 * Class Controller
 * @package Light\Crud\AdminHistory
 *
 * @crud-title Действия администраторов
 * @crud-sorting {"dateTime": -1}
 *
 * @crud-filter {"type": "search", "by": ["search"]}
 */
class Controller extends Crud
{
  /**
   * @return string
   */
  public function getModelClassName()
  {
    return Model::class;
  }

  /**
   * @return array
   */
  public function getControls(): array
  {
    // TODO: Сделать просмотр подробностей
    return [];
  }

  /**
   * @return array
   */
  protected function getHeaderButtons(): array
  {
    // TODO: Сделать удаление логов по фильтру
    return parent::getHeaderButtons();
  }

  /**
   * @return array
   */
  public function getHeader(): array
  {
    return [
      'admin' => [
        'title' => 'Администратор',
        'source' => function (Model $adminHistory) {
          return $adminHistory->admin['login'];
        }],
      'dateTime' => ['title' => 'Дата/Время', 'type' => 'datetime'],
      'type' => [
        'title' => 'Действие',
        'source' => function (Model $adminHistory) {

          switch ($adminHistory->type) {
            case Model::TYPE_READ_TABLE;
              return "<span class='label label-info'>Просмотр таблицы</span>";

            case Model::TYPE_READ_ENTITY;
              return "<span class='label label-info'>Просмотр записи</span>";

            case Model::TYPE_WRITE_ENTITY;
              return "<span class='label label-warning'>Редактирование записи</span>";

            case Model::TYPE_CREATE_ENTITY;
              return "<span class='label label-warning'>Создание записи</span>";
          }
          return "<span class='label label-danger'>Неизвестно</span>";
        }
      ],
      'section' => [
        'title' => 'Раздел',
        'source' => function (Model $adminHistory) {
          $content = "<b>{$adminHistory->section}</b>";
          try {
            $fields = [];
            foreach (array_values($adminHistory->entity) as $values) {
              if (is_string($values)) {
                $fields[] = $values;
              }
            }
            $content .= '<br>' . implode(', ', $fields);
          } catch (\Exception $e) {
          }
          return $content;
        }
      ],
    ];
  }
}
