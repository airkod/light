<?php

declare(strict_types=1);

namespace Light;

use Exception;
use Light\Crud\AuthCrud;
use Light\Form\Generator;
use Light\Model\ModelInterface;
use MongoDB\BSON\Regex;
use ReflectionClass;

/**
 * Class Crud
 * @package Light
 */
abstract class Crud extends AuthCrud
{
  /**
   * @var View
   */
  public $view = null;

  /**
   * @var Model
   */
  public $model = null;

  /**
   * @return string
   */
  protected function getModelClassName()
  {
    $controllerClassPars = explode('\\', get_class($this));

    $entity = end($controllerClassPars);

    return implode('\\', [
      Front::getInstance()->getConfig()['light']['loader']['namespace'],
      'Model',
      $entity
    ]);
  }

  /**
   * @return string
   */
  protected function getEntity()
  {
    $controllerClassPars = explode('\\', get_class($this));

    return end($controllerClassPars);
  }

  /**
   * @return false|string
   * @throws Exception
   */
  public function position()
  {
    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    /** @var Model $model */
    $model = new $modelClassName();

    if (!$model->getMeta()->hasProperty('position')) {
      throw new Exception('Model doesn\'t have position property');
    }

    $this->getView()->setVars([
      'rows' => $model::fetchAll($this->getConditions(), $this->getSorting()),
      'header' => $this->getPositioning()
    ]);

    $this->getView()->setScript('table/position');
  }

  /**
   * @return array
   */
  protected function getConditions()
  {
    $conditions = [];

    foreach ($this->getFilterWithValues() as $filter) {

      if (empty($filter['value'])) {
        continue;
      }

      if ($filter['type'] == 'search') {

        if (count($filter['by']) > 1) {
          foreach ($filter['by'] as $field) {
            $conditions['$or'][] = [$field => new Regex(htmlspecialchars(quotemeta($filter['value'])), 'i')];
          }
        } else {
          $conditions[$filter['by'][0]] = new Regex(htmlspecialchars(quotemeta($filter['value'])), 'i');
        }

      } else if ($filter['type'] == 'bool') {

        if ($filter['value'] == 'true') {
          $conditions[$filter['by'] ?? 'id'] = true;

        } else if ($filter['value'] == 'false') {
          $conditions[$filter['by'] ?? 'id'] = false;
        }

      } else if ($filter['type'] == 'model') {
        $conditions[$filter['by'] ?? 'id'] = $filter['value'];

      } else if ($filter['type'] == 'dateTime') {
        $conditions[$filter['by']] = ['$gt' => strtotime($filter['value']['from']), '$lt' => strtotime($filter['value']['to'])];

      } else {
        $conditions[$filter['by']] = $filter['value'];
      }
    }

    return $conditions;
  }

  /**
   * @return array
   */
  protected function getFilterWithValues()
  {
    $filters = [];

    foreach ($this->getFilter() as $filter) {

      if ($filter['type'] == 'search') {
        $filter['value'] = $this->getParam('filter', [])[$filter['type']] ?? null;

      } else {
        $filter['value'] = $this->getParam('filter', [])[$filter['by']] ?? null;
      }

      $filters[] = $filter;
    }

    return $filters;
  }

  /**
   * @return array
   */
  protected function getFilter()
  {
    return $this->getCruds('filter');
  }

  /**
   * @return array
   */
  protected function getSorting()
  {
    $sorting = $this->getCruds('sorting');

    if (count($sorting)) {
      return $sorting[0];
    }

    $defaultSort = [];

    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $model = new $modelClassName();

    if ($model->getMeta()->hasProperty('position')) {
      $defaultSort = [
        'position' => 1
      ];
    }

    return array_merge($defaultSort, array_filter($this->getRequest()->getGet('sort', $defaultSort)));
  }

  /**
   * @return bool
   */
  protected function getPositioning()
  {
    return $this->getCruds('sortable');
  }

  /**
   * @return array
   * @throws Exception
   */
  public function setPosition()
  {
    $this->getView()->setLayoutEnabled(false);

    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    /** @var Model $model */
    $model = new $modelClassName();

    if (!$model->getMeta()->hasProperty('position')) {
      throw new Exception('Model doesn\'t have position property');
    }

    foreach ($this->getRequest()->getParam('items', []) as $index => $id) {

      $model = $modelClassName::fetchOne([
        'id' => $id
      ]);

      $model->position = $index;
      $model->save();
    }

    return [];
  }

  /**
   * @throws Exception
   */
  protected function copy()
  {
    $this->getView()->setLayoutEnabled(false);

    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $record = $modelClassName::fetchOne([
      'id' => $this->getParam('id')
    ]);

    if (!$record) {
      throw new Exception('Model was not found');
    }

    $data = [];

    foreach ($record->getMeta()->getProperties() as $property) {

      if ($property->getName() == 'id') {
        continue;
      }

      if (class_exists($property->getType())) {

        if ($field = $record->{$property->getName()}) {
          $data[$property->getName()] = $field->id;
        }
      } else {
        $data[$property->getName()] = $record->{$property->getName()};
      }
    }

    /** @var Model $newRecord */
    $newRecord = new $modelClassName();
    $newRecord->populate($data);
    $newRecord->save();
  }

  /**
   *
   */
  public function index()
  {
    $this->adminLog(\Light\Crud\AdminHistory\Model::TYPE_READ_TABLE);

    $this->getView()->setVars([
      'icon' => $this->getAdminMenuItem()['icon'] ?? null,
      'title' => $this->getTitle(),
      'button' => $this->getButton(),
      'positioning' => $this->getPositioning(),
      'positioningWithoutLanguage' => $this->positioningWithoutLanguage ?? false,
      'positioningCustom' => $this->positioningCustom ?? false,
      'export' => $this->getExportHeader() ?? false,

      'language' => $this->getRequest()->getGet('filter')['language'] ?? false,
      'filter' => $this->getFilterWithValues(),
      'header' => $this->getHeader(),
      'headerButtons' => $this->getHeaderButtons(),
      'controls' => $this->getControls(),
      'paginator' => $this->getPaginator(),
      'controller' => $this->getRouter()->getController(),
    ]);

    $this->getView()->setScript('table/index');
  }

  /**
   * @return string
   */
  protected function getTitle()
  {
    return $this->getCruds('title');
  }

  /**
   * @return string
   */
  protected function getButton(): bool
  {
    return (bool)$this->getCruds('manageable');
  }

  /**
   * @return array
   */
  protected function getHeaderButtons(): array
  {
    return $this->getCruds('header-button');
  }

  /**
   * @return array
   */
  protected function getHeader()
  {
    $headers = [];

    foreach ($this->getCruds('header') as $header) {
      $headers[$header['by']] = $header;
    }

    if (!count($headers)) {

      /** @var Model $modelClassName */
      $modelClassName = $this->getModelClassName();

      if (class_exists($modelClassName)) {

        /** @var Model $model */
        $model = new $modelClassName;

        if ($model->getMeta()->hasProperty('image')) {
          $headers['image'] = ['title' => 'Рис.', 'type' => 'image', 'static' => true];
        }
        if ($model->getMeta()->hasProperty('title')) {
          $headers['title'] = ['title' => 'Заголовок', 'static' => true];
        }
        if ($model->getMeta()->hasProperty('enabled')) {
          $headers['enabled'] = ['title' => 'Активность', 'type' => 'bool'];
        }
      }
    }
    return $headers;
  }

  /**
   * @return array
   */
  protected function getControls(): array
  {
    $controls = $this->getCruds('controls') ?? [];

    $controls[] = ['type' => 'edit'];

    $modelClassName = $this->getModelClassName();

    /** @var Model $model */
    $model = new $modelClassName();

    if ($model->getMeta()->hasProperty('enabled')) {
      $controls[] = ['type' => 'enabled'];
    }

    return $controls;
  }

  /**
   * @return Paginator
   */
  protected function getPaginator()
  {
    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $paginator = new Paginator(
      new $modelClassName(),
      $this->getConditions(),
      $this->getSorting()
    );

    $page = $this->getParam('page', '1');

    if (!strlen($page)) {
      $page = 1;
    }

    $paginator->setPage(intval($page));

    $paginator->setItemsPerPage(
      $this->getItemsPerPage()
    );

    return $paginator;
  }

  /**
   * @return int
   */
  protected function getItemsPerPage()
  {
    return 50;
  }

  /**
   *
   */
  public function select()
  {
    $this->getView()->setVars([

      'title' => $this->getTitle(),
      'language' => $this->getRequest()->getGet('filter')['language'] ?? false,
      'filter' => $this->getFilterWithValues(),
      'header' => $this->getHeader(),
      'isSelectControl' => true,
      'paginator' => $this->getPaginator(),
      'elementName' => $this->getParam('elementName'),
      'controller' => $this->getRouter()->getController(),
      'fields' => json_decode(base64_decode($this->getParam('fields')), true),
      'fieldsRaw' => $this->getParam('fields')
    ]);

    $this->getView()->setScript('table/modal');
  }

  /**
   * @return string
   */
  public function export()
  {
    ini_set('memory_limit', '-1');

    set_time_limit(0);

    $this->getView()->setLayoutEnabled(false);

    $response = [];

    $response[] = '"' . implode('","', array_keys($this->getExportHeader())) . '"';

    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $table = $modelClassName::fetchAll(
      $this->getConditions(),
      $this->getSorting()
    );

    foreach ($table as $row) {
      $cols = [];
      foreach ($this->getExportHeader() as $name => $field) {
        if (is_string($field)) {
          $cols[] = $row->{$field};
        } else {
          $cols[] = $field($row);
        }
      }
      $response[] = '"' . implode('","', $cols) . '"';
    }

    $response = implode(";\n", $response) . ';';

    $fileName = $this->getExportFileName() . '_' . date('c') . '.csv';
    $this->getResponse()->setHeader('Content-Disposition', 'attachment;filename=' . $fileName);
    $this->getResponse()->setHeader('Content-Size', (string)mb_strlen($response));

    return $response;
  }

  /**
   * @return array
   * @throws Exception
   */
  protected function getExportHeader(): array
  {
    return $this->getCruds('export')[0] ?? [];
  }

  /**
   * @return string
   */
  protected function getExportFileName()
  {
    $controllerClassPars = explode('\\', get_class($this));
    return end($controllerClassPars);
  }

  /**
   * @param string|null $id
   * @throws Exception\DomainMustBeProvided
   * @throws Exception\RouterVarMustBeProvided
   * @throws Exception\ValidatorClassWasNotFound
   */
  public function manage(string $id = null)
  {
    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $model = $modelClassName::fetchObject([
      'id' => $this->getRequest()->getParam('id')
    ]);

    /** @var Form $form */
    $form = $this->getForm($model);

    if ($this->getRequest()->isPost()) {

      if ($form->isValid($this->getRequest()->getPostAll())) {

        $formData = $form->getValues();

//        $this->adminLog(
//          $model->id
//            ? \Light\Crud\AdminHistory\Model::TYPE_WRITE_ENTITY
//            : \Light\Crud\AdminHistory\Model::TYPE_CREATE_ENTITY,
//          Map::execute($model->toArray(), array_combine(
//            array_keys($this->getHeader()),
//            array_keys($this->getHeader()),
//          )),
//          null,
//          $model->toArray(),
//          $formData
//        );

        $model->populate($formData);
        $isCreating = !!$model->id;
        $model->save();

        if ($isCreating) {
          $this->didChanged($model, $formData);
        } else {
          $this->didCreated($model, $formData);
        }

        $this->didSaved($model, $formData);

        die('ok:' . $this->getRequest()->getPost('return-url'));
      }
    }

//    if ($model->id) {
//      $this->adminLog(
//        \Light\Crud\AdminHistory\Model::TYPE_READ_ENTITY,
//        Map::execute($model, array_combine(
//          array_keys($this->getHeader()),
//          array_keys($this->getHeader()),
//        ))
//      );
//    }

    $form->setReturnUrl(
      $this->getRouter()->assemble([
        'controller' => $this->getRouter()->getController(),
        'action' => 'index'
      ])
    );

    $this->getView()->setVars([
      'title' => $this->getTitle(),
      'form' => $form,
    ]);

    $this->getView()->setScript('form/default');
  }

  /**
   * @param mixed|null $model
   * @return Form|null
   */
  protected function getForm($model = null)
  {
    /** @var Form $formClassName */
    $formClassName = $this->getFormClassName();

    if (class_exists($formClassName)) {
      return new $formClassName([
        'data' => $model
      ]);
    } else {
      return new Generator([
        'data' => $model
      ]);
    }

    return null;
  }

  /**
   * @return string
   */
  protected function getFormClassName()
  {
    $controllerClassPars = explode('\\', get_class($this));

    $controllerClassPars[count($controllerClassPars) - 2] = 'Form';

    return implode('\\', $controllerClassPars);
  }

  /**
   * @param ModelInterface $model
   * @param array $formData
   */
  protected function didCreated(ModelInterface $model, array $formData)
  {
  }

  /**
   * @param ModelInterface $model
   * @param array $formData
   */
  protected function didChanged(ModelInterface $model, array $formData)
  {
  }

  /**
   * @param ModelInterface $model
   * @param array $formData
   */
  protected function didSaved(ModelInterface $model, array $formData)
  {
  }

  /**
   * @return bool
   */
  public function setEnabled()
  {
    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $record = $modelClassName::fetchOne([
      'id' => $this->getRequest()->getGet('id')
    ]);

    $record->enabled = $this->getRequest()->getGet('enabled');
    $record->save();

    return true;
  }

  /**
   *
   */
  public function init()
  {
    parent::init();

    $this->getView()->setLayoutEnabled(
      !$this->getRequest()->isAjax()
    );

    $this->getView()->setLayoutTemplate('index');
    $this->getView()->setAutoRender(true);

    $this->getView()->setPath(__DIR__ . '/Crud');
  }

  /**
   * @param string $type
   * @param array $entity
   * @param string|null $section
   * @param array $was
   * @param array $became
   */
  protected function adminLog(string $type, array $entity = [], string $section = null, array $was = [], array $became = []): void
  {
    if (Front::getInstance()->getConfig()['light']['admin']['history'] ?? false) {

      if ($this instanceof \Light\Crud\AdminHistory\Controller) {
        return;
      }

      $section = $section ?? $this->title ?? null;
      if (!$section) {

        $controllerClassPars = explode('\\', get_class($this));
        $section = strtolower(end($controllerClassPars));

        foreach (Front::getInstance()->getConfig()['light']['admin']['menu'] ?? [] as $menu) {
          foreach ($menu['items'] as $subMenu) {
            if (isset($subMenu['url'])) {
              if (strtolower($subMenu['url']['controller']) == $section) {
                $section = $subMenu['title'];
              }
            }
          }
        }
      }

      $history = new \Light\Crud\AdminHistory\Model();
      $data = [
        'dateTime' => time(),
        'admin' => Auth::getInstance()->get(),
        'type' => $type,
        'section' => $section,
        'entity' => $entity,
        'was' => $was,
        'became' => $became
      ];
      $data['search'] = serialize($data);
      $history->populate($data);
      $history->save();
    }
  }

  /**
   * @return mixed|null
   */
  protected function getAdminMenuItem()
  {
    $controllerClassPars = explode('\\', get_class($this));
    $section = strtolower(end($controllerClassPars));

    foreach (Front::getInstance()->getConfig()['light']['admin']['menu'] ?? [] as $menu) {
      foreach ($menu['items'] as $subMenu) {
        if (strtolower($subMenu['url']['controller'] ?? '') == $section) {
          return $subMenu;
        }
      }
    }
    return null;
  }

  /**
   * @param string $type
   * @return array|string
   */
  protected function getCruds(string $type)
  {
    $reflection = new ReflectionClass(static::class);

    $cruds = array_values(array_map(function ($item) use ($type) {
      return trim(str_replace('@crud-' . $type . " ", '', $item));
    }, array_filter(
      explode("\n", str_replace('*', ' ', $reflection->getDocComment())),
      function ($item) use ($type) {
        return strstr($item, '@crud-' . $type . " ");
      }
    )));

    if ($type == 'title') {
      return $cruds[0];
    }

    if ($type == 'sortable') {
      return $cruds[0] ?? false;
    }

    foreach ($cruds as $index => $crud) {
      if ($crud = json_decode($crud, true)) {
        $cruds[$index] = $crud;
        continue;
      }
      throw new Exception("Crud with type: {$type} have not a valid JSON: {$crud}");
    }

    return $cruds;
  }
}
