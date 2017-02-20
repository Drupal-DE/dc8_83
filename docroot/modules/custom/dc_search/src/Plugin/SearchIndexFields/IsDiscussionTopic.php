<?php

namespace Drupal\dc_search\Plugin\SearchIndexFields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dc_search\Plugin\SearchIndexFields\SearchIndexFieldsBoolean;
use Drupal\search_api\IndexInterface;

/**
 * Add a search index field "Is discussion topic" to an indexed entity.
 *
 * @SearchIndexFields(
 *   id = "is_type_discussion_topic",
 *   name = "Content: is discussion topic",
 *   field = "bs_is_type_discussion_topic",
 *   boost = "0.5",
 *   weight = 5
 * )
 */
class IsDiscussionTopic extends SearchIndexFieldsBoolean {

  /**
   * {@inheritdoc}
   */
  public function applies(IndexInterface $index, EntityInterface $entity) {
    try {
      /* @var $service \Drupal\dc_discussion\DiscussionInformationInterface */
      $service = \Drupal::service('dc_discussion.discussion_information');
      return ('discussion' === $entity->bundle() && $service->isTopic($entity));
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
