<?php

namespace Drupal\dc_search\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\dc_search\Plugin\SearchIndexFieldsInterface;

/**
 * Provides a base class for SearchIndexFields plugins.
 *
 * @see \Drupal\dc_search\Annotation\SearchIndexFields
 * @see \Drupal\dc_search\SearchIndexFieldsPluginManager
 * @see \Drupal\dc_search\Plugin\SearchIndexFieldsInterface
 * @see plugin_api
 */
abstract class SearchIndexFieldsBase extends PluginBase implements SearchIndexFieldsInterface {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration + ['boost' => NULL];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return $this->configuration['field'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBoostQuery() {
    if (empty($this->getBoost())) {
      return NULL;
    }
    $replacements = [
      '{field}' => $this->getFieldName(),
    ];
    $query = strtr($this->getBoostQueryString(), $replacements);
    if (empty($query)) {
      return NULL;
    }

    return [
      'key' => $this->getFieldName(),
      'query' => $query . '^' . $this->getBoost(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBoostFunction() {
    if (empty($this->getBoost())) {
      return NULL;
    }
    $replacements = [
      '{field}' => $this->getFieldName(),
    ];
    $query = strtr($this->getBoostFunctionString(), $replacements);
    if (empty($query)) {
      return NULL;
    }

    return $query . '^' . $this->getBoost();
  }

  /**
   * {@inheritdoc}
   */
  public function getBoost() {
    $config = \Drupal::config('dc_search.search_index_fields');
    if ($override = $config->get($this->getPluginId() . '.boost')) {
      return $override;
    }
    return $this->configuration['boost'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBoostQueryString() {
    $config = \Drupal::config('dc_search.search_index_fields');
    if ($override = $config->get($this->getPluginId() . '.query')) {
      return $override;
    }
    return NULL;
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

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->configuration['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->configuration['weight'];
  }

}
