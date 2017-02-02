<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Process class to attach images to user profiles.
 *
 * @DcMigrateProcessor(
 *   id = "user__picture",
 *   description = "Link user profiles with their images",
 *   weight = 10
 * )
 */
class UserPicture extends DcMigrateProcessorBase {

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
    $this->sourceDataTableName = 'dcmigrate_source__user_picture';
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($options = []) {
    if (!parent::prepare($options)) {
      return FALSE;
    }

    $database = \Drupal::database();
    $configuration = $this->getConfiguration();

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
        'description' => 'ID of new user',
      ];
      $fields['revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Revision ID of new user',
      ];
      $fields['field_image_target_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of media entity on new site',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['entity_id', 'field_image_target_id'],
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

    $this->insertIgnore('user__field_image')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_image_target_id'])
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
    $database->query('UPDATE IGNORE {user__field_image} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->fields('s', ['bundle', 'entity_id', 'revision_id', 'field_image_target_id']);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with mapping table.
    $query->leftJoin('user__field_image', 'ufi', 's.revision_id = ufi.revision_id');
    $query->isNull('ufi.entity_id');

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

    $query = $database->select('migrate_map_user_accounts', 'm');
    $query->join('migrate_map_media__user_picture', 'u', 'm.sourceid1 = u.sourceid1');
    $query->addExpression("'user'", 'bundle');
    $query->addField('m', 'destid1', 'entity_id');
    $query->addField('m', 'destid1', 'revision_id');
    $query->addField('u', 'destid1', 'field_image_target_id');

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
