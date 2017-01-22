<?php

/**
 * @file
 * Contains \Drupal\dc_placeholder_image\Utility\PlaceholderImage.
 */

namespace Drupal\dc_placeholder_image\Utility;

use Drupal\file\Entity\File;
use Drupal\media_entity\Entity\Media;

/**
 * Service class for creating a placeholder image generation.
 */
class PlaceholderImage implements PlaceholderImageInterface   {

  /**
   * {@inheritdoc}
   */
  public function create($directory , $filename, $type = "file") {

    $destination = $directory . '/' . $filename;
    // Check if file object for placeholder image already exists.
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $destination]);
    // Processing for type media.
    if ($type == 'media') {
      $files = \Drupal::entityTypeManager()
        ->getStorage('media')
        ->loadByProperties(['name' => $filename]);
    }
    // Return loaded file object.
    if (!empty($files)) {
      return reset($files);
    }

    // No file object found - build file object / return it.
    try {
      /** @var FileSystemthunder_media.module $filesystem */
      $filesystem = \Drupal::service('file_system');
      // Create file entity.
      $image = File::create();
      $image->setFileUri($destination);
      $image->setOwnerId(\Drupal::currentUser()->id());
      $image->setMimeType('image/' . pathinfo($destination, PATHINFO_EXTENSION));
      $image->setFileName($filesystem->basename($destination));
      $image->setPermanent();
      $image->save();
      // For simple file entities we just return file id
      // In case of media we save media entity and return media_entity value.
      if ($type != 'media') {
        return $image->id();
      }
      // Create media entity with saved file.
      $image_media = Media::create([
        'bundle' => 'image',
        'uid' => \Drupal::currentUser()->id(),
        'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
        'status' => Media::PUBLISHED,
        'field_image' => [
          'target_id' => $image->id(),
          'alt' => t('Placeholder image'),
          'title' => t('Placeholder image'),
        ],
      ]);
      $image_media->save();
      return $image_media->id();
    } catch (\Exception $e) {

    }
  }

}