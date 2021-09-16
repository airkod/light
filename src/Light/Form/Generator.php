<?php

declare(strict_types=1);

namespace Light\Form;

use Light\Filter\Lowercase;
use Light\Filter\Trim;
use Light\Form;
use Light\Form\Element\Checkbox;
use Light\Form\Element\Image;
use Light\Form\Element\Images;
use Light\Form\Element\Text;
use Light\Form\Element\Textarea;
use Light\Form\Element\Trumbowyg;
use Light\Form\Element\TrumbowygResponsive;
use Light\Model;

/**
 * Class Generator
 * @package Light\Form
 */
class Generator extends Form
{
  /**
   * @var Model
   */
  public $data = null;

  /**
   * @param null $model
   * @param array|Element\ElementAbstract[] $elements
   */
  public function init($model = null, array $elements = [])
  {
    $elements = array_merge_recursive(
      array_filter([

        'Общие настройки' => array_filter([
          $this->url(),
          $this->enabled(),
          $this->date(),
          $this->dateTime()
        ]),

        'Содержимое' => array_filter([
          $this->title(),
          $this->subTitle(),
          $this->description(),
          $this->image(),
          $this->images(),
          $this->contentString(),
          $this->contentArray(),
          $this->icon(),
        ]),

        'Button' => $this->button(),

        'Breadcrumbs' => $this->header(),

        'META settings' => $this->seo(),
      ]),
      $elements
    );

    parent::init($model, $elements);
  }

  /**
   * @param string $property
   * @return bool
   */
  public function modelHasProperty(string $property): bool
  {
    return $this->data->getMeta()->hasProperty($property);
  }

  /**
   * @return Text|null
   */
  public function icon(): ?Text
  {
    return !$this->modelHasProperty('icon') ? null :
      new Text('icon', [
        'value' => $this->data->icon,
        'label' => 'Icon class name',
        'filters' => [Trim::class, Lowercase::class],
        'allowNull' => true
      ]);
  }

  /**
   * @return Text|null
   */
  public function url(): ?Text
  {
    return !$this->modelHasProperty('url') ? null :
      new Text('url', [
        'value' => $this->data->url,
        'label' => 'URL',
        'filters' => [Trim::class, Lowercase::class],
        'allowNull' => true,
      ]);
  }

  /**
   * @return Checkbox|null
   */
  public function enabled(): ?Checkbox
  {
    return !$this->modelHasProperty('enabled') ? null :
      new Checkbox('enabled', [
        'value' => $this->data->enabled,
        'label' => 'Активность'
      ]);
  }

  /**
   * @return Form\Element\Date|null
   */
  public function date(): ?Form\Element\Date
  {
    return !$this->modelHasProperty('date') ? null :
      new Form\Element\Date('date', [
        'value' => $this->data->date,
        'label' => 'Дата',
        'allowNull' => true
      ]);
  }

  /**
   * @return Form\Element\DateTime|null
   */
  public function dateTime(): ?Form\Element\DateTime
  {
    return !$this->modelHasProperty('dateTime') ? null :
      new Form\Element\DateTime('dateTime', [
        'value' => $this->data->dateTime,
        'label' => 'Дата/Время',
        'allowNull' => true
      ]);
  }

  /**
   * @return Text|null
   */
  public function title(): ?Text
  {
    return !$this->modelHasProperty('title') ? null :
      new Text('title', [
        'value' => $this->data->title,
        'label' => 'Заголовок',
        'filters' => [Trim::class],
        'allowNull' => false
      ]);
  }

  /**
   * @return Text|null
   */
  public function subTitle(): ?Text
  {
    return !$this->modelHasProperty('subTitle') ? null :
      new Text('subTitle', [
        'value' => $this->data->subTitle,
        'label' => 'Подзаголовок',
        'filters' => [Trim::class],
        'allowNull' => true
      ]);
  }

  /**
   * @return Textarea|null
   */
  public function description(): ?Textarea
  {
    return !$this->modelHasProperty('description') ? null :
      new Textarea('description', [
        'value' => $this->data->description,
        'label' => 'Описание',
        'allowNull' => true,
        'filters' => [Trim::class],
      ]);
  }

  /**
   * @return Image|null
   */
  public function image(): ?Image
  {
    return !$this->modelHasProperty('image') ? null :
      new Image('image', [
        'value' => $this->data->image,
        'label' => 'Изображение',
        'allowNull' => true
      ]);
  }

  /**
   * @return Images|null
   */
  public function images(): ?Images
  {
    return !$this->modelHasProperty('images') ? null :
      new Images('images', [
        'value' => $this->data->images,
        'label' => 'Изображения'
      ]);
  }

  /**
   * @return Trumbowyg|null
   */
  public function contentString(): ?Trumbowyg
  {
    return (
      $this->modelHasProperty('content') &&
      $this->data->getMeta()->getPropertyWithName('content')->getType() == 'string')
      ? new Trumbowyg('content', [
        'value' => $this->data->content,
        'label' => 'Контент'
      ])
      : null;
  }

  /**
   * @return TrumbowygResponsive|null
   */
  public function contentArray(): ?TrumbowygResponsive
  {
    return (
      $this->modelHasProperty('content') &&
      $this->data->getMeta()->getPropertyWithName('content')->getType() == 'array'
    )
      ? new TrumbowygResponsive('content', [
        'value' => $this->data->content,
        'label' => 'Контент'
      ])
      : null;
  }

  /**
   * @return Text[]|null
   */
  public function button(): ?iterable
  {
    return !$this->modelHasProperty('buttonText') ? null :
      [
        new Text('buttonText', [
          'value' => $this->data->buttonText,
          'label' => 'Button title'
        ]),

        new Text('buttonLink', [
          'value' => $this->data->buttonLink,
          'label' => 'Button link'
        ]),
      ];
  }

  /**
   * @return Form\Element\ElementAbstract[]|null
   */
  public function header(): ?iterable
  {
    return !$this->modelHasProperty('headerTitle') ? null :
      [
        new Text('headerTitle', [
          'value' => $this->data->headerTitle,
          'label' => 'Title'
        ]),

        new Text('headerBreadcrumb', [
          'value' => $this->data->headerBreadcrumb,
          'label' => 'Breadcrumbs'
        ]),

        new Image('headerImage', [
          'value' => $this->data->headerImage,
          'label' => 'Image',
          'allowNull' => true
        ]),
      ];
  }

  /**
   * @return Form\Element\ElementAbstract[]|null
   */
  public function seo(): ?iterable
  {
    return !$this->modelHasProperty('metaTitle') ? null :
      [
        new Text('metaTitle', [
          'value' => $this->data->metaTitle,
          'label' => 'META Title',
          'allowNull' => true
        ]),

        new Textarea('metaDescription', [
          'value' => $this->data->metaDescription,
          'label' => 'META Description',
          'allowNull' => true
        ]),

        new Text('metaKeywords', [
          'value' => $this->data->metaKeywords,
          'label' => 'META Keywords',
          'allowNull' => true
        ]),

        new Text('ogTitle', [
          'value' => $this->data->ogTitle,
          'label' => 'OG Title',
          'allowNull' => true
        ]),

        new Textarea('ogDescription', [
          'value' => $this->data->ogDescription,
          'label' => 'OD Description',
          'allowNull' => true
        ]),

        new Image('ogImage', [
          'value' => $this->data->ogImage,
          'label' => 'OG Image',
          'allowNull' => true
        ]),
      ];
  }
}
