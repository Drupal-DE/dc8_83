<?php

namespace Drupal\dc_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\Migration;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Calculates the value of a property based on a previous migration. Unlike the
 * core "migration" plugin it may return multiple values if configuration option
 * "expect_multiple" is set to a non-empty value.
 *
 * @MigrateProcessPlugin(
 *   id = "multi_value_migration"
 * )
 */
class MultiValueMigration extends Migration {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $migration_ids = $this->configuration['migration'];
    if (!is_array($migration_ids)) {
      $migration_ids = array($migration_ids);
    }
    $scalar = FALSE;
    if (!is_array($value)) {
      $scalar = TRUE;
      $value = array($value);
    }
    $this->skipOnEmpty($value);
    $self = FALSE;
    /** @var MigrationInterface[] $migrations */
    $destination_ids = NULL;
    $source_id_values = array();
    $migrations = $this->migrationPluginManager->createInstances($migration_ids);
    foreach ($migrations as $migration_id => $migration) {
      if ($migration_id == $this->migration->id()) {
        $self = TRUE;
      }
      if (isset($this->configuration['source_ids'][$migration_id])) {
        $configuration = array('source' => $this->configuration['source_ids'][$migration_id]);
        $source_id_values[$migration_id] = $this->processPluginManager
          ->createInstance('get', $configuration, $this->migration)
          ->transform(NULL, $migrate_executable, $row, $destination_property);
      }
      else {
        $source_id_values[$migration_id] = $value;
      }
      // Break out of the loop as soon as a destination ID is found.
      if ($destination_ids = $migration->getIdMap()->lookupDestinationIds($source_id_values[$migration_id])) {
        if (empty($this->configuration['expect_multiple'])) {
          // Use first result.
          $destination_ids = reset($destination_ids);
        }
        // Lookup revision IDs as well.
//        $ids = call_user_func_array('array_merge', $destination_ids);
//        $revision_ids = \Drupal::database()
//          ->query('SELECT revision_id FROM {paragraphs_item} WHERE id IN (:ids[])', [':ids[]' => $ids])
//          ->fetchAll(\PDO::FETCH_COLUMN, 0);
        break;
      }
    }

    if (!$destination_ids && !empty($this->configuration['no_stub'])) {
      return NULL;
    }

    if (!$destination_ids && ($self || isset($this->configuration['stub_id']) || count($migrations) == 1)) {
      // If the lookup didn't succeed, figure out which migration will do the
      // stubbing.
      if ($self) {
        $migration = $this->migration;
      }
      elseif (isset($this->configuration['stub_id'])) {
        $migration = $migrations[$this->configuration['stub_id']];
      }
      else {
        $migration = reset($migrations);
      }
      $destination_plugin = $migration->getDestinationPlugin(TRUE);
      // Only keep the process necessary to produce the destination ID.
      $process = $migration->getProcess();

      // We already have the source ID values but need to key them for the Row
      // constructor.
      $source_ids = $migration->getSourcePlugin()->getIds();
      $values = array();
      foreach (array_keys($source_ids) as $index => $source_id) {
        $values[$source_id] = $source_id_values[$migration->id()][$index];
      }

      $stub_row = new Row($values + $migration->getSourceConfiguration(), $source_ids, TRUE);

      // Do a normal migration with the stub row.
      $migrate_executable->processRow($stub_row, $process);
      $destination_ids = array();
      try {
        $destination_ids = $destination_plugin->import($stub_row);
      }
      catch (\Exception $e) {
        $migration->getIdMap()->saveMessage($stub_row->getSourceIdValues(), $e->getMessage());
      }

      if ($destination_ids) {
        $migration->getIdMap()->saveIdMapping($stub_row, $destination_ids, MigrateIdMapInterface::STATUS_NEEDS_UPDATE);
      }
    }
    if ($destination_ids) {
      if ($scalar) {
        if (count($destination_ids) == 1) {
          return reset($destination_ids);
        }
      }
      else {
        foreach ($destination_ids as $key => $id) {
          $destination_ids[$key] = [
            'target_id' => $id[0],
            'target_revision_id' => $id[0],
          ];
        }
        return $destination_ids;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return !empty($this->configuration['expect_multiple']);
  }

}
