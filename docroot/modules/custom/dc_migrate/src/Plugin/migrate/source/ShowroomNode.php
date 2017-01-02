<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseNode;

/**
 * SQL-based source plugin for news nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__showroom",
 *   bundle = "showroom"
 * )
 */
class ShowroomNode extends DcSqlBaseNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $query->leftJoin('content_field_url_der_webseite', 'fu', 'fu.nid = n.nid');
    $query->addField('fu', 'field_url_der_webseite_url', 'url');

    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    $fields['url'] = $this->t('Showroom website url');
  }

}
