<?php

namespace Drupal\expand_style\Plugin\ImageToolkit\Operation\gd;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\GDImageToolkitOperationBase;

/**
 * Expand GD2 Toolkit Operation
 *
 * @author Attila Németh, UBG
 * @date 26.10.2021
 * 
 * @ImageToolkitOperation(
 *   id = "expand",
 *   toolkit = "gd",
 *   operation = "expand",
 *   label = @Translation("Expand"),
 *   description = @Translation("Expands an image to a rectangle specified by the given dimensions.")
 * )
 */
class Expand extends GDImageToolkitOperationBase {
  
  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'width' => [
        'description' => 'The width of the full area, in pixels',
        'required' => TRUE,
      ],
      'height' => [
        'description' => 'The height of the full area, in pixels',
        'required' => TRUE,
      ],
      'background' => [
        'description' => 'Beackground color',
        'required' => FALSE,
        'default' => '#ffffffff',
      ],
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    if (empty($arguments['width']) && empty($arguments['height'])) {
      throw new \InvalidArgumentException("Width and Height must be provided");
    }
    if (!is_numeric($arguments['width'])) {
      throw new \InvalidArgumentException("Invalid width ('{$arguments['width']}') specified for the image 'expand' operation");
    }
    if (!is_numeric($arguments['height'])) {
      throw new \InvalidArgumentException("Invalid height ('{$arguments['height']}') specified for the image 'expand' operation");
    }
    if ($arguments['width'] <= 0) {
      throw new \InvalidArgumentException("Invalid width ('{$arguments['width']}') specified for the image 'expand' operation");
    }
    if ($arguments['height'] <= 0) {
      throw new \InvalidArgumentException("Invalid height ('{$arguments['height']}') specified for the image 'expand' operation");
    }
    if (!empty($arguments['background'])) {
      $bg = preg_replace('/^\#/', '', $arguments['background']);
      if (strlen($bg) !== 6 && strlen($bg) !== 8) {
        throw new \InvalidArgumentException("Invalid background ('{$arguments['background']}') specified for the image 'expand' operation");
      }
    }
    return $arguments;
  }
  
  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    $resource = $this->getToolkit()->getResource();
    $new = imagecreatetruecolor($arguments['width'], $arguments['height']);
    if (empty($arguments['background'])) {
      $bgColor = 'ffffffff';
    }
    else {
      $bgColor = preg_replace('/^\#/', '', $arguments['background']);
    }
    if (strlen($bgColor) === 6) {
      $bg = imagecolorallocate($new, hexdec(substr($bgColor, 0, 2)), hexdec(substr($bgColor, 2, 2)), hexdec(substr($bgColor, 4, 2)));
    }
    elseif (strlen($bgColor) === 8) {
      $bg = imagecolorallocatealpha($new, 
            hexdec(substr($bgColor, 0, 2)), 
            hexdec(substr($bgColor, 2, 2)), 
            hexdec(substr($bgColor, 4, 2)),
            hexdec(substr($bgColor, 6, 2)) / 2);
    }
    imagefill($new, 0, 0, $bg);
    $aspectWidth = $arguments['width'] / imagesx($resource);
    $aspectHeight = $arguments['height'] / imagesy($resource);
    if ($aspectHeight < $aspectWidth) {
      $aspect = $aspectHeight;
    }
    else {
      $aspect = $aspectWidth;
    }
    $newWidth = imagesx($resource) * $aspect;
    $newHeight = imagesy($resource) * $aspect;
    $newX = ($arguments['width'] - $newWidth) / 2;
    $newY = ($arguments['height'] - $newHeight) / 2;
    imagecopyresampled($new, $resource, $newX, $newY, 0, 0, $newWidth, $newHeight, imagesx($resource), imagesy($resource));
    $this->getToolkit()->setResource($new);
    imagedestroy($resource);
    return TRUE;
  }
  
}
