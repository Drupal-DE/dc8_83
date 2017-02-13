<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseFile;
use Drupal\migrate\Row;

/**
 * SQL-based source plugin for user pictures.
 *
 * @MigrateSource(
 *   id = "dc_file__user_picture"
 * )
 */
class UserPictureFile extends DcSqlBaseFile {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Rewrite query.
    $query = $this->select('users', 'u');
    $query->addField('u', 'uid');
    $query->addField('u', 'picture', 'uri');
    $query->condition('u.picture', '', '<>');
    $query->isNotNull('u.picture');

    return $query->distinct()->orderBy('u.uid');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'uid' => $this->t('File owner'),
      'filename' => $this->t('Name of file'),
      'filepath' => $this->t('File path'),
      'directory' => $this->t('File directory'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    // Extract file name.
    $path = $row->getSourceProperty('filepath');
    $row->setSourceProperty('filename', substr($path, strrpos($path, '/') + 1));
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

}
