<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;

/**
 * SQL-based source plugin for book nodes.
 *
 * @MigrateSource(
 *   id = "dc_book"
 * )
 */
class Book extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this
      ->select('book', 'b')
      ->fields('b', ['nid', 'bid']);
    $query->join('menu_links', 'ml', 'b.mlid = ml.mlid');
    $ml_fields = ['mlid', 'plid', 'weight', 'has_children', 'depth'];
    for ($i = 1; $i <= 9; $i++) {
      $field = "p{$i}";
      $ml_fields[] = $field;
      $query->orderBy('ml.' . $field);
    }
    $query->fields('ml', $ml_fields);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('Node ID'),
      'bid' => $this->t('Book ID'),
      'mlid' => $this->t('Menu link ID'),
      'plid' => $this->t('Parent link ID'),
      'weight' => $this->t('Weight'),
      'p1' => $this->t('The first mlid in the materialized path. If N = depth, then pN must equal the mlid. If depth > 1 then p(N-1) must equal the parent link mlid. All pX where X > depth must equal zero. The columns p1 .. p9 are also called the parents.'),
      'p2' => $this->t('The second mlid in the materialized path. See p1.'),
      'p3' => $this->t('The third mlid in the materialized path. See p1.'),
      'p4' => $this->t('The fourth mlid in the materialized path. See p1.'),
      'p5' => $this->t('The fifth mlid in the materialized path. See p1.'),
      'p6' => $this->t('The sixth mlid in the materialized path. See p1.'),
      'p7' => $this->t('The seventh mlid in the materialized path. See p1.'),
      'p8' => $this->t('The eighth mlid in the materialized path. See p1.'),
      'p9' => $this->t('The ninth mlid in the materialized path. See p1.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'mlid' => [
        'type' => 'integer',
        'alias' => 'ml',
      ],
    ];
  }

}
