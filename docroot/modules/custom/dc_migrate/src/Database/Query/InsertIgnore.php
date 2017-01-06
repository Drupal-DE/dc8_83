<?php

namespace Drupal\dc_migrate\Database\Query;

use Drupal\Core\Database\Query\Insert;

/**
 * General class for an abstracted INSERT query.
 *
 * @ingroup database
 */
class InsertIgnore extends Insert {

  /**
   * Indicating whether to ignore errors on insert.
   *
   * @var boolean
   */
  protected $ignore = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct($connection, $table, array $options = []) {
    $options += ['ignore' => TRUE];
    parent::__construct($connection, $table, $options);
    $this->ignore = !empty($options['ignore']);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $return = parent::__toString();
    if ($this->ignore) {
      return str_replace('INSERT INTO', 'INSERT IGNORE INTO', $return);
    }
    return $return;
  }

}
