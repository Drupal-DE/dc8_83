<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Base process class to attach terms to showroom nodes.
 */
abstract class ShowroomTerm extends DcMigrateProcessorBase {

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
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->addField('s', 'bundle');
    $query->addField('s', 'nid', 'entity_id');
    $query->addField('s', 'revision_id');

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

    $query = $database->select('migrate_map_node__showroom', 'mn');
    // Join to {node} to get the revision_id.
    $query->join('node', 'n', 'n.nid = mn.destid1');
    // Join to tables in source database.
    $query->join("{$state['database']}.term_node", 'tn', 'tn.nid = mn.sourceid1');
    // Add fields in required order.
    $query->addExpression("'showroom'", 'bundle');
    $query->addField('n', 'nid');
    $query->addExpression('n.vid', 'revision_id');

    // Sort results.
    $query->orderBy('n.nid');

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
