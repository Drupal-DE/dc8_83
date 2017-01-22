<?php

/**
 * @file
 * Contains \Drupal\dc_placeholder_image\Utility\PlaceholderImageInterface.
 */

namespace Drupal\dc_placeholder_image\Utility;

/**
 * Interface for placeholder image generation service classes.
 */
interface PlaceholderImageInterface {

  /**
   * Create a placeholder image.
   *
   * @param string $directory
   *   Directory to save placeholder images
   * @param string $filename
   *   Name of placeholder image.
   *
   * @param string $type
   *   Type can be file or image.
   *
   * @return FileInterface
   *   The file entity of the placeholder image.
   */
  public function create($directory , $filename, $type);
}
