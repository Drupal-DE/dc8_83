<?php

namespace Drupal\dc_discussion\Plugin\Block;

use Drupal\Core\Block\BlockBase;

use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 *
 * Provides a 'DiscussionAnswer' block.
 *
 * @Block(
 *   id = "discussion_answer",
 *   admin_label = @Translation("Discussion answer"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node")
 *     )
 *   }
 * )
 */
class DiscussionAnswer extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Get current node from block context.
   *
   * @return mixed
   */
  protected function getCurrentNode() {
    return $this->getContextValue('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.form_builder')
    );
  }


  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFormBuilder $entity_form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['discussion_answer']['#markup'] = 'Implement DiscussionAnswer.';
    // Create new discussion node answer form.
    $discussion_node_add = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create([
        'type' => 'discussion',
      ]);

    $build['discussion_answer_form'] = $this->entityFormBuilder->getForm($discussion_node_add);
    // Hide some fields
    // Get current node from block context.
    $node = $this->getCurrentNode();

    if ($node instanceof NodeInterface) {
      // Set default value for discussion_category.
      $build['discussion_answer_form']['field_discussion_category']['widget']['#value'][] = $node->field_discussion_category->target_id;
      $build['discussion_answer_form']['field_discussion_category']['widget']['#default_value'][] = $node->field_discussion_category->target_id;
      // @toDo prefill other fields.
    }
    return $build;
  }
}
