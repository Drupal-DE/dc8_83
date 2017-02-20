<?php

namespace Drupal\dc_search\Plugin\SearchIndexFields;

use Drupal\dc_search\Plugin\SearchIndexFieldsBase;

/**
 * Provides a base class for boolean SearchIndexFields plugins.
 */
abstract class SearchIndexFieldsBoolean extends SearchIndexFieldsBase {

  /**
   * {@inheritdoc}
   */
  public function getBoostQueryString() {
    $config = \Drupal::config('dc_search.search_index_fields');
    if ($override = $config->get($this->getPluginId() . '.query')) {
      return $override;
    }
    return '{field}:true';
  }

  /**
   * {@inheritdoc}
   */
  public function getBoostFunctionString() {
    $config = \Drupal::config('dc_search.search_index_fields');
    if ($override = $config->get($this->getPluginId() . '.function')) {
      return $override;
    }
    return NULL;
  }

}
