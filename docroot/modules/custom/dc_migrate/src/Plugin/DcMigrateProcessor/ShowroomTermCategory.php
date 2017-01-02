<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomTerm;

/**
 * Process class to attach the showroom category to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_fields__showroom_category",
 *   description = "Set showroom category on showroom nodes",
 *   weight = 2
 * )
 */
class ShowroomTermCategory extends ShowroomTerm {

  /**
   * {@inheritdoc}
   */
  public function init() {
    parent::init();
    $this->sourceDataTableName = 'dcmigrate_source__node__showroom_category';
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
      $fields['nid'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'New ID of showroom node',
      ];
      $fields['revision_id'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'New revision ID of showroom node',
      ];
      $fields['tid'] = [
        'type' => 'int',
        'length' => '11',
        'not null' => FALSE,
        'description' => 'New ID of term',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['revision_id', 'tid'],
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

    $this->insertIgnore('node__field_showroom_category')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_showroom_category_target_id'])
      ->from($query)
      ->execute();
    $this->insertIgnore('node_revision__field_showroom_category')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_showroom_category_target_id'])
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
    $database->query('UPDATE IGNORE {node__field_showroom_category} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode', [':langcode' => 'de']);
    $database->query('UPDATE IGNORE {node_revision__field_showroom_category} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode', [':langcode' => 'de']);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    /* @var $query \Drupal\Core\Database\Query\SelectInterface */
    $query = parent::getSourceQuery();
    // Add join to term map table.
    $query->leftJoin('migrate_map_term__showroom_category', 'mdc', 'mdc.sourceid1 = tn.tid');
    $query->leftJoin('taxonomy_term_data', 'td', 'td.tid = mdc.destid1');

    $query->addField('td', 'tid');
    $query->condition('td.vid', 'showroom_category');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $query = parent::getQuery();
    $query->addField('s', 'tid', 'field_showroom_category_target_id');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with revision table.
    $query->leftJoin('node_revision__field_showroom_category', 'fdc', 's.revision_id = fdc.revision_id');
    $query->isNull('fdc.entity_id');

    return $query;
  }

}
