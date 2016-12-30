<?php

namespace Drupal\dc_migrate\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for custom migration processors.
 */
interface DcMigrateProcessorInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Initialize the processor.
   */
  public function init();

  /**
   * Build the database query.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The query to fetch all data.
   */
  public function getQuery();

  /**
   * Build the database query for remaining items.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The query to fetch the unprocessed data.
   */
  public function getRemainingItemsQuery();

  /**
   * Run preparation tasks.
   *
   * @param array $options
   *   (optional) Additional options for the processor.
   */
  public function prepare($options = []);

  /**
   * Run the processor.
   *
   * @param int $limit
   *   (optional) The maximum number of items to process, or -1 to process all
   *   items.
   * @param array $options
   *   (optional) Additional options.
   */
  public function process($limit = -1, $options = []);

  /**
   * Run cleanup tasks.
   *
   * @param array $options
   *   (optional) Additional options.
   */
  public function cleanup($options = []);

  /**
   * Get the number of existing items
   *
   * @return int
   *   Total number of source items to process.
   */
  public function getExistingItemsCount();

  /**
   * Get number of remaining items to process.
   *
   * @return int
   *   Number of unprocessed items.
   */
  public function getRemainingItemsCount();

  /**
   * Get the mapping table schema for the processor.
   *
   * @return array|NULL
   *   Schema definition or NULL if no mapping table should be created.
   */
  public function getSchema();

  /**
   *
   * @return string
   *   The plugin description.
   */
  public function getDescription();
}
