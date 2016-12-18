<?php

namespace DrupalCenter\Robo\Task\Site;

use DrupalCenter\Robo\Task\Settings\EnsureSettingsFile;
use DrupalCenter\Robo\Utility\Environment;
use DrupalCenter\Robo\Utility\PathResolver;
use Robo\Collection\Collection;
use Robo\Task\BaseTask;
use Robo\Task\Composer\Install as ComposerInstall;

/**
 * Robo task base: Initialize site.
 */
class Initialize extends BaseTask {

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

    // Is valid environment?
    if (!Environment::isValid($this->environment)) {
      throw new \InvalidArgumentException(get_class($this) . ' - Unknown environment: ' . $this->environment);
    }
  }

  /**
   * Return task collection for this task.
   *
   * @return \Robo\Collection\Collection
   *   The task collection.
   */
  public function collection() {
    $collection = new Collection();

    // Build has to be performed?
    if (Environment::needsBuild($this->environment)) {
      $collection->add([
        // Run 'composer install'.
        'Initialize.composerInstall' => (new ComposerInstall())
          ->dir(PathResolver::root())
          ->option('optimize-autoloader'),
      ]);
    }

    $collection->add([
      // Ensure settings file for environment.
      'Initialize.ensureSettingsFile' => new EnsureSettingsFile($this->environment),
    ]);

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    return $this->collection()->run();
  }

}
