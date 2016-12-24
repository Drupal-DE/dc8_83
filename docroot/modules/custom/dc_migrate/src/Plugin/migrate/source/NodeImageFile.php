<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseImage;
use Drupal\migrate\MigrateException;

/**
 * SQL-based source plugin for node images.
 *
 * @MigrateSource(
 *   id = "file__node_image"
 * )
 */
class NodeImageFile extends DcSqlBaseImage {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);

    $source_config = $this->migration->getSourceConfiguration();
    if (empty($this->pluginDefinition['bundle']) && empty($source_config['bundle'])) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $bundle = empty($this->pluginDefinition['bundle']) ? $source_config['bundle'] : $this->pluginDefinition['bundle'];

    // Add join to table of field "field_data_field_image".
    $query->leftJoin('field_data_field_image', 'fi', 'fm.fid = fi.field_image_fid');
    $query->condition('fi.entity_type', 'node')
      ->condition('fi.bundle', $bundle);
  }

}
