<?php

namespace DrupalCenter\Robo\Task\Site;

use Robo\Task\BaseTask;
use DrupalCenter\Robo\Task\Drush\StateSet;

/**
 * Robo task base: Enable/disable maintenance mode.
 */
class MaintenanceMode extends BaseTask {

  /**
   * Status.
   *
   * @var bool
   */
  protected $status;

  /**
   * Constructor.
   *
   * @param bool $status
   *   Whether to enable/disable maintenance mode.
   */
  public function __construct($status) {
    $this->status = $status;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    return (new StateSet('system.maintenance_mode', $this->status ? 1 : 0, 'integer'))
      ->run();
  }

}
