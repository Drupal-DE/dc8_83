<?php

namespace DrupalCenter\Robo;

use Robo\Result;
use DrupalCenter\Robo\Utility\Drupal;
use DrupalCenter\Robo\Utility\Environment;
use DrupalCenter\Robo\Utility\PathResolver;

/**
 * Console commands configuration base for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFileBase extends \Robo\Tasks {

  use \DrupalCenter\Robo\Task\DatabaseDump\loadTasks;
  use \DrupalCenter\Robo\Task\Drush\loadTasks;
  use \DrupalCenter\Robo\Task\FileSystem\loadTasks;
  use \DrupalCenter\Robo\Task\Npm\loadTasks;
  use \DrupalCenter\Robo\Task\Settings\loadTasks;
  use \DrupalCenter\Robo\Task\Site\loadTasks;

  /**
   * Constructor.
   */
  public function __construct() {
    $reflection = new \ReflectionClass($this);

    // Initialize path resolver.
    PathResolver::init(dirname($reflection->getFileName()));
  }

  /**
   * Update project database dump.
   *
   * This command refreshes the 'project.sql' database dump file with all latest
   * changes (e.g. config updates).
   *
   * @param string $environment An environment string.
   *
   * @return Result|null
   *   The command result.
   */
  public function dumpUpdate($environment) {
    // Show notice fro dropped database tables.
    $this->yell('!!! All database tables will be dropped - This action cannot be undone !!!', 40, 'red');

    // Ask for confirmation.
    $continue = $this->confirm('Are you sure you want to continue');

    if ($continue) {
      $collection = $this->dumpUpdateCollection($environment);
      return $collection->run();
    }

    return NULL;
  }

  /**
   * Return task collection for 'dump:update' command.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  protected function dumpUpdateCollection($environment) {
    $dump = PathResolver::databaseDump();
    $collection = $this->collection();

    // Initialize site.
    $collection->add($this->taskSiteInitialize($environment)->collection());

    $collection->add([
      // Drop all database tables.
      'Base.sqlDrop' => $this->taskDrushSqlDrop(),
      // Import database.
      'Base.databaseDumpImport' => $this->taskDatabaseDumpImport($dump),
    ]);

    // Perform update tasks.
    $collection->add($this->taskSiteUpdate($environment)->collection());

    $collection->add([
      // Export database.
      'Base.databaseDumpExport' => $this->taskDatabaseDumpExport($dump),
    ]);

    return $collection;
  }

  /**
   * Install site.
   *
   * If a 'project.sql' database dump file is availble, the site will be
   * installed using that dump file and all exported configuration (if any).
   *
   * If there is no 'project.sql' file available, the site is installed from
   * scratch, the database dump file and all configuration is exported
   * afterwards.
   *
   * @param string $environment An environment string.
   * @param array $opts
   *
   * @option $force Force site install. This will drop all database tables and
   *   re-install the site, if it is already installed.
   *
   * @return Result|null
   *   The command result.
   */
  public function siteInstall($environment, $opts = ['force' => FALSE]) {
    // Already installed -> Abort.
    if (Drupal::isInstalled()) {
      $continue = FALSE;

      // Preform re-install?
      if ($opts['force']) {
        $this->yell('!!! All data will be lost - This action cannot be undone !!!', 40, 'red');

        // Ask for confirmation.
        $continue = $this->confirm('Are you sure you want to continue');
      }

      if (!$continue) {
        $this->yell('Site is already installed', 40, 'red');
        $this->say('Run <fg=yellow>site:update</fg=yellow> command instead.');

        return NULL;
      }
    }

    // Not installed -> run tasks.
    $collection = $this->siteInstallCollection($environment);

    return $collection->run();
  }

  /**
   * Return task collection for 'site:install' command.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  protected function siteInstallCollection($environment) {
    $collection = $this->collection();

    // Initialize site.
    $collection->add($this->taskSiteInitialize($environment)->collection());

    // Install site.
    $collection->add($this->taskSiteInstall($environment)->collection());

    return $collection;
  }

  /**
   * Update site.
   *
   * Runs all update tasks on the site.
   *
   * @param string $environment An environment string.
   * @param array $opts
   *
   * @option $maintenance-mode Take site offline during site update.
   *
   * @return Result|null
   *   The command result.
   */
  public function siteUpdate($environment, $opts = ['maintenance-mode' => FALSE]) {
    // Not installed -> Abort.
    if (!Drupal::isInstalled()) {
      $this->yell('Site is not installed', 40, 'red');
      $this->say('Run <fg=yellow>site:install</fg=yellow> command instead.');
    }

    // Installed -> run tasks.
    else {
      $collection = $this->collection();

      // Take site offline (if --maintenance-mode option is set).
      if ($opts['maintenance-mode']) {
        $collection->add([
          'Update.enableMaintenanceMode' => $this->taskSiteMaintenanceMode(TRUE)
        ]);
      }

      // Perform update tasks.
      $collection->add($this->siteUpdateCollection($environment));

      // Bring site back online (if --maintenance-mode option is set).
      if ($opts['maintenance-mode']) {
        $collection->add([
          'Update.disableMaintenanceMode' => $this->taskSiteMaintenanceMode(FALSE)
        ]);
      }

      return $collection->run();
    }

    return NULL;
  }

  /**
   * Return task collection for 'site:update' command.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  protected function siteUpdateCollection($environment) {
    $collection = $this->collection();

    // Perform basic setup.
    $collection->add($this->taskSiteInitialize($environment)->collection());

    // Update site.
    $collection->add($this->taskSiteUpdate($environment)->collection());

    return $collection;
  }

  /**
   * Compile a theme.
   *
   * @param string $environment
   *   An environment string.
   * @param string $path
   *   The path to the theme.
   *
   * @return Result|null
   *   The command result.
   */
  public function themeCompile($environment, $path) {
    // Do not allow in Acquia environments
    if (Environment::isAcquia($environment)) {
      $this->yell('Must not be executed in Aqcuia environments', 40, 'red');

      return NULL;
    }

    // Theme path not found.
    elseif (!file_exists($path) || !is_dir($path) || !file_exists(rtrim($path, '/') . '/package.json')) {
      $this->yell('Theme path not found or invalid: ' . $path, 40, 'red');

      return NULL;
    }

    $collection = $this->themeCompileCollection($environment, $path);

    return $collection->run();
  }

  /**
   * Return task collection for 'theme:compile' command.
   *
   * @param string $environment
   *   An environment string.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  public function themeCompileCollection($environment, $path) {
    $collection = $this->collection();

    $collection->add([
      // Run 'npm install'.
      'Theme.npmInstall' => $this->taskNpmInstall('npm')->dir($path),
      // Run 'npm run compile'.
      'Theme.npmRunCompile' => $this->taskNpmRun('npm')->dir($path)->arg('compile'),
    ]);

    return $collection;
  }

}
