<?php

namespace Drupal\dc_search\Plugin\Search;

use Drupal\fac\Entity\FacConfig;
use Drupal\fac\Plugin\Search\SearchApiSearch;

/**
 * Provides a basic title search plugin.
 *
 * @Search(
 *   id = "filtered_searchapi_search",
 *   name = @Translation("filterable SearchAPI search plugin"),
 * )
 */
class FilteredSearchApiSearch extends SearchApiSearch {

  /**
   * Gets the configuration form for the search plugin.
   *
   * @param array $plugin_config
   *   The plugin config array.
   *
   * @return array
   *   The configuration form for the search plugin.
   */
  public function getConfigForm(array $plugin_config) {
    $form = parent::getConfigForm($plugin_config);

    $query = $this->entityQuery->get('search_api_index');
    $index_ids = $query->execute();
    $indexes = $this->storage->getStorage('search_api_index')->loadMultiple($index_ids);

    /* @var $index \Drupal\search_api\IndexInterface */
    foreach ($indexes as $index) {
      foreach ($index->getDatasources() as $datasource) {
        $config = $datasource->getConfiguration();
        $bundles = $config['bundles']['selected'];
        $form['bundles'][$index->id()][$datasource->getPluginId()] = [
          '#type' => 'checkboxes',
          '#options' => array_combine($bundles, $bundles),
          '#title' => $this->t('Bundles'),
          '#default_value' => isset($plugin_config['bundles'][$index->id()][$datasource->getPluginId()]) ? $plugin_config['bundles'][$index->id()][$datasource->getPluginId()] : [],
          '#description' => $this->t('Select the bundles to search in.'),
        ];
      }
    }

    return $form;
  }

  /**
   * Return the results for the given key.
   *
   * @param FacConfig $fac_config
   *   The FacConfig entity.
   * @param string $key
   *   The query string to get results for.
   *
   * @return array
   *   The result entity ids for the given key.
   */
  public function getResults(FacConfig $fac_config, $key) {
    $results = [];

    $plugin_config = $fac_config->getSearchPluginConfig();
    $index_id = $plugin_config['index'];
    $index = $this->storage->getStorage('search_api_index')->load($index_id);
    /* @var $query \Drupal\search_api\Query\QueryInterface */
    $query = $this->queryHelper->createQuery($index);
    $query->keys($key)
      ->range(0, $fac_config->getNumberOfResults());

    foreach ($plugin_config['bundles'][$index_id] as $datasource) {
      $bundles = array_filter($datasource);
      if ($bundles) {
        $query->addCondition('type', array_values($bundles), 'IN');
      }
    }

    $items = $query->execute()->getResultItems();

    foreach ($items as $item) {
      $entity = $item->getOriginalObject()->getValue();

      $results[] = [
        'entity_type' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
      ];
    }

    return $results;
  }

}
