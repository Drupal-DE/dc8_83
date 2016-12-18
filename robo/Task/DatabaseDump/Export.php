<?php

namespace DrupalCenter\Robo\Task\DatabaseDump;

use DrupalCenter\Robo\Utility\Drush;

/**
 * Robo task: Export database dump.
 */
class Export extends Dump {

  /**
   * {@inheritdoc}
   */
  public function run() {
    return Drush::exec()
      ->arg('sql-dump')
      ->arg('>')
      ->arg(escapeshellarg($this->filepath))
      ->option('ordered-dump')
      ->option('extra=--skip-comments')
      ->option('structure-tables-list=' . escapeshellarg(implode(',', $this->getStructureOnlyTableList())))
      ->run();
  }

  /**
   * Return structure-only database table names.
   *
   * This is used to make the exported file as small as possible. All returned
   * database table names indicate to only export their structure but not data
   * rows.
   *
   * @return array
   *   An array of database table names.
   */
  protected function getStructureOnlyTableList() {
    $tables = [
      'cache_data',
      'cache_bootstrap',
      'cache_container',
      'cache_config',
      'cache_default',
      'cache_discovery',
      'cache_dynamic_page_cache',
      'cache_entity',
      'cache_menu',
      'cache_migrate',
      'cache_render',
      'cache_toolbar',
      'cachetags',
      'watchdog',
      'sessions',
    ];

    return $tables;
  }

}
