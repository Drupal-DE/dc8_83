<?php

namespace Drupal\dc_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin removes duplicate values from source value.
 *
 * @MigrateProcessPlugin(
 *   id = "array_unique",
 *   handle_multiples = TRUE
 * )
 */
class ArrayUnique extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      return array_unique($value);
    }
    throw new MigrateException(sprintf('%s is not an array', var_export($value, TRUE)));
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
