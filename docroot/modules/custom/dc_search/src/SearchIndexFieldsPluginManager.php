<?php

namespace Drupal\dc_search;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages search index fields plugins.
 *
 * @see hook_dc_search_index_fields_info_alter()
 * @see \Drupal\dc_search\Annotation\SearchIndexFields
 * @see \Drupal\dc_search\Plugin\SearchIndexFieldsInterface
 * @see plugin_api
 */
class SearchIndexFieldsPluginManager extends DefaultPluginManager {

  /**
   * Constructs a SearchIndexFieldsPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/SearchIndexFields', $namespaces, $module_handler, 'Drupal\dc_search\Plugin\SearchIndexFieldsInterface', 'Drupal\dc_search\Annotation\SearchIndexFields');
    $this->alterInfo('dc_search_index_fields_info');
    $this->setCacheBackend($cache_backend, 'search_index_fields_plugins');
  }

  /**
   * Get a list of all registered plugin instances.
   *
   * @return \Drupal\dc_search\Plugin\SearchIndexFieldsInterface[]
   *   List of loaded plugin instances.
   */
  public function getPlugins() {
    $instances = &drupal_static(__FUNCTION__, []);
    if (empty($instances)) {
      // Get registered plugins.
      $plugins = $this->getDefinitions();
      uasort($plugins, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
      foreach ($plugins as $plugin_id => $plugin) {
        // Create instance of plugin.
        $instances[$plugin_id] = $this->createInstance($plugin_id, $plugin);
      }
    }

    return $instances;
  }

  /**
   * Get a single plugin instance.
   *
   * @param string $plugin_id
   *   ID of plugin to load.
   *
   * @return \Drupal\dc_search\Plugin\SearchIndexFieldsInterface
   *   The loaded plugin or NULL it a plugin with the given ID does not exist.
   */
  public function getPlugin($plugin_id) {
    $plugins = $this->getPlugins();
    return isset($plugins[$plugin_id]) ? $plugins[$plugin_id] : NULL;
  }

}
