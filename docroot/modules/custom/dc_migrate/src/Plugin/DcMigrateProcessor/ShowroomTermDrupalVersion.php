<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomTerm;

/**
 * Process class to attach the drupal version to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_fields__drupal_version",
 *   description = "Set drupal version on showroom nodes",
 *   weight = 1
 * )
 */
class ShowroomTermDrupalVersion extends ShowroomTerm {

  /**
   * {@inheritdoc}
   */
  public function init() {
    parent::init();
    $this->sourceDataTableName = 'dcmigrate_source__node__showroom_dv';
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
      $fields['version_identifier'] = [
        'type' => 'varchar',
        'length' => '25',
        'not null' => FALSE,
        'description' => 'New term identifier',
      ];
      $schema = [
        'description' => 'Contains all relevant source data.',
        'fields' => $fields,
        'primary key' => ['revision_id', 'version_identifier'],
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

    $this->insertIgnore('node__field_drupal_version')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_drupal_version_target_id'])
      ->from($query)
      ->execute();
    $this->insertIgnore('node_revision__field_drupal_version')
      ->fields(['bundle', 'entity_id', 'revision_id', 'field_drupal_version_target_id'])
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
    $database->query('UPDATE IGNORE {node__field_drupal_version} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);
    $database->query('UPDATE IGNORE {node_revision__field_drupal_version} '
      . 'SET deleted = 0, delta = 0, langcode = :langcode WHERE langcode = :empty', [':langcode' => 'de', ':empty' => '']);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $state = \Drupal::state()->get('dc_migrate.database');
    /* @var $query \Drupal\Core\Database\Query\SelectInterface */
    $query = parent::getSourceQuery();
    // Join to table "term_data" in source database.
    $query->join("{$state['database']}.term_data", 'std', 'std.tid = tn.tid');

    // Add expression to map old values to new identifiers.
    $expression = "CASE
		WHEN tn.tid = 51 THEN 'term--version-46'
		WHEN tn.tid = 52 THEN 'term--version-47'
		WHEN tn.tid = 53 THEN 'term--version-5'
		WHEN tn.tid = 54 THEN 'term--version-6'
		WHEN tn.tid = 1437 THEN 'term--version-7'
		WHEN tn.tid = 1927 THEN 'term--version-8'
	END";
    $query->addExpression($expression, 'version_identifier');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $query = parent::getQuery();

    // Join to map table of drupal_version.
    $query->join('migrate_map_term__drupal_version', 'mtdv', 's.version_identifier = mtdv.sourceid1');
    $query->addField('mtdv', 'destid1', 'field_drupal_version_target_id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with revision table.
    $query->leftJoin('node_revision__field_drupal_version', 'fdc', 's.revision_id = fdc.revision_id');
    $query->isNull('fdc.entity_id');

    return $query;
  }

}
