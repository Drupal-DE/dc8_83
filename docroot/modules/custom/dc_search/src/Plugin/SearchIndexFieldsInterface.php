<?php

namespace Drupal\dc_search\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\search_api\IndexInterface;

/**
 * Defines the interface for search index fields plugins.
 *
 * @see \Drupal\dc_search\Annotation\SearchIndexFields
 * @see \Drupal\dc_search\SearchIndexFieldsPluginManager
 * @see plugin_api
 */
interface SearchIndexFieldsInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Whether the given entity is applicable for processing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity which is requested to be handled by the plugin.
   *
   * @return boolean
   *   TRUE if the entity can be handled by the plugin, FALSE otherwise.
   */
  public function applies(IndexInterface $index, EntityInterface $entity);

  /**
   * Get the field value to add to the indexed item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Original entity.
   *
   * @return string
   *   The fields value to add to the indexed item.
   */
  public function getValue(EntityInterface $entity);

  /**
   * Get the name of the field to add to the indexed item.
   *
   * @return string
   *   The name of the field.
   */
  public function getFieldName();

  /**
   * Get additional field boost.
   *
   * @return float|NULL
   *   Additional boost for the field.
   */
  public function getBoost();

  /**
   * Get the fields boost query string.
   *
   * @return string
   *   The boost query for the field.
   */
  public function getBoostQueryString();

  /**
   * Build the fields boost query.
   *
   * @return array
   *   Structured array containing information for a BoostQuery.
   */
  public function buildBoostQuery();

  /**
   * Get the fields boost function string.
   *
   * @return string
   *   The boost function for the field.
   */
  public function getBoostFunctionString();

  /**
   * Build the fields boost function.
   *
   * @return string
   *   The BoostFunction definition.
   *
   * @see \Solarium\QueryType\Select\Query\Component\DisMax::setBoostFunctions()
   */
  public function buildBoostFunction();

  /**
   * Get the name of the plugin.
   *
   * @return string
   *   Plugin name.
   */
  public function getName();

  /**
   * Get the weight of the plugin.
   *
   * @return int
   *   Plugin weight.
   */
  public function getWeight();
}
