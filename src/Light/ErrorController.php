<?php

declare(strict_types=1);

namespace Light;

/**
 * Class ErrorController
 * @package Light
 */
class ErrorController extends Controller
{
  /**
   * @var \Exception|\Error
   */
  private $_exception = null;

  /**
   * @var bool
   */
  private $_exceptionEnabled = true;

  /**
   * @return bool
   */
  public function isExceptionEnabled(): bool
  {
    return $this->_exceptionEnabled;
  }

  /**
   * @param bool $exceptionEnabled
   */
  public function setExceptionEnabled(bool $exceptionEnabled): void
  {
    $this->_exceptionEnabled = $exceptionEnabled;
  }

  /**
   * Setup status code and message
   */
  public function init()
  {
    parent::init();

    $this->getResponse()->setStatusCode(
      $this->getException()->getCode()
    );

    $this->getResponse()->setStatusMessage(
      $this->getException()->getMessage()
    );
  }

  /**
   * @return \Exception|\Error
   */
  public function getException()
  {
    return $this->_exception;
  }

  /**
   * @param \Exception|\Error $exception
   */
  public function setException($exception)
  {
    $this->_exception = $exception;
  }
}
