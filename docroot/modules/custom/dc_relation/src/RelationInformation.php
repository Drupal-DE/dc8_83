<?php

namespace Drupal\dc_relation;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dc_relation\RelationInformationInterface;

/**
 * General service for relation-related questions about Entity API.
 */
class RelationInformation implements RelationInformationInterface {

  /**
   * The active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new RelationInformation instance.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Current database connection.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager) {
    $this->database = $connection;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getParent(ContentEntityInterface $entity) {
    if (!($entity->hasField('field_parent'))) {
      return NULL;
    }
    return $entity->field_parent->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function isTopic(EntityInterface $entity) {
    if ('discussion' !== $entity->bundle() || !$entity->hasField('field_parent')) {
      return FALSE;
    }
    // The main topic does not have a parent.
    return $entity->get('field_parent')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getTopic(ContentEntityInterface $entity) {
    /* @var $parent \Drupal\Core\Field\FieldItemListInterface */
    while ($entity && !$entity->get('field_parent')->isEmpty()) {
      try {
        $entity = $entity->field_parent->entity;
      }
      catch (Exception $exc) {
        $entity = NULL;
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function hasAnswers(ContentEntityInterface $entity) {
    if ('discussion' !== $entity->bundle()) {
      return FALSE;
    }
    $query = $this->database->select('node__field_topic', 'p');
    $query->condition('field_topic_target_id', $entity->id());

    return !empty($query->countQuery()->execute()->fetchField());
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestAnswer($entity_id) {
    $storage = $this->entityTypeManager->getStorage('discussion_relation');
    $answers = $storage->loadByProperties([
      'topic_id' => $entity_id,
    ]);
    // Order by "updated"?
  }

  /**
   * {@inheritdoc}
   */
  public function isLatestAnswer(ContentEntityInterface $entity) {
    if ('discussion' !== $entity->bundle()) {
      return FALSE;
    }
  }

}
