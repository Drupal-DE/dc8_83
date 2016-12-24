<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;
use Drupal\migrate\Row;

/**
 * General SQL-based source plugin for taxonomy terms.
 *
 * @MigrateSource(
 *   id = "term__base"
 * )
 */
class DcSqlBaseTerm extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $options = $this->getConfig('options', []);

    $query = $this->select('taxonomy_term_data', 'td');
    $query->fields('td', ['tid', 'vid', 'name', 'description', 'format', 'weight']);

    if (empty($options['disable_hierarchy'])) {
      // Join to hierarchy table.
      $query->join('taxonomy_term_hierarchy', 'th', 'td.tid = th.tid');
      $query->addField('th', 'parent');
      $query->groupBy('td.tid');
    }

    $vocabulary = $this->getConfig('vocabulary', FALSE);
    if (!empty($vocabulary)) {
      // Add join to vocabulary table to allow filtering by machine_name.
      $query->join('taxonomy_vocabulary', 'tv', 'td.vid = tv.vid');
      $query->condition('tv.machine_name', $vocabulary);
    }

    if (empty($options['disable_metatags']) && \Drupal::moduleHandler()->moduleExists('metatag')) {
      // Add join to metatag-table.
      $query->leftJoin('metatag', 'm', 'td.tid = m.revision_id');
      $query->addField('m', 'data', 'metatags');
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
      'format' => $this->t('Term description format'),
      'weight' => $this->t('Term weight'),
      'parent' => $this->t('Term parent'),
    ];
    if (\Drupal::moduleHandler()->moduleExists('metatag')) {
      $fields['metatags'] = $this->t('Term metatags');
    }

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

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    // Fix FB metatag value.
    if (!$row->hasSourceProperty('metatags')) {
      return TRUE;
    }
    $metatags = unserialize($row->getSourceProperty('metatags'));
    if (empty($metatags['fb:app_id']) || empty($metatags['fb:app_id']['value'])) {
      // Nothing to do here.
      return TRUE;
    }
    // The name of the key changed also. Yeah!
    $metatags['fb_app_id'] = $metatags['fb:app_id']['value'];
    unset($metatags['fb:app_id']);
    $row->setSourceProperty('metatags', serialize($metatags));

    return TRUE;
  }

}
