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
    // Create new discussion node answer form.
    $discussion_node_add = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create([
        'type' => 'discussion',
      ]);

    $build['discussion_answer_form'] = $this->entityFormBuilder->getForm($discussion_node_add, 'discussion_answer');
    // Clean up default form / re-arrange fields.
    $build['discussion_answer_form']['book']['#access'] = FALSE;
    $build['discussion_answer_form']['advanced']['#access'] = FALSE;
    // Rebuild actions.
    // Skip dropbutton #processing.
    $build['discussion_answer_form']['actions']['#process'] = [
      [
        'Drupal\\Core\\Render\\Element\\Actions',
        'processActions',
      ],
      [
        'Drupal\\Core\\Render\\Element\\Actions',
        'processContainer',
      ],
    ];


    // Get current node from block context.
    $node = $this->getCurrentNode();

    if ($node instanceof NodeInterface) {
      // Set default value for discussion_category.
      $build['discussion_answer_form']['field_discussion_category']['widget']['#value'] = $node->field_discussion_category->target_id;
      $build['discussion_answer_form']['field_discussion_category']['widget']['#default_value'][] = $node->field_discussion_category->target_id;
      // @toDo prefill other fields.
    }

    // We don't want to show summary above body field.
    $build['discussion_answer_form']['body']['widget'][0]['summary']['#access'] = FALSE;

    // Form actions
    // We don't want to show dropbutton - only show normal buttons.
    $build['discussion_answer_form']['actions']['save']['#access'] = FALSE;
    // Just show regular submit button - unpublishing etc. will be handled
    // by administrative links.
    $build['discussion_answer_form']['actions']['submit']['#access'] = TRUE;
    // Fix styling for button
    $button_classes = ['uk-button', 'uk-button-primary', 'uk-button-small'];
    $build['discussion_answer_form']['actions']['submit']['#attributes']['class'] = $button_classes;
    $build['discussion_answer_form']['actions']['preview']['#attributes']['class'] = $button_classes;

    return $build;
  }
}
