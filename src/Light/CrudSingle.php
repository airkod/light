<?php

declare(strict_types=1);

namespace Light;

/**
 * Class CrudSingle
 * @package Light
 */
abstract class CrudSingle extends Crud
{
  /**
   * @throws Exception\DomainMustBeProvided
   * @throws Exception\RouterVarMustBeProvided
   * @throws Exception\ValidatorClassWasNotFound
   */
  public function index()
  {
    /** @var Model $modelClassName */
    $modelClassName = $this->getModelClassName();

    $formClassName = $this->getFormClassName();

    $model = $modelClassName::fetchObject();

    /** @var Form $form */
    $form = $this->getForm($model);

    if ($this->getRequest()->isPost()) {

      if ($form->isValid($this->getRequest()->getPostAll())) {

        $formData = $form->getValues();

        $this->adminLog(
          $model->id
            ? \Light\Crud\AdminHistory\Model::TYPE_WRITE_ENTITY
            : \Light\Crud\AdminHistory\Model::TYPE_CREATE_ENTITY,
          Map::execute($model->toArray(), array_combine(
            array_keys($this->getHeader()),
            array_keys($this->getHeader()),
          )),
          null,
          $model->toArray(),
          $formData
        );

        $model->populateWithoutQuerying($formData);
        $isCreating = !!$model->id;
        $model->save();

        if ($isCreating) {
          $this->didChanged($model, $formData);
        } else {
          $this->didCreated($model, $formData);
        }

        $this->didSaved($model, $formData);
      }
    }

    $this->adminLog(
      \Light\Crud\AdminHistory\Model::TYPE_READ_ENTITY,
      Map::execute($model->toArray(), array_combine(
        array_keys($this->getHeader()),
        array_keys($this->getHeader()),
      ))
    );

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
}
