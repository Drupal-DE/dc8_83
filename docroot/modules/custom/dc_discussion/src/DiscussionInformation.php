<?php

namespace Drupal\dc_discussion;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dc_discussion\DiscussionInformationInterface;
use Drupal\dc_relation\RelationInformationInterface;
use Drupal\node\Entity\Node;

/**
 * General service for discussion-related questions about Entity API.
 */
class DiscussionInformation implements DiscussionInformationInterface {

  /**
   * The active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  public $database;

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
    $query->condition('h.uid', $uid);

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
  public function isTopic(ContentEntityInterface $entity) {
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
    if ('discussion' !== $entity->bundle() || !$entity->hasField('field_topic')) {
      return NULL;
    }

    return $this->isTopic($entity) ? $entity : $entity->field_topic->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function hasAnswers(ContentEntityInterface $entity) {
    if ('discussion' !== $entity->bundle()) {
      return FALSE;
    }
    // Find all items with parent set to the given entity.
    $query = $this->database->select('node__field_parent', 'p');
    $query->condition('field_parent_target_id', $entity->id());

    return !empty($query->countQuery()->execute()->fetchField());
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswers($entity_id, $tree = TRUE, $load = TRUE) {
    $answers = [];

    try {
      // Find all items with parent set to the given entity.
      $query = $this->database->select('node__field_parent', 'p');
      $query->condition('field_parent_target_id', $entity_id);
      $query->join('node_field_data', 'n', 'n.nid = p.entity_id');
      $query->addField('n', 'nid');
      $query->addField('n', 'changed');
      $query->orderBy('n.changed', 'DESC');

      $answers = $query->execute()->fetchAllAssoc('nid');
      // Load entities if necessary.
      if (!empty($answers) && $load) {
        $answers = Node::loadMultiple(array_keys($answers));
      }

      if ($tree) {
        foreach (array_keys($answers) as $nid) {
          // Merge child answers while preserve numeric keys in array.
          $answers += $this->getAnswers($nid);
        }
      }

      // Sort by "changed" property.
      uasort($answers, function($a, $b) use ($load) {
        // Sort descending.
        if ($load) {
          return strcmp($b->getChangedTime(), $a->getChangedTime());
        }
        return strcmp($b->changed, $a->changed);
      });
    }
    catch (Exception $ex) {
      \Drupal::logger('dc_discussion')->warning('Failed to load answers for discussion @id', ['@id' => $entity_id]);
      return [];
    }

    return $answers;
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestAnswer($entity_id) {
    // Find all items with parent set to the given entity.
    $query = $this->database->select('node__field_parent', 'p');
    $query->condition('p.field_parent_target_id', $entity_id);
    $query->join('node_field_data', 'n', 'n.nid = p.entity_id');
    $query->addField('n', 'nid');
    $query->orderBy('n.changed', 'DESC');
    // Limit to 1 result.
    $query->range(0, 1);

    return Node::load($query->execute()->fetchField());
  }

  /**
   * {@inheritdoc}
   */
  public function isLatestAnswer(ContentEntityInterface $entity) {
    if ('discussion' !== $entity->bundle()) {
      // How dare you!
      return FALSE;
    }
    if ($this->isTopic($entity) && !$this->hasAnswers($entity)) {
      // Without answers a topic is basically the latest answer to itself.
      return TRUE;
    }
    $topic = $this->getTopic($entity);
    if (($latest_answer = $this->getLatestAnswer($topic->id())) === FALSE) {
      // Something went wrong so better not do anything.
      return FALSE;
    }

    return $latest_answer->id() === $entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function answerCount(ContentEntityInterface $entity) {
    if ($this->isTopic($entity)) {
      // Count all answers to this topic.
      $query = $this->database->select('node__field_topic', 't');
      $query->condition('t.field_topic_target_id', $entity->id());

      return $query->countQuery()->execute()->fetchField();
    }
    // Count direct answers to a discussion answer.
    $query = $this->database->select('node__field_parent', 'p');
    $query->condition('p.field_parent_target_id', $entity->id());

    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAnswers(ContentEntityInterface $entity) {
    if (!$this->hasAnswers($entity)) {
      return;
    }
    // Load direct answers only. Answers on answers will be deleted recursively.
    $answers = $this->getAnswers($entity->id(), FALSE);
    /* @var $storage \Drupal\node\NodeStorageInterface */
    $storage = $this->entityTypeManager->getStorage('node');
    $storage->delete($answers);
  }

}
