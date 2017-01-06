<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Base process class to attach paragraphs to showroom nodes.
 */
abstract class ShowroomParagraphsBase extends DcMigrateProcessorBase {

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
    $this->sourceDataTableName = "dcmigrate_source__showroom_paragraph__{$field}";
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($options = []) {
    if (!parent::prepare($options)) {
      return FALSE;
    }

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
      $fields['delta'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Delta of paragraph',
      ];
      $fields['field_content_target_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'ID of paragraph',
      ];
      $fields['field_content_target_revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'Revision ID of paragraph',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['entity_id', 'revision_id', 'delta'],
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

    $this->insertIgnore('node__field_content')
      ->fields(['bundle', 'entity_id', 'revision_id', 'delta', 'field_content_target_id', 'field_content_target_revision_id'])
      ->from($query)
      ->execute();
    $this->insertIgnore('node_revision__field_content')
      ->fields(['bundle', 'entity_id', 'revision_id', 'delta', 'field_content_target_id', 'field_content_target_revision_id'])
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
    $database->query('UPDATE IGNORE {node__field_content} '
      . 'SET deleted = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);
    $database->query('UPDATE IGNORE {node_revision__field_content} '
      . 'SET deleted = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->fields('s', ['bundle', 'entity_id', 'revision_id', 'delta', 'field_content_target_id', 'field_content_target_revision_id']);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with revision table.
    $query->leftJoin('node_revision__field_content', 'rfc', 's.revision_id = rfc.revision_id	AND	s.delta = rfc.delta');
    $query->isNull('rfc.entity_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $database = \Drupal::database();
    $field = $this->configuration['field'];
    $delta = $this->configuration['delta'];

    $query = $database->select("migrate_map_paragraph__text__showroom_{$field}", 'mp');
    // Join to tables in source database.
    $query->join('migrate_map_node__showroom', 'mn', 'mn.sourceid1 = mp.sourceid1');
    $query->join('paragraphs_item', 'p', 'p.id = mp.destid1');
    $query->join('node', 'n', 'n.nid = mn.destid1');
    $query->addExpression("'showroom'", 'bundle');
    $query->addField('n', 'nid', 'entity_id');
    $query->addField('n', 'vid', 'revision_id');
    $query->addExpression($delta, 'delta');
    $query->addField('p', 'id', 'field_content_target_id');
    $query->addField('p', 'revision_id', 'field_content_target_revision_id');

    // Do not include unmigrated items.
    $query->isNotNull('mp.destid1');

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
