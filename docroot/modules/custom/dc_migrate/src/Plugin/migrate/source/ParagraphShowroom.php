<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;
use Drupal\migrate\Row;

/**
 * SQL-based source plugin for showroom paragraphs.
 *
 * @MigrateSource(
 *   id = "dc_paragraph__showroom"
 * )
 */
class ParagraphShowroom extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('content_type_showroom', 'cs');
    $query->join('node_revisions', 'nr', 'cs.vid = nr.vid');
    $query->addField('cs', 'nid');
    $query->addField('nr', 'body', 'description');
    $query->addField('cs', 'field_erluterung_zu_eigenen_mod_value', 'custom_development');
    $query->addField('cs', 'field_screenshot_der_webseite_fid', 'screenshot');

    $this->alterQuery($query);

    return $query->orderBy('cs.nid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Define base fields for nodes.
    $fields = [
      'nid' => $this->t('Node ID'),
      'body' => $this->t('Showroom description'),
      'custom_development' => $this->t('Custom development description'),
      'screenshot' => $this->t('Screenshot of website'),
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

  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'cs',
      ],
    ];
  }

}
