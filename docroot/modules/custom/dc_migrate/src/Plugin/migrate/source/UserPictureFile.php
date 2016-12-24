<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseFile;

/**
 * SQL-based source plugin for user pictures.
 *
 * @MigrateSource(
 *   id = "file__user_picture"
 * )
 */
class UserPictureFile extends DcSqlBaseFile {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    // Add join to table of field "users".
    $query->join('users', 'u', 'fm.fid = u.picture');
  }

}
