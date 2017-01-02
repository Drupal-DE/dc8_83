<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessorBase;

/**
 * Process class to fix parent information of paragraphs attached to showroom
 * nodes.
 *
 * @DcMigrateProcessor(
 *   id = "parent_fix__showroom_paragraphs",
 *   description = "Fix parent information in showroom paragraph items",
 *   weight = 10
 * )
 */
class ShowroomParagraphsParentFix extends DcMigrateProcessorBase {

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
    $this->sourceDataTableName = 'node__field_content';
  }

  /**
   * {@inheritdoc}
   */
  public function process($limit = 1, $options = []) {
    $database = \Drupal::database();
    $query = $this->getRemainingItemsQuery();
    if ($limit > 0) {
      // Limit number of items to process.
      $query->range(0, $limit);
    }
    $results = $query
      ->execute()
      ->fetchAll();

    // The update may take a while so we wrap all actions into a single
    // transaction.
    $transaction = $database->startTransaction('update');
    try {
      foreach ($results as $record) {
        $args = [
          ':parent_id' => $record->entity_id,
          ':parent_type' => 'node',
          ':field' => 'field_content',
          ':revision_id' => $record->field_content_target_revision_id,
          ':type' => 'text',
        ];
        $database->query('UPDATE IGNORE {paragraphs_item_field_data} '
          . 'SET parent_id = :parent_id, parent_type = :parent_type, parent_field_name = :field '
          . 'WHERE revision_id = :revision_id AND type = :type', $args);
      }
    }
    catch (\Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    // No need to commit the transaction since it is automatically closed when
    // the variable loses its scope.

    return ($limit > 0) ? $limit : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $database = \Drupal::database();

    $query = $database->select($this->sourceDataTableName, 's');
    $query->fields('s', ['entity_id', 'field_content_target_revision_id']);
    $query->condition('bundle', 'showroom');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemainingItemsQuery() {
    $query = $this->getQuery();
    // Join with mapping table.
    $query->leftJoin('paragraphs_item_field_data', 'map', 's.field_content_target_revision_id = map.revision_id	AND	s.entity_id = map.parent_id');
    $query->isNull('map.id');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return NULL;
  }

}
