<?php

namespace Drupal\dc_migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migrate processor annotation object.
 *
 * Plugin Namespace: Plugin\DcMigrateProcessor
 *
 * For a working example, see \Drupal\dc_migrate\Plugin\DcMigrateProcessor\DiscussionTermCategory
 *
 * @see \Drupal\dc_migrate\DcMigrateProcessorPluginManager
 * @see \Drupal\dc_migrate\Plugin\DcMigrateProcessorInterface
 * @see \Drupal\dc_migrate\Plugin\DcMigrateProcessorBase
 * @see plugin_api
 *
 * @Annotation
 */
class DcMigrateProcessor extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Description of processor.
   *
   * @var string
   */
  public $description;

  /**
   * The plugin weight.
   *
   * @var integer
   */
  public $weight;

  /**
   * (optional) Name of mapping table.
   *
   * @var string
   */
  public $mapTableName;

}
