<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseNode;

/**
 * SQL-based source plugin for discussion nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__discussion__terms",
 *   bundle = "forum"
 * )
 */
class DiscussionNodeTerms extends DcSqlBaseNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $vid = $this->getConfig('vid');
    if (empty($vid)) {
      throw new MigrateException('You need to specify the vocabulary ID in the plugin definition or in the migration.');
    }
    $query->join('term_node', 'tn', 'tn.nid = n.nid');
    $query->join('term_data', 'td', 'td.tid = tn.tid');
    $query->addField('td', 'tid');
    $query->condition('td.vid', $vid);

    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    $fields['tid'] = $this->t('Term ID');
  }

}
