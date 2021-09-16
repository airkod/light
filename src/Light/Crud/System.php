<?php

declare(strict_types=1);

namespace Light\Crud;

use Light\Crud;

/**
 * Class System
 * @package Light\Crud
 */
class System extends Crud
{
  /**
   * @throws \Light\Exception\SystemProcNotReadable
   */
  public function index()
  {
    $this->adminLog(\Light\Crud\AdminHistory\Model::TYPE_READ_ENTITY, [], 'Мониторинг');

    try {
      $this->getView()->setVars([
        'disk' => \Light\System::disk(),
        'memory' => \Light\System::memory(),
        'uptime' => \Light\System::uptime(true),
        'version' => \Light\System::version(),
        'cpuLoadAverage' => \Light\System::cpuLoadAverage(),
        'cpuCoreCount' => \Light\System::cpuCoreCount(),
        'cpuName' => \Light\System::cpuName(),
      ]);
    } catch (\Exception $e) {
    }
    $this->getView()->setScript('system');
  }
}
