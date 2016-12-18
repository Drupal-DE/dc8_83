<?php

namespace DrupalCenter\Robo\Utility;

/**
 * A helper class for environments.
 */
class Environment {

  /**
   * Environment: local.
   */
  const LOCAL = 'local';

  /**
   * Environment: travis.
   */
  const TRAVIS = 'travis';

  /**
   * Detect environment identifier from environment variable.
   *
   * @return string|null
   *   The environment identifier on success, otherwise NULL.
   */
  public static function detect() {
    $environment = getenv('AH_SITE_ENVIRONMENT');

    return $environment ?: NULL;
  }

  /**
   * Is Acquia environment?
   *
   * @param string $environment
   *   An environment string.
   *
   * @return bool
   *   Whether the environment is an Acquia server or not.
   */
  public static function isAcquia($environment) {
    return $environment && !in_array($environment, [
      static::LOCAL,
      static::TRAVIS,
    ]);
  }

  /**
   * Is valid environment?
   *
   * @param string $environment
   *   An environment string.
   *
   * @return bool
   *   Whether the environment is valid or not.
   */
  public static function isValid($environment) {
    return $environment && ($environment === static::LOCAL || file_exists(PathResolver::siteDirectory() . '/settings.' . $environment . '.php'));
  }

  /**
   * Needs building?
   *
   * @param $environment
   *   An environment string.
   *
   * @return bool
   *   Whether the environment has to perform builds (e.g. run 'composer install').
   */
  public static function needsBuild($environment) {
    return $environment && static::isValid($environment) && !static::isAcquia($environment);
  }

}
