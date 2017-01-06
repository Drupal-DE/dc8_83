<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;

/**
 * General SQL-based source plugin for taxonomy terms.
 *
 * @MigrateSource(
 *   id = "dc_term__base"
 * )
 */
class DcSqlBaseTerm extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $options = $this->getConfig('options', []);

    $query = $this->select('term_data', 'td');
    $query->fields('td', ['tid', 'vid', 'name', 'description', 'weight']);

    if (empty($options['disable_hierarchy'])) {
      // Join to hierarchy table.
      $query->join('term_hierarchy', 'th', 'td.tid = th.tid');
      $query->addField('th', 'parent');
      $query->groupBy('td.tid');
    }

    $vid = $this->getConfig('vid');
    if (isset($vid)) {
      $query->condition('td.vid', $vid);
    }

    $vocabulary = $this->getConfig('vocabulary', FALSE);
    if (!empty($vocabulary)) {
      // Add join to vocabulary table to allow filtering by machine_name.
      $query->join('vocabulary', 'tv', 'td.vid = tv.vid');
      $query->condition('tv.machine_name', $vocabulary);
    }

    $this->alterQuery($query);

    return $query->orderBy('td.tid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Define base fields for terms.
    $fields = [
      'tid' => $this->t('Term ID'),
      'vid' => $this->t('Term vocabulary'),
      'name' => $this->t('Term name'),
      'description' => $this->t('Term description'),
      'weight' => $this->t('Term weight'),
      'parent' => $this->t('Term parent'),
    ];

    $this->alterFields($fields);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'td',
      ],
    ];
  }

}
