<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Process class to attach images to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_fields__screenshot",
 *   description = "Link showroom nodes with their images",
 *   weight = 12
 * )
 */
class ShowroomScreenshot extends DcMigrateProcessorBase {

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
    $this->sourceDataTableName = 'dcmigrate_source__showroom_image';
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($options = []) {
    if (!parent::prepare($options)) {
      return FALSE;
    }

    $database = \Drupal::database();

    // Prepare source table and copy values.
    if (!$database->schema()->tableExists($this->sourceDataTableName)) {
      $fields = [];
      $fields['bundle'] = [
        'type' => 'varchar',
        'length' => '64',
        'not null' => TRUE,
        'description' => 'Target entity bundle',
      ];
      $fields['entity_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of new entity',
      ];
      $fields['revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Revision ID of new entity',
      ];
      $fields['field_screenshot_target_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of media entity on new site',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['entity_id', 'field_screenshot_target_id'],
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
    $query = $this->getRemainingItemsQuery();
    if ($limit > 0) {
      // Limit number of items to process.
      $query->range(0, $limit);
    }

    $this->insertIgnore('node__field_screenshot')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_screenshot_target_id'])
      ->from($query)
      ->execute();
    $this->insertIgnore('node_revision__field_screenshot')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_screenshot_target_id'])
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

    $database = \Drupal::database();
    $database->query('UPDATE IGNORE {node__field_screenshot} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);
    $database->query('UPDATE IGNORE {node_revision__field_screenshot} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->fields('s', ['bundle', 'entity_id', 'revision_id', 'field_screenshot_target_id']);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with mapping table.
    $query->leftJoin('node_revision__field_screenshot', 'nfs', 's.revision_id = nfs.revision_id');
    $query->isNull('nfs.entity_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $database = \Drupal::database();
    $state = \Drupal::state()->get('dc_migrate.database');
    if (empty($state['database'])) {
      throw new Exception('No source database defined!');
    }

    $query = $database->select('migrate_map_node__showroom', 'm');
    $query->join("{$state['database']}.content_type_showroom", 'cs', 'm.sourceid1 = cs.nid');
    $query->join('migrate_map_media__showroom_image', 'mi', 'cs.field_screenshot_der_webseite_fid = mi.sourceid1');
    $query->join('node', 'n', 'n.nid = m.destid1');
    $query->addExpression("'showroom'", 'bundle');
    $query->addField('n', 'nid', 'entity_id');
    $query->addField('n', 'vid', 'revision_id');
    $query->addField('mi', 'destid1', 'field_screenshot_target_id');

    $query->isNotNull('mi.destid1');

    $query->orderBy('m.destid1');

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
