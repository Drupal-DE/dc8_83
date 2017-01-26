<?php

namespace Drupal\dc_discussion;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dc_discussion\DiscussionInformationInterface;
use Drupal\dc_relation\RelationInformationInterface;

/**
 * General service for discussion-related questions about Entity API.
 */
class DiscussionInformation implements DiscussionInformationInterface {

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
   * Relation information service.
   *
   * @var \Drupal\dc_relation\RelationInformationInterface
   */
  protected $relationInformation;

  /**
   * Creates a new DiscussionInformation instance.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Current database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\dc_relation\RelationInformationInterface $relation_information
   *   The relation information service.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager, RelationInformationInterface $relation_information) {
    $this->database = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->relationInformation = $relation_information;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnreadForUser($uid = NULL, $limit = 10, $sort = 'DESC') {
    if (empty($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    $query = $this->database
      ->select('node_field_data', 'n');
    $query->join('node__field_topic', 't', 't.entity_id = n.nid');
    $query->join('discussion_relation_data', 'r', 'r.topic_id = t.field_topic_target_id');
    $query->join('node_field_data', 'nt', 'r.topic_id = nt.nid');
    $query->leftJoin('history', 'h', 'h.nid = n.nid');
    $query->distinct();

    $query->addField('nt', 'nid');
    $query->addField('nt', 'title');
    $query->addField('r', 'changed');
    $query->addField('r', 'id', 'relation_id');

    $changed_limit = REQUEST_TIME - (REQUEST_TIME - HISTORY_READ_LIMIT);
    // Add conditions.
    $group_and = $query->andConditionGroup()
      ->isNull('h.timestamp')
      ->condition('r.changed', $changed_limit, '>');
    $group_or = $query->orConditionGroup()
      ->condition($group_and)
      ->where('h.timestamp < r.changed');
    $query->condition($group_or);
    // Filter for user;
    $query->condition('n.uid', $uid);

    // Sort results.
    $query->orderBy('r.changed', $sort);

    // Limit resultset.
    $query->range(0, $limit);

    return $query->execute()->fetchAll();
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
  public function getAnswers($entity_id, $tree = TRUE) {
    return [];
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
