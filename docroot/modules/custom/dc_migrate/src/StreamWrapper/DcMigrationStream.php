<?php

namespace Drupal\dc_migrate\StreamWrapper;

use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\LocalReadOnlyStream;

/**
 * Simple read-only stream wrapper class for migration files.
 */
class DcMigrationStream extends LocalReadOnlyStream {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('DrupalCenter migration files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Migration files for the daskochrezept.de.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  /**
   * Implements Drupal\Core\StreamWrapper\StreamWrapperInterface::getExternalUrl().
   *
   * @throws \LogicException PO files URL should not be public.
   */
  function getExternalUrl() {
    throw new \LogicException('Migration file URLs are not meant to be public.');
  }

  /**
   * Returns the base path for dcmigration://.
   *
   * Note that this static method is used by \Drupal\system\Form\FileSystemForm
   * so you should alter that form or substitute a different form if you change
   * the class providing the stream_wrapper.private service.
   *
   * @return string
   *   The base path for dcmigration://.
   */
  public static function basePath() {
    return Settings::get('file_dc_migration_path');
  }


}
