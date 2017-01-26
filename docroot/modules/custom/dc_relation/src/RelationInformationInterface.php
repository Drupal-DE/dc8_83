<?php

namespace Drupal\dc_relation;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface for relation_information service.
 */
interface RelationInformationInterface {

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
}
