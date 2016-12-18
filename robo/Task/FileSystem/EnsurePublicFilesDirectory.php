<?php

namespace DrupalCenter\Robo\Task\FileSystem;

use DrupalCenter\Robo\Utility\PathResolver;

/**
 * Robo task: Ensure public files directory.
 */
class EnsurePublicFilesDirectory extends EnsureDirectorySkippedIfAcquia {

  /**
   * {@inheritdoc}
   */
  protected function getPath() {
    return PathResolver::publicFilesDirectory();
  }

}
