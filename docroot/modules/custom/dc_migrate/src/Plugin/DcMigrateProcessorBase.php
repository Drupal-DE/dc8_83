<?php

namespace Drupal\dc_migrate\Plugin;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Plugin\PluginBase;
use Drupal\dc_migrate\Database\Query\InsertIgnore;
use Drupal\dc_migrate\Plugin\DcMigrateProcessorInterface;

/**
 * Basic process class for custom migrations.
 */
abstract class DcMigrateProcessorBase extends PluginBase implements DcMigrateProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
    $this->init();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
        $this->defaultConfiguration(), $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'map_table' => NULL,
      'settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $configuration = $this->getConfiguration();
    if (empty($configuration['map_table'])) {
      return;
    }
    $database = \Drupal::database();

    if (!$database->schema()->tableExists($configuration['map_table']) && ($schema = $this->getSchema()) !== NULL) {
      $database->schema()->createTable($configuration['map_table'], $schema);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingItemsCount() {
    $count_query = $this->getQuery()
      ->countQuery();
    return $count_query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsCount() {
    return $this->getRemainingItemsQuery()
        ->countQuery()
        ->execute()
        ->fetchField();
  }

  /**
   * Return a new InsertIgnore query.
   *
   * @param string $table
   *   Name of table.
   *
   * @return InsertIgnore
   */
  public function insertIgnore($table) {
    $database = \Drupal::database();
    return new InsertIgnore($database, $table);
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($options = []) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanup($options = []) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'] ?: '- none -';
  }

}
