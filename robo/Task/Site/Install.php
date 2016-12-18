<?php

namespace DrupalCenter\Robo\Task\Site;

use DrupalCenter\Robo\Task\DatabaseDump\Export;
use DrupalCenter\Robo\Task\DatabaseDump\Import;
use DrupalCenter\Robo\Task\Drush\CacheRebuild;
use DrupalCenter\Robo\Task\Drush\ConfigExport;
use DrupalCenter\Robo\Task\Drush\EnableExtension;
use DrupalCenter\Robo\Task\Drush\LocaleUpdate;
use DrupalCenter\Robo\Task\Drush\SiteInstall;
use DrupalCenter\Robo\Task\Drush\SqlDrop;
use DrupalCenter\Robo\Task\Drush\UserLogin;
use DrupalCenter\Robo\Utility\PathResolver;
use Robo\Collection\Collection;
use Robo\Task\BaseTask;

/**
 * Robo task base: Install site.
 */
class Install extends BaseTask {

  /**
   * Environment.
   * 
   * @var string
   */
  protected $environment;

  /**
   * Constructor.
   *
   * @param string $environment
   *   An environment string.
   */
  public function __construct($environment) {
    $this->environment = $environment;
  }

  /**
   * Return task collection for this task.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  public function collection() {
    $collection = new Collection();
    $dump = PathResolver::databaseDump();

    // No database dump file present -> perform initial installation, export
    // configuration and create database dump file.
    if (!file_exists($dump)) {
      $collection->add([
        // Install Drupal site.
        'Install.siteInstall' => new SiteInstall(),
      ]);

      // Set up file system.
      $collection->add((new SetupFileSystem($this->environment))->collection());

      $collection->add([
        // Ensure 'config' and 'locale' module.
        'Install.enableExtensions' => new EnableExtension(['config', 'locale']),
        // Update translations.
        'Install.localeUpdate' => new LocaleUpdate(),
        // Rebuild caches.
        'Install.cacheRebuild' => new CacheRebuild(),
        // Export configuration.
        'Install.configExport' => new ConfigExport(),
        // Export database dump file.
        'Install.databaseDumpExport' => new Export($dump),
      ]);
    }

    // Database dump file already exists -> import it and update database with
    // latest exported configuration (if any).
    else {
      $collection->add([
        // Drop all tables.
        'Install.sqlDrop' => new SqlDrop(),
        // Import database dump.
        'Install.databaseDumpImport' => new Import($dump)
      ]);

      // Perform site update tasks
      $collection->add((new Update($this->environment))->collection());
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    return $this->collection()->run();
  }

}
