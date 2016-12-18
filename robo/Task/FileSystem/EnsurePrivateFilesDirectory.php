<?php

namespace DrupalCenter\Robo\Task\FileSystem;

use DrupalCenter\Robo\Utility\PathResolver;

/**
 * Robo task: Ensure private files directory.
 */
class EnsurePrivateFilesDirectory extends EnsureDirectorySkippedIfAcquia {

  /**
   * {@inheritdoc}
   */
  protected function getPath() {
    return PathResolver::privateFilesDirectory();
  }

  /**
   * {@inheritdoc}
   */
  protected function skip() {
    return parent::skip() || !$this->getPath();
  }

}
