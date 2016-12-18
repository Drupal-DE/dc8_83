<?php

namespace DrupalCenter\Robo\Task\Drush;

use Robo\Result;
use DrupalCenter\Robo\Utility\Environment;

/**
 * Robo task: Update localizations.
 */
class LocaleUpdate extends DrushTask {

  /**
   * {@inheritdoc}
   */
  public function run() {
    if (!$this->skip()) {
      return $this->exec()
        ->arg('locale-update')
        ->run();
    }

    return Result::success($this);
  }

  /**
   * Task should be skipped?
   *
   * @return bool
   *   Whether the task should be skipped or not?
   */
  protected function skip() {
    // Only update translations outside of Acquia environments.
    // This is currently needed, because the installed Drush version at Acquia
    // does not support this command yet.
    // FIXME Make this command workin in Acquia environments.
    return Environment::isAcquia(Environment::detect());
  }

}
