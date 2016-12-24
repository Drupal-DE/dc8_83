<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;
use Drupal\migrate\MigrateException;

/**
 * General SQL-based source plugin for nodes.
 *
 * @MigrateSource(
 *   id = "node__base"
 * )
 */
class DcSqlBaseNode extends DcSqlBase {

  /**
   * List of fields to include in query.
   *
   * @var array
   */
  protected $field_definitions = [];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $query = $this->select('node', 'n')
      ->fields('n', ['nid', 'vid', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote', 'sticky'])
      ->condition('type', $bundle);

    foreach ($this->getFieldDefinitions() as $key => $field) {
      $alias = $field['alias'];
      $table_alias = $field['table_alias'];
      $value_key = empty($field['value_key']) ? 'value' : $field['value_key'];
      $join_condition = isset($field['condition']) ? $field['condition'] : "{$table_alias}.entity_id = n.nid";
      $query->leftJoin("field_data_{$key}", $table_alias, $join_condition);
      $query->addField($table_alias, "{$key}_{$value_key}", $alias);
    }

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
      'vid' => $this->t('Node revision'),
      'language' => $this->t('Language'),
      'title' => $this->t('Node title'),
      'uid' => $this->t('Node author'),
      'status' => $this->t('Node status'),
      'created' => $this->t('Creation date'),
      'changed' => $this->t('Update date'),
      'promote' => $this->t('Promoted to frontpage'),
      'sticky' => $this->t('Sticky at top of lists'),
    ];

    foreach ($this->getFieldDefinitions() as $key => $field) {
      $fields[$field['alias']] = isset($field['description']) ? $field['description'] : $key;
    }

    $this->alterFields($fields);

    return $fields;
  }

  /**
   * Get list of field definitions to include in the query.
   *
   * @return array
   *   Associative list of field definitions.
   */
  public function getFieldDefinitions() {
    return [];
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
