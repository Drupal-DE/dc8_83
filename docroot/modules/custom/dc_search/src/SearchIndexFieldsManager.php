<?php

namespace Drupal\dc_search;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dc_search\SearchIndexFieldsPluginManager;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Query\Component\DisMax;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * Defines a SearchIndexFields manager.
 */
class SearchIndexFieldsManager {

  /**
   * Plugin manager service object.
   *
   * @var \Drupal\dc_search\SearchIndexFieldsPluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a SearchIndexFieldsManager object.
   */
  public function __construct(SearchIndexFieldsPluginManager $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * Add fields from SearchIndexFields plugins to a Solarium document.
   *
   * @param \Solarium\QueryType\Update\Query\Document\Document $document
   * @param \Drupal\search_api\IndexInterface $index
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function addFields(Document $document, IndexInterface $index, EntityInterface $entity) {
    foreach ($this->pluginManager->getPlugins() as $plugin) {
      if (!$plugin->applies($index, $entity)) {
        continue;
      }
      $field_name = $plugin->getFieldName();
      $value = $plugin->getValue($entity);

      // Add field to document.
      $document->addField($field_name, $value);
    }
  }

  /**
   * Add boost queries on a per-field base to a Solarium query.
   *
   * @param \Solarium\QueryType\Select\Query\Component\DisMax $dismax
   *   The DisMax component to add the boost to.
   * @param \Solarium\QueryType\Select\Query $solarium_query
   *   Solarium query to alter.
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   Current SearchAPI query.
   */
  public function addFieldBoost(DisMax $dismax, Query $solarium_query, QueryInterface $query) {
    $boost_queries = [];
    $boost_functions = [];
    foreach ($this->pluginManager->getPlugins() as $plugin) {
      $boost_query = $plugin->buildBoostQuery();
      if (!empty($boost_query['query'])) {
        // Plugins with higher weight override existing boosts.
        $boost_queries[$boost_query['key']] = $boost_query;
      }
      if ($boost_function = $plugin->buildBoostFunction()) {
        $boost_functions[] = $boost_function;
      }
    }
    if (!empty($boost_queries)) {
      // Add boost queries.
      $dismax->addBoostQueries($boost_queries);
    }
    if (!empty($boost_functions)) {
      // Add boost functions.
      $existing = $dismax->getBoostFunctions();
      $existing .= ' ' . implode(' ', $boost_functions);
      $dismax->setBoostFunctions($existing);
    }
  }

}
