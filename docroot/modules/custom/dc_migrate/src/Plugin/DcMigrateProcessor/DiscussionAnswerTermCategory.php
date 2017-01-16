<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Process class to set discussion category on answers.
 *
 * @DcMigrateProcessor(
 *   id = "discussion_fields__answer_category",
 *   description = "Set discussion category for discussion answers",
 *   weight = 14
 * )
 */
class DiscussionAnswerTermCategory extends DcMigrateProcessorBase {

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
    $this->sourceDataTableName = 'dcmigrate_source__node__answer_category';
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
        'description' => 'Target bundle',
      ];
      $fields['entity_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of discussion node',
      ];
      $fields['revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Revision ID of discussion node',
      ];
      $fields['field_discussion_category_target_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of category',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['revision_id', 'field_discussion_category_target_id'],
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
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->addField('s', 'bundle');
    $query->addField('s', 'entity_id');
    $query->addField('s', 'revision_id');
    $query->addField('s', 'field_discussion_category_target_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $state = \Drupal::state()->get('dc_migrate.database');
    if (empty($state['database'])) {
      throw new Exception('No source database defined!');
    }
    $database = \Drupal::database();

    $query = $database->select('migrate_map_node__discussion_answers', 'mda');
    // Join to comments in source database.
    $query->join("{$state['database']}.comments", 'c', 'c.cid = mda.sourceid1 AND mda.sourceid2 = c.pid AND mda.sourceid3 = c.nid');
    // Join to node table for entity revision.
    $query->join('node', 'n', 'n.nid = mda.destid1');
    $query->join('migrate_map_node__discussion', 'md', 'md.sourceid1 = c.nid');
    $query->join('node__field_discussion_category', 'fdc', 'fdc.entity_id = md.destid1');

    // Add fixed value.
    $query->addExpression("'discussion'", 'bundle');
    $query->addField('n', 'nid', 'entity_id');
    $query->addField('n', 'vid', 'revision_id');
    $query->addField('fdc', 'field_discussion_category_target_id');

    // Sort results.
    $query->orderBy('c.cid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    // Do not create custom map table.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with revision table.
    $query->leftJoin('node__field_discussion_category', 'fc', 's.revision_id = fc.revision_id');
    $query->isNull('fc.entity_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function process($limit = -1, $options = array()) {
    $query = $this->getRemainingItemsQuery();
    if ($limit > 0) {
      // Limit number of items to process.
      $query->range(0, $limit);
    }

    $this->insertIgnore('node__field_discussion_category')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_discussion_category_target_id'])
      ->from($query)
      ->execute();
    $this->insertIgnore('node_revision__field_discussion_category')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_discussion_category_target_id'])
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
    $database->query('UPDATE IGNORE {node__field_discussion_category} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);
    $database->query('UPDATE IGNORE {node_revision__field_discussion_category} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);

    return TRUE;
  }

}
