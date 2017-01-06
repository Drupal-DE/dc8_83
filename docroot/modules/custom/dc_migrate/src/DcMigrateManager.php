<?php

namespace Drupal\dc_migrate;

use Drupal\dc_migrate\DcMigrateProcessorPluginManager;

/**
 * Defines a migrate manager.
 */
class DcMigrateManager {

  /**
   * Processor plugin manager service object.
   *
   * @var \Drupal\dc_migrate\DcMigrateProcessorPluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a manager object.
   */
  public function __construct(DcMigrateProcessorPluginManager $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * Load a processor plugin.
   *
   * @param string $processor_id
   *   Plugin ID of processor to load.
   *
   * @return \Drupal\dc_migrate\Plugin\DcMigrateProcessorInterface|null
   *   The loaded processor.
   */
  public function load($processor_id) {
    return $this->pluginManager->getProcessor($processor_id);
  }

}
