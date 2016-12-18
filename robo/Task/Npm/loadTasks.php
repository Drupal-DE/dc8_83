<?php

namespace DrupalCenter\Robo\Task\Npm;

trait loadTasks {

  /**
   * 'npm run' command.
   *
   * @param string|null $pathToNpm
   *   Optional path to 'npm' binary'.
   *
   * @return Run
   */
  protected function taskNpmRun($pathToNpm = NULL) {
    return new Run($pathToNpm);
  }

}
