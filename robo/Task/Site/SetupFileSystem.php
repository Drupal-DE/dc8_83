<?php

namespace DrupalCenter\Robo\Task\Site;

use DrupalCenter\Robo\Task\FileSystem\EnsurePrivateFilesDirectory;
use DrupalCenter\Robo\Task\FileSystem\EnsurePublicFilesDirectory;
use DrupalCenter\Robo\Task\FileSystem\EnsureTemporaryFilesDirectory;
use DrupalCenter\Robo\Task\FileSystem\EnsureTranslationFilesDirectory;
use Robo\Collection\Collection;
use Robo\Task\BaseTask;

/**
 * Robo task base: Set up file system.
 */
class SetupFileSystem extends BaseTask {

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

    $collection->add([
      // Ensure private files directory.
      'Setup.ensurePrivateFilesDirectory' => new EnsurePrivateFilesDirectory($this->environment),
      // Ensure public files directory.
      'Setup.ensurePublicFilesDirectory' => new EnsurePublicFilesDirectory($this->environment),
      // Ensure temporary files directory.
      'Setup.ensureTemporaryFilesDirectory' => new EnsureTemporaryFilesDirectory($this->environment),
      // Ensure translation files directory.
      'Setup.ensureTranslationFilesDirectory' => new EnsureTranslationFilesDirectory($this->environment),
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
