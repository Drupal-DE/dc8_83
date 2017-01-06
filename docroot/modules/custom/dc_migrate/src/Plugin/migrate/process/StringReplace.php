<?php

namespace Drupal\dc_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin allows string replacements in the source value.
 *
 * @MigrateProcessPlugin(
 *   id = "string_replace"
 * )
 */
class StringReplace extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $replacements = $this->configuration['replacements'] ?: [];
    if (!is_array($replacements)) {
      $replacements = [$replacements];
    }
    return strtr($value, $replacements);
  }

}
