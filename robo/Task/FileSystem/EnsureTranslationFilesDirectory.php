<?php

namespace DrupalCenter\Robo\Task\FileSystem;

use DrupalCenter\Robo\Utility\PathResolver;

/**
 * Robo task: Ensure translation files directory.
 */
class EnsureTranslationFilesDirectory extends EnsureDirectory {

  /**
   * {@inheritdoc}
   */
  protected function getPath() {
    return PathResolver::translationFilesDirectory();
  }

  /**
   * {@inheritdoc}
   */
  protected function skip() {
    return parent::skip() || !PathResolver::translationFilesDirectory();
  }

}
