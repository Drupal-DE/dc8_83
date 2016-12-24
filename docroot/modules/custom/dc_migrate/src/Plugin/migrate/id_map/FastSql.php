<?php

namespace Drupal\dc_migrate\Plugin\migrate\id_map;

use Drupal\migrate\Plugin\migrate\id_map\Sql;

/**
 * Defines a faster sql based ID map implementation.
 *
 * It creates unique keys over all source columns so joins would be much faster.
 *
 * @PluginID("fastsql")
 */
class FastSql extends Sql {

  /**
   * {@inheritdoc}
   */
  protected function ensureTables() {
    parent::ensureTables();

    if ($this->getDatabase()->schema()->indexExists($this->mapTableName, 'source')) {
      return;
    }
    // Add unique index over all source columns.
    $count = 1;
    $unique_fields = [];
    foreach ($this->migration->getSourcePlugin()->getIds() as $id_definition) {
      $unique_fields[] = 'sourceid' . $count++;
    }

    $this->getDatabase()->schema()->addUniqueKey($this->mapTableName, 'source', $unique_fields);
  }

}
