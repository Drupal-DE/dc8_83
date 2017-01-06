<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseNode;

/**
 * SQL-based source plugin for book nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__book",
 *   bundle = "book"
 * )
 */
class BookNode extends DcSqlBaseNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(\Drupal\Core\Database\Query\SelectInterface $query) {
    $query->leftJoin('term_node', 'tn', 'tn.nid = n.nid');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid');
    $query->addField('td', 'tid', 'drupal_version');

    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    $fields['drupal_version'] = $this->t(('Drupal version'));
  }

}
