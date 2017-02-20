<?php

namespace Drupal\dc_search\Plugin\SearchIndexFields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dc_search\Plugin\SearchIndexFields\SearchIndexFieldsBoolean;
use Drupal\search_api\IndexInterface;

/**
 * Add a search index field "Is discussion" to an indexed entity.
 *
 * @SearchIndexFields(
 *   id = "is_type_discussion",
 *   name = "Content: is discussion",
 *   field = "bs_is_type_discussion",
 *   boost = "2.0",
 *   weight = 0
 * )
 */
class IsDiscussion extends SearchIndexFieldsBoolean {

  /**
   * {@inheritdoc}
   */
  public function applies(IndexInterface $index, EntityInterface $entity) {
    try {
      return ('discussion' === $entity->bundle());
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
