<?php

namespace DrupalCenter\Robo\Task\Site;

trait loadTasks {

  /**
   * Initialize site.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return Initialize
   */
  protected function taskSiteInitialize($environment) {
    return new Initialize($environment);
  }

  /**
   * Install site.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return Install
   */
  protected function taskSiteInstall($environment) {
    return new Install($environment);
  }

  /**
   * Enable/disable maintenance mode.
   *
   * @param bool $status
   *   Whether to enable or disable maintenance mode.
   *
   * @return MaintenanceMode
   */
  protected function taskSiteMaintenanceMode($status) {
    return new MaintenanceMode($status);
  }

  /**
   * Set up file system.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return SetupFileSystem
   */
  protected function taskSiteSetupFileSystem($environment) {
    return new SetupFileSystem($environment);
  }

  /**
   * Update site.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return Update
   */
  protected function taskSiteUpdate($environment) {
    return new Update($environment);
  }

}
