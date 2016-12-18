<?php

namespace DrupalCenter\Robo\Utility;

use Symfony\Component\Filesystem\Filesystem;

/**
 * A helper class for path resolving.
 */
class PathResolver {

  /**
   * Return path exported configration.
   *
   * @return string
   *   The path to the exported Drupal configuration files.
   */
  public static function config() {
    return static::root() . '/config/sync';
  }

  /**
   * Return database dump path.
   *
   * @return string
   *   The path to the 'project.sql' database dump file.
   */
  public static function databaseDump() {
    return static::root() . '/database/project.sql';
  }

  /**
   * Return docroot path.
   *
   * @return string
   *   The path to the Drupal docroot.
   */
  public static function docroot() {
    return static::root() . '/docroot';
  }

  /**
   * Return Drush binary path.
   *
   * @return string
   *   The path to the Drush binary.
   */
  public static function drush() {
    // Use 'drush8' binary in Acquia environments.
    if (Environment::isAcquia(Environment::detect())) {
      return 'drush8';
    }

    // Use Drush binary from Composer vendor directory for all other
    // environments.
    else {
      return static::root() . '/bin/drush';
    }
  }

  /**
   * Initialize path resolver.
   *
   * @param string $root
   *   The root path to use.
   */
  public static function init($root) {
    static::rootCache($root);
  }

  /**
   * Return root path.
   *
   * @return string
   *   The path to the project root.
   */
  public static function root() {
    return static::rootCache();
  }

  /**
   * Cache root path in global variable.
   *
   * @param null|string $root
   *   The root path to cache.
   *
   * @return string
   *   The cached root path.
   *
   * @throws \Exception
   */
  protected static function rootCache($root = NULL) {
    $cid = '__DRUPALCENTER_ROBO_ROOT__';

    if (isset($root) && !empty($root)) {
      if (isset($GLOBALS[$cid]) && !empty($GLOBALS[$cid])) {
        throw new \Exception(__CLASS__ . ' - Is already initialized.');
      }

      $GLOBALS[$cid] = $root;
    }

    if (!isset($GLOBALS[$cid]) || empty($GLOBALS[$cid])) {
      throw new \Exception(__CLASS__ . ' - Not initialized.');
    }

    return $GLOBALS[$cid];
  }

  /**
   * Return private files directory path.
   *
   * @return string|null
   *   If set, the absolute path to the private files directory of Drupal,
   *   otherwise NULL.
   */
  public static function privateFilesDirectory() {
    if (($path = Drupal::privateFilesDirectory())) {
      return static::absolute($path);
    }

    return NULL;
  }

  /**
   * Return public files directory path.
   *
   * @return string
   *   The absolute path to the public files directory of Drupal.
   */
  public static function publicFilesDirectory() {
    return static::absolute(Drupal::publicFilesDirectory());
  }

  /**
   * Return site directory path.
   *
   * @return string
   *   The path to the site directory of Drupal.
   */
  public static function siteDirectory() {
    return static::docroot() . '/sites/default';
  }

  /**
   * Return temporary files directory path.
   *
   * @return string
   *   The absolute path to the temporary files directory of Drupal.
   */
  public static function temporaryFilesDirectory() {
    return static::absolute(Drupal::temporaryFilesDirectory());
  }

  /**
   * Return translation files directory path.
   *
   * @return string
   *   The path to the translation files directory of Drupal.
   */
  public static function translationFilesDirectory() {
    return static::publicFilesDirectory() . '/translations';
  }

  /**
   * Return absolute path.
   *
   * This makes relative paths absolute using the docroot path as base.
   *
   * @param $path
   *   The path to make absolute.
   *
   * @return string
   *   The absolute path.
   */
  protected static function absolute($path) {
    $fs = new Filesystem();

    // Make path absolute (if not already).
    if (!$fs->isAbsolutePath($path)) {
      $path = realpath(static::docroot() . '/' . $path) ?: static::docroot() . '/' . $path;
    }

    return $path;
  }

}
