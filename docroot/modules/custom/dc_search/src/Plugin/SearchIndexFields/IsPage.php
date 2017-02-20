<?php

namespace Drupal\dc_search\Plugin\SearchIndexFields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dc_search\Plugin\SearchIndexFields\SearchIndexFieldsBoolean;
use Drupal\search_api\IndexInterface;

/**
 * Add a search index field "Is page" to an indexed entity.
 *
 * @SearchIndexFields(
 *   id = "is_type_page",
 *   name = "Content: is page",
 *   field = "bs_is_type_page",
 *   boost = "-1.0",
 *   weight = 0
 * )
 */
class IsPage extends SearchIndexFieldsBoolean {

  /**
   * {@inheritdoc}
   */
  public function applies(IndexInterface $index, EntityInterface $entity) {
    try {
      return ('page' === $entity->bundle());
    }
    catch (\Exception $ex) {
      \Drupal::logger('SearchIndexFieldsPlugin')->warning('Failed to check if entity applies in [@plugin].', ['@plugin' => $this->getPluginId()]);
    }

    // Fallback to "do not apply".
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(EntityInterface $entity) {
    return TRUE;
  }

}
