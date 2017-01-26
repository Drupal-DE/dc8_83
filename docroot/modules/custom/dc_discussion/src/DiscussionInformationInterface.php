<?php

namespace Drupal\dc_discussion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for discussion_information service.
 */
interface DiscussionInformationInterface {

  /**
   * Get list of unread discussions a user participated in.
   *
   * @param int $uid
   *   User ID.
   * @param int $limit
   *   Number of items to return.
   * @param string $sort
   *   Whether to sort the results by oldest first or newest first ('DESC').
   *
   * @return array
   *   Array of query results with updated discussions the given user
   *   participated in.
   */
  public function getUnreadForUser($uid = NULL, $limit = 10, $sort = 'DESC');

  /**
   * Determines if an entity is a main topic.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that may be a top-level item.
   *
   * @return bool
   *   TRUE if this entity is a top-level item, FALSE otherwise.
   */
  public function isTopic(EntityInterface $entity);

  /**
   * Loads the main topic of a relation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The main topic or NULL, if the entity doesn't exist.
   */
  public function getTopic(ContentEntityInterface $entity);

  /**
   * Loads the parent of a relation item.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The parent entity or NULL, if the entity doesn't exist.
   */
  public function getParent(ContentEntityInterface $entity);

  /**
   * Loads all answers of a specific discussion item.
   *
   * @param int $entity_id
   *   The entity ID.
   * @param boolean $tree
   *   (Optional) Load answers of answers also. Defaults to TRUE.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   List of answers ordered by changed-date or NULL, if the entity doesn't
   *   exist.
   */
  public function getAnswers($entity_id, $tree = TRUE);

  /**
   * Loads the latest answer of a specific relation item.
   *
   * @param int $entity_id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The latest answer or NULL, if the entity doesn't exist.
   */
  public function getLatestAnswer($entity_id);

  /**
   * Determines if an entity is a latest answer of a relation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A content entity.
   *
   * @return bool
   *   TRUE if the specified object is the latest answer of a relation,
   *   FALSE otherwise.
   */
  public function isLatestAnswer(ContentEntityInterface $entity);

  /**
   * Determines if the entity has answers.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity which may or may not have answers.
   *
   * @return bool
   *   TRUE if this entity has answers, FALSE otherwise.
   */
  public function hasAnswers(ContentEntityInterface $entity);
}
