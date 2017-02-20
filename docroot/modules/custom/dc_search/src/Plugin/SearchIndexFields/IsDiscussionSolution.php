<?php

namespace Drupal\dc_search\Plugin\SearchIndexFields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dc_search\Plugin\SearchIndexFields\SearchIndexFieldsBoolean;
use Drupal\search_api\IndexInterface;

/**
 * Add a search index field "Is discussion solution" to an indexed entity.
 *
 * @SearchIndexFields(
 *   id = "is_discussion_solution",
 *   name = "Content: is solution",
 *   field = "bs_is_discussion_solution",
 *   boost = "1.0",
 *   weight = 10
 * )
 */
class IsDiscussionSolution extends SearchIndexFieldsBoolean {

  /**
   * {@inheritdoc}
   */
  public function applies(IndexInterface $index, EntityInterface $entity) {
    try {
      // @todo
      return ('discussion' === $entity->bundle() && FALSE);
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
