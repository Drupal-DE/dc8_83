<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;

/**
 * General SQL-based source plugin for comments.
 *
 * @MigrateSource(
 *   id = "dc_comment__base"
 * )
 */
class DcSqlBaseComment extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bundle = $this->getConfig('bundle');

    $query = $this->select('comments', 'c');
    $query->fields('c', ['cid', 'pid', 'nid', 'uid', 'subject', 'comment', 'hostname', 'timestamp', 'status', 'thread', 'name', 'mail', 'homepage']);

    if (!empty($bundle)) {
      // Limit comments to specified node bundle.
      $query->join('node', 'n', 'n.nid = c.nid');
      if (is_array($bundle)) {
        $query->condition('n.type', $bundle, 'IN');
      }
      else {
        $query->condition('n.type', $bundle);
      }
    }

    $this->alterQuery($query);

    return $query->orderBy('c.cid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Define base fields for comments.
    $fields = [
      'cid' => $this->t('Comment ID'),
      'pid' => $this->t('ID of parent comment'),
      'nid' => $this->t('Node ID, the comment is attached to'),
      'uid' => $this->t('Comment author'),
      'subject' => $this->t('Comment subject'),
      'comment' => $this->t('Comment'),
      'hostname' => $this->t('Hostname'),
      'timestamp' => $this->t('Timestamp of comment'),
      'status' => $this->t('Comment status (0 = published, 1 = unpublished)'),
      'thread' => $this->t('Comment thread position'),
      'name' => $this->t('Author name'),
      'mail' => $this->t('Author mail'),
      'homepage' => $this->t('Author homepage'),
    ];

    $this->alterFields($fields);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'cid' => [
        'type' => 'integer',
        'alias' => 'c',
      ],
      'pid' => [
        'type' => 'integer',
        'alias' => 'c',
      ],
      'nid' => [
        'type' => 'integer',
        'alias' => 'c',
      ],
    ];
  }

}
