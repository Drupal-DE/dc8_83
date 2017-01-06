<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;

/**
 * SQL-based source plugin for terms in showroom nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__showroom__terms",
 * )
 */
class ShowroomNodeTerms extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $vid = $this->getConfig('vid');
    if (empty($vid)) {
      throw new MigrateException('You need to specify the vocabulary ID in the plugin definition or in the migration.');
    }

    $query = $this->select('term_node', 'tn');
    $query->join('node', 'n', 'n.nid = tn.nid');
    $query->join('term_data', 'td', 'td.tid = tn.tid');
    $query->condition('n.type', 'showroom');
    $query->condition('td.vid', $vid);

    $query->addField('n', 'nid');
    $query->groupBy('n.nid');

    $this->alterQuery($query);

    return $query->orderBy('n.nid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Define base fields for nodes.
    $fields = [
      'nid' => $this->t('Node ID'),
      'terms' => $this->t('List of term IDs (comma separated)'),
      'term_names' => $this->t('List of term names (comma separated)'),
    ];

    $this->alterFields($fields);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    $vid = $this->getConfig('vid');
    $nid = $row->getSourceProperty('nid');
    $query = $this->select('term_node', 'tn');
    $query->join('term_data', 'td', 'td.tid = tn.tid');
    $query->condition('td.vid', $vid);
    $query->condition('tn.nid', $nid);

    $query->fields('td', ['tid', 'name']);

    $results = $query->execute()->fetchAllKeyed();
    $row->setSourceProperty('terms', implode(', ', array_keys($results)));
    $row->setSourceProperty('term_names', implode(', ', array_values($results)));
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

}
