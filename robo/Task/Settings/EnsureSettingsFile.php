<?php

namespace DrupalCenter\Robo\Task\Settings;

use DrupalCenter\Robo\Utility\Environment;
use DrupalCenter\Robo\Utility\PathResolver;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Task\File\Write;

/**
 * Robo task base: Ensure settings file for environment.
 */
class EnsureSettingsFile extends BaseTask {

  /**
   * Database: Host.
   *
   * @var string
   */
  protected $db_host;

  /**
   * Database: Name.
   *
   * @var string
   */
  protected $db_name;

  /**
   * Database: Port.
   *
   * @var string
   */
  protected $db_port;

  /**
   * Database: User.
   *
   * @var string
   */
  protected $db_user;

  /**
   * Database: Password.
   *
   * @var string
   */
  protected $db_pass;

  /**
   * Environment.
   *
   * @var string
   */
  protected $environment;

  /**
   * File path.
   *
   * @var string
   */
  protected $file;

  /**
   * Constructor.
   *
   * @param string $environment
   *   An environment string.
   */
  public function __construct($environment) {
    $this->environment = $environment;
    $this->file = PathResolver::siteDirectory() . '/settings.' . $this->environment . '.php';
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    if (!$this->skip()) {
      $this->say('Settings file not available: ' . $this->file);
      $this->say('Let\'s create one...');

      $this->db_host = $this->askDefault('Host', 'localhost');
      $this->db_port = $this->askDefault('Port', '3306');
      $this->db_name = $this->askDefault('Database', 'dcd8');
      $this->db_user = $this->askDefault('User', 'root');
      $this->db_pass = $this->askDefault('Password', '');

      return (new Write($this->file))
        ->lines($this->lines())
        ->run();
    }

    return Result::success($this);
  }

  /**
   * Assemble settings file source code lines.
   *
   * @return array
   *   An array of source code lines.
   */
  protected function lines() {
    $lines = array_merge(
      ['<?php'],
      $this->linesFileComment(),
      $this->linesOverrides(),
      $this->linesDatabaseConnection()
    );

    return $lines;
  }

  /**
   * Assemble settings file source code lines for database connection.
   *
   * @return array
   *   An array of source code lines.
   */
  protected function linesDatabaseConnection() {
    $lines = [
      '',
      "\$databases['default']['default'] = array(",
      "  'database' => '" . $this->db_name . "',",
      "  'username' => '" . $this->db_user . "',",
      "  'password' => '" . $this->db_pass . "',",
      "  'host' => '" . $this->db_host . "',",
      "  'port' => '" . $this->db_port . "',",
      "  'driver' => 'mysql',",
      "  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',",
      ");",
    ];

    return $lines;
  }

  /**
   * Assemble settings file source code lines for file comment.
   *
   * @return array
   *   An array of source code lines.
   */
  protected function linesFileComment() {
    $lines = [
      '',
      '/**',
      ' * @file',
      ' * Settings for \'' . $this->environment . '\' environment.',
      ' *',
      ' * This file was generated automatically during Robo execution, but you may',
      ' * alter it to suite your needs.',
      ' */',
    ];

    return $lines;
  }

  /**
   * Assemble settings file source code lines for settings/config overrides.
   *
   * @return array
   *   An array of source code lines.
   */
  protected function linesOverrides() {
    $lines = [];

    // Add specific settings for local environment.
    if ($this->environment === Environment::LOCAL) {
      $lines = array_merge($lines, [
        '',
        '// Enable development services.',
        '$settings[\'container_yamls\'][] = DRUPAL_ROOT . \'/sites/development.services.yml\';',
        '',
        '// Disable render cache.',
        '$settings[\'cache\'][\'bins\'][\'render\'] = \'cache.backend.null\';',
        '',
        '// Disable CSS/JS aggregation.',
        '$config[\'system.performance\'][\'css\'][\'preprocess\'] = FALSE;',
        '$config[\'system.performance\'][\'js\'][\'preprocess\'] = FALSE;',
        '',
        '// Set error level to verbose.',
        '$config[\'system.logging\'][\'error_level\'] = \'verbose\';',
        '',
        '// Set temporary folder.',
        '$config[\'system.file\'][\'path.temporary\'] = \'../tmp\';',
        '',
        '// Set private folder.',
        '$settings[\'file_private_path\'] = \'../private\';',
        '',
        '// Set migration folder.',
        '$settings[\'file_dc_migration_path\'] = \'../migration\';',
        '',
        '/**',
        ' * Override Search API settings.',
        ' */',
        '$config[\'search_api.index.acquia_search_index\'][\'server\'] = \'local\';',
      ]);
    }

    return $lines;
  }

  /**
   * Task should be skipped?
   *
   * @return bool
   *   Whether the task should be skipped or not?
   */
  protected function skip() {
    return file_exists($this->file);
  }

}
