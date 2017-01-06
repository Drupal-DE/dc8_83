<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Base Sql source for DKR migrations.
 */
abstract class DcSqlBase extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state) {
    if (empty($configuration['key']) && empty($configuration['target']) && empty($configuration['database_state_key'])) {
      // Try to load database information from site state.
      if (($database_config = $state->get('dc_migrate.database')) === NULL) {
        throw new MigrateException('You should either define a database connection in the migration or set the connection info in the appropriate configuration form.');
      }
      // Add default configuration.
      $database_config = array_filter($database_config) + [
        'host' => 'localhost',
        'port' => '3306',
        'username' => '',
        'password' => '',
        'driver' => 'mysql',
        'namespace' => 'Drupal\Core\Database\Driver\mysql',
        'init_commands' => [
          // Use custom sql_mode so we disable "ONLY_FULL_GROUP_BY".
          'sql_mode' => "SET sql_mode = 'ANSI,STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER'",
        ],
      ];
      $configuration['database'] = $database_config;
    }

    // Now we can safely call the parent constructor.
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);
  }

  /**
   * Alter the base query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The query to alter.
   */
  protected function alterQuery(SelectInterface $query) {

  }

  /**
   * Alter the list of available fields.
   *
   * @param array $fields
   *   List of fields available for migration.
   */
  protected function alterFields(array &$fields = []) {

  }

  /**
   * Get configuration values for the current migration. Configuration made in
   * the source plugin definition may be overriden by the migration itself.
   *
   * @param string $name
   *   The configuration name.
   * @param mixed $default
   *   Default value.
   *
   * @return mixed
   *   The configuration value or $default if there is no configuration with
   *   that name.
   */
  public function getConfig($name, $default = NULL) {
    $plugin_definition = $this->pluginDefinition;
    $source_configuration = $this->migration->getSourceConfiguration();

    $value = $default;
    if (isset($plugin_definition[$name])) {
      // Empty value is allowed.
      $value = $plugin_definition[$name];
    }
    if (isset($source_configuration[$name])) {
      // Empty value is allowed.
      $value = is_array($value) ? array_merge($value, $source_configuration[$name]) : $source_configuration[$name];
    }

    return $value;
  }

}
