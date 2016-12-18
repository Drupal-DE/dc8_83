<?php

namespace DrupalCenter\Robo\Task\DatabaseDump;

use DrupalCenter\Robo\Utility\Drush;

/**
 * Robo task: Import database dump.
 */
class Import extends Dump {

  /**
   * {@inheritdoc}
   */
  public function run() {
    return Drush::exec()
      ->arg('sql-cli')
      ->arg('<')
      ->arg(escapeshellarg($this->filepath))
      ->run();
  }

}
