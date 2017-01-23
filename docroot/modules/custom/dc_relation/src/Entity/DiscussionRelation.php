<?php

namespace Drupal\dc_relation\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\dc_relation\DcRelationInterface;

/**
 * Defines the Discussion relation entity.
 *
 * @ContentEntityType(
 *   id = "discussion_relation",
 *   label = @Translation("Discussion relation"),
 *   label_singular = @Translation("discussion relation"),
 *   label_plural = @Translation("discussion relations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count discussion relation",
 *     plural = "@count discussion relations"
 *   ),
 *   handlers = {
 *     "views_data" = "\Drupal\views\EntityViewsData",
 *   },
 *   base_table = "discussion_relation_data",
 *   data_table = "discussion_relation_field_data",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "entity" = "entity_id",
 *     "topic" = "topic_id",
 *     "parent" = "parent_id"
 *   }
 * )
 */
class DiscussionRelation extends ContentEntityBase implements DcRelationInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('Reference to the entity of this discussion relation.'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE);

    $fields['topic_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Topic ID'))
      ->setDescription(t('Reference to the top-level entity in the discussion (aka topic).'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE);

    $fields['parent_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent entity ID'))
      ->setDescription(t('Reference to the parent entity of this discussion relation.'))
      ->setSetting('target_type', 'node')
      ->setRequired(FALSE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the item was last edited.'));

    return $fields;
  }

  /**
   * Creates or updates a discussion relation whilst saving that discussion.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The discussion node to update or create the relation for.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   */
  public static function updateOrCreateFromEntity(EntityInterface $entity) {
    if ('discussion' !== $entity->bundle()) {
      return;
    }
    /* @var $service \Drupal\dc_relation\RelationInformationInterface */
    $service = \Drupal::service('dc_relation.relation_information');
    $topic = $service->getTopic($entity);
    $parent = $service->getParent($entity);

    if (empty($topic)) {
      // Something went wrong. Better skip.
      return;
    }

    // Set parent to the entity itself.
    if (!$parent instanceof EntityInterface) {
      $parent = $entity;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('discussion_relation');
    $properties = [
      'entity_id' => $entity->id(),
      'topic_id' => $topic->id(),
      'parent_id' => $parent->id(),
    ];
    $entities = $storage->loadByProperties($properties);
    // We need the first result (which should be the only one).
    /* @var $relation DiscussionRelation */
    $relation = reset($entities);
    if (!($relation instanceof DcRelationInterface)) {
      // Create new entity.
      $relation = $storage->create([
        'entity_id' => $entity->id(),
      ]);
    }

    // Set entity values.
    $relation->set('topic_id', $topic->id());
    $relation->set('parent_id', $parent->id());
    $relation->set('changed', $entity->changed->value);

    // Save relation entity.
    return $relation->save();
  }

}
