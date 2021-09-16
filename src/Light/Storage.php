<?php

declare(strict_types=1);

namespace Light;

/**
 * Class Storage
 * @package Light
 */
class Storage
{
  /**
   * @var array
   */
  private static $_settings = null;

  /**
   * @return array
   */
  public static function getSettings(): ?array
  {
    return self::$_settings;
  }

  /**
   * @param array $settings
   */
  public static function setSettings(?array $settings): void
  {
    self::$_settings = $settings;
  }

  /**
   * @param string $path
   * @param string $folder
   * @param array $files
   *
   * @return array
   * @throws \Exception
   */
  public static function upload(string $path, string $folder, array $files): array
  {
    if (!self::$_settings) {
      throw new \Exception('Storage settings is not defined');
    }

    try {

      $data = [
        'files' => [],
        'path' => $path,
        'folder' => $folder,
      ];

      foreach ($files as $file) {

        try {
          $mime = mime_content_type($file);

        } catch (\Exception $e) {
          $fileParts = explode('.', $file);
          $ext = end($fileParts);
          if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
            $mime = 'image/' . $ext;
          } else {
            $mime = 'application/' . $ext;
          }
        }

        $data['files'][] = [
          'content' => base64_encode(file_get_contents($file)),
          'mime' => $mime
        ];
      }

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::$_settings['url'] . 'api/uploadFileBase64');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

      $result = curl_exec($ch);
      curl_close($ch);

      $result = json_decode($result, true);

      if (isset($result['error'])) {
        throw new \Exception($result['message'] ?? 'Storage response error');
      }

      return $result;

    } catch (\Exception $e) {
      throw new \Exception('Cannot upload files. Reason: ' . $e->getMessage());
    }
  }

  /**
   * @param string $image
   * @param int|null $width
   * @param int|null $height
   */
  public static function resize(string $image, int $width, int $height = null)
  {
    $_image = explode('.', $image);
    $ext = $_image[count($_image) - 1];
    if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
      unset($_image[count($_image) - 1]);
      return implode('.', $_image) . '_r_' . $width . '.' . $ext;
    }
    return $image;
  }
}
