<?php

namespace Drupal\dc_relation;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for relation_information service.
 */
interface RelationInformationInterface {

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
