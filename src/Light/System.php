<?php

namespace Light;

use Light\Exception\SystemProcNotReadable;

/**
 * Class System
 * @package Light
 */
class System
{
  /**
   * @param bool $format
   *
   * @return int|string
   * @throws SystemProcNotReadable
   */
  public static function uptime(bool $format = false)
  {
    $uptime = explode(' ', self::_readProc('uptime'));
    $uptime = intval(trim($uptime[0]));

    if (!$format) {
      return $uptime;
    }

    $create_time = 0;
    $current_time = $uptime;

    $dtCurrent = \DateTime::createFromFormat('U', (string)$current_time);
    $dtCreate = \DateTime::createFromFormat('U', (string)$create_time);
    $diff = $dtCurrent->diff($dtCreate);

    $interval = $diff->format("%y years %m months %d days %h hours %i minutes %s seconds");
    $interval = preg_replace('/(^0| 0) (years|months|days|hours|minutes|seconds)/', '', $interval);

    return trim($interval);
  }

  /**
   * @return array
   */
  public static function disk()
  {
    return [
      'total' => number_format(disk_total_space('/') / 1024 / 1024 / 1024, 2),
      'free' => number_format(disk_free_space('/') / 1024 / 1024 / 1024, 2),
    ];
  }

  /**
   * @return array
   * @throws SystemProcNotReadable
   */
  public static function memory()
  {
    $memInfo = [];
    $procMemInfo = self::_readProc('meminfo');

    foreach (explode("\n", $procMemInfo) as $line) {
      try {
        $line = explode(':', $line);
        $key = trim($line[0]);
        $value = number_format(intval(array_values(array_filter(explode(' ', $line[1])))[0]) / 1024 / 1024, 2);
        $memInfo[$key] = $value;
      } catch (\Exception $e) {
      }
    }
    return $memInfo;
  }

  /**
   * @return string|null
   * @throws SystemProcNotReadable
   */
  public static function cpuLoadAverage()
  {
    $statData1 = self::_getServerLoadLinuxData();
    sleep(1);
    $statData2 = self::_getServerLoadLinuxData();

    if ($statData1 && $statData2) {

      $statData2[0] -= $statData1[0];
      $statData2[1] -= $statData1[1];
      $statData2[2] -= $statData1[2];
      $statData2[3] -= $statData1[3];

      $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];
      $load = 100 - ($statData2[3] * 100 / $cpuTime);

      return $load;
    }
    return 0;
  }

  /**
   * @return int
   * @throws SystemProcNotReadable
   */
  public static function cpuCoreCount()
  {
    return substr_count(self::_readProc('cpuinfo'), 'processor');
  }

  /**
   * @return string
   * @throws SystemProcNotReadable
   */
  public static function cpuName()
  {
    $procCpuInfo = self::_readProc('cpuinfo');
    $cpuInfo = [];

    foreach (explode("\n", $procCpuInfo) as $line) {
      try {
        $line = explode(':', $line);
        $key = trim($line[0]);
        $cpuInfo[$key] = trim($line[1]);
      } catch (\Exception $e) {
      }
    }

    return implode(' ', [
      $cpuInfo['vendor_id'],
      $cpuInfo['model name'],
    ]);
  }

  /**
   * @return string
   * @throws SystemProcNotReadable
   */
  public static function version()
  {
    return self::_readProc('version');
  }

  /**
   * @return array|null
   * @throws SystemProcNotReadable
   */
  private static function _getServerLoadLinuxData()
  {
    $stats = preg_replace("/[[:blank:]]+/", " ", self::_readProc('stat'));

    $stats = str_replace(["\r\n", "\n\r", "\r"], "\n", $stats);
    $stats = explode("\n", $stats);

    foreach ($stats as $statLine) {
      $statLineData = explode(" ", trim($statLine));
      if ((count($statLineData) >= 5) && ($statLineData[0] == "cpu")) {
        return [
          $statLineData[1],
          $statLineData[2],
          $statLineData[3],
          $statLineData[4],
        ];
      }
    }
    return null;
  }

  /**
   * @param string $proc
   *
   * @return string
   * @throws SystemProcNotReadable
   */
  private static function _readProc(string $proc)
  {
    try {
      return file_get_contents('/proc/' . $proc);
    } catch (Exception $e) {
      throw new SystemProcNotReadable($proc);
    }
  }
}
