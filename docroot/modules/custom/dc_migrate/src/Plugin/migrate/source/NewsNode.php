<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseNode;

/**
 * SQL-based source plugin for news nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__news",
 *   bundle = "news"
 * )
 */
class NewsNode extends DcSqlBaseNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $query->leftJoin('term_node', 'tn', 'tn.nid = n.nid');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid');
    $query->addField('td', 'tid', 'category');

    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    $fields['category'] = $this->t('News category');
  }

}
