<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Base process class to set field values in user profiles.
 */
abstract class AccountFieldsBase extends DcMigrateProcessorBase {

  /**
   * Name of source data table with contents from
   * various databases/tables.
   *
   * @var string
   */
  protected $sourceDataTableName = NULL;

  /**
   * {@inheritdoc}
   */
  public function init() {
    parent::init();
    $field = $this->configuration['field'];
    $this->sourceDataTableName = "dcmigrate_source__account_field__{$field['alias']}";
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($options = []) {
    if (!parent::prepare($options)) {
      return FALSE;
    }

    $field = $this->configuration['field'];
    /* @var $database \Drupal\Core\Database\Connection */
    $database = \Drupal::database();

    // Prepare source table and copy values.
    if (!$database->schema()->tableExists($this->sourceDataTableName)) {
      $fields = [];
      $fields['bundle'] = [
        'type' => 'varchar',
        'length' => '64',
        'not null' => TRUE,
        'description' => 'Target bundle',
      ];
      $fields['entity_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of new showroom entity',
      ];
      $fields['revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Revision ID of new showroom entity',
      ];
      $fields['langcode'] = [
        'type' => 'varchar',
        'length' => '64',
        'not null' => FALSE,
        'description' => 'Langcode',
      ];
      $fields[$field['name']] = [
        'type' => 'varchar',
        'length' => '255',
        'not null' => FALSE,
        'description' => $field['name'],
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['entity_id', 'langcode'],
      ];
      $database->schema()->createTable($this->sourceDataTableName, $schema);

      $options['refresh_source_data'] = TRUE;
    }
    if (!empty($options['refresh_source_data'])) {
      $database->truncate($this->sourceDataTableName);
      // Insert data.
      $this->insertIgnore($this->sourceDataTableName)
        ->from($this->getSourceQuery())
        ->execute();
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function process($limit = 1, $options = []) {
    $field = $this->configuration['field'];
    $query = $this->getRemainingItemsQuery();
    if ($limit > 0) {
      // Limit number of items to process.
      $query->range(0, $limit);
    }

    $this->insertIgnore("{$field['table']}")
      ->fields(['bundle', 'entity_id', 'revision_id', 'langcode', $field['name']])
      ->from($query)
      ->execute();

    return ($limit > 0) ? $limit : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanup($options = []) {
    if (!parent::cleanup($options)) {
      return FALSE;
    }

    $field = $this->configuration['field'];
    $database = \Drupal::database();
    $database->query("UPDATE IGNORE {{$field['table']}} SET deleted = 0");

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $field = $this->configuration['field'];
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->fields('s', ['bundle', 'entity_id', 'revision_id', 'langcode', $field['name']]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $field = $this->configuration['field'];
    $query = $this->getQuery();
    // Join with field table.
    $query->leftJoin("{$field['table']}", 'uf', 's.revision_id = uf.revision_id');
    $query->isNull('uf.entity_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $database = \Drupal::database();
    $field = $this->configuration['field'];
    $state = \Drupal::state()->get('dc_migrate.database');
    if (empty($state['database'])) {
      throw new Exception('No source database defined!');
    }

    $query = $database->select('migrate_map_user_accounts', 'mu');
    $query->join('users', 'u', 'u.uid = mu.destid1');
    // Join to tables in source database.
    $query->join("{$state['database']}.profile_values", 'p', 'p.uid = mu.sourceid1');

    $query->addExpression("'user'", 'bundle');
    $query->addField('mu', 'destid1', 'entity_id');
    $query->addField('mu', 'destid1', 'revision_id');
    $query->addField('u', 'langcode');
    $query->addField('p', 'value', $field['name']);

    $query->condition('p.fid', $field['fid']);

    // Do not include unmigrated items.
    $query->isNotNull('mu.destid1');
    // Do not include empty items.
    $query->isNotNull('p.value');
    $query->condition('p.value', '', '<>');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    // Do not create custom map table.
    return NULL;
  }

}
