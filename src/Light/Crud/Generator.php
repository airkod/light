<?php

declare(strict_types=1);

namespace Light\Crud;

/**
 * Class Generator
 * @package Light\Crud
 */
class Generator
{
  /**
   * @var array
   */
  public $config = [];

  /**
   * @var string
   */
  public $name = null;

  /**
   * @var string
   */
  public $type = null;

  /**
   * Generator constructor.
   * @param array $config
   * @param string $name
   * @param string $type
   */
  public function __construct(array $config, string $name, string $type)
  {
    if (!in_array($type, ['full', 'single'])) {
      throw new \Exception('Unsupported model type - ' . $type);
    }

    $this->config = $config;

    $this->name = $name;
    $this->type = $type;
  }

  /**
   * @return int
   */
  public function model()
  {
    $namespace = $this->config['light']['loader']['namespace'] . "\\Model";
    $name = $this->name;

    return $this->_generate($namespace, $name, 'model', 'Model');
  }

  /**
   * @return int
   */
  public function controller()
  {
    $namespace = $this->config['light']['loader']['namespace'] . '\Module\Admin\Controller';
    $name = $this->name;

    return $this->_generate($namespace, $name, 'controller', 'Module/Admin/Controller');
  }

  /**
   * @param string $namespace
   * @param string $name
   * @param string $subject
   * @param string $dest
   *
   * @return int
   * @throws \Exception
   */
  private function _generate(string $namespace, string $name, string $subject, string $dest): int
  {
    $dir = __DIR__;

    $destFile = "{$this->config['light']['loader']['path']}/{$dest}/{$name}.php";

    $template = str_replace(
      ['{namespace}', '{name}'],
      [$namespace, $name],
      file_get_contents("{$dir}/Generator/{$this->type}/{$subject}.tpl")
    );

    try {
      return file_put_contents($destFile, $template);
    } catch (\Exception $e) {
      throw new \Exception("Cant write a {$subject}. Because of {$e->getMessage()}");
    }
  }
}
