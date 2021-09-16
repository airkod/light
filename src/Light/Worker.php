<?php

declare(strict_types=1);

namespace Light;

/**
 * Class Worker
 * @package Light
 */
abstract class Worker extends Controller
{
  /**
   * @param string $name
   * @param array $arguments
   * @throws Exception
   */
  public function __call($name, $arguments)
  {
    $arguments = $this->getParams();

    $this->didStarted($name, $arguments);

    if (!method_exists($this, $name)) {
      throw new \Exception("Method {$name} was not found");
    }
    while (call_user_func_array([$this, $name], $arguments) !== false) {
      sleep(Front::getInstance()->getConfig()['light']['worker']['sleep'] ?? 1);
    }
    $this->didFinished($name, $arguments);
  }

  /**
   * @param string $method
   * @param array $params
   */
  public function didStarted(string $method, array $params = [])
  {
  }

  /**
   * @param string $method
   * @param array $params
   */
  public function didFinished(string $method, array $params = [])
  {
  }
}
