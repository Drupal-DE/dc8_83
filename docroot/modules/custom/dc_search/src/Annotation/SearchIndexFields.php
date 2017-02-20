<?php

namespace Drupal\dc_search\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a search index fields annotation object.
 *
 * Plugin Namespace: Plugin\SearchIndexFields
 *
 * For a working example, see \Drupal\dc_search\Plugin\SearchIndexFields\IsDiscussion
 *
 * @see \Drupal\dc_search\SearchIndexFieldsPluginManager
 * @see \Drupal\dc_search\Plugin\SearchIndexFieldsInterface
 * @see \Drupal\dc_search\Plugin\SearchIndexFieldsBase
 * @see plugin_api
 *
 * @Annotation
 */
class SearchIndexFields extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Human readable plugin name.
   *
   * @var string
   */
  public $name;

  /**
   * The plugin weight.
   *
   * @var integer
   */
  public $weight;

  /**
   * Name of field in index.
   *
   * @var string
   */
  public $field;

}
