<?php

namespace Drupal\dc_discussion\Plugin\Block;

use Drupal\Core\Block\BlockBase;

use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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

    // We don't want to show summary above body field.
    $build['discussion_answer_form']['body']['widget'][0]['summary']['#access'] = FALSE;

    // Form actions
    $button_classes = ['uk-button', 'uk-button-primary', 'uk-button-small'];
    // Skip pre-rendering of save actions - we don't want a drop button here.
    $build['discussion_answer_form']['actions']['save']['#pre_render'] = [];
    $build['discussion_answer_form']['actions']['save']['#type'] = 'container';
    $build['discussion_answer_form']['actions']['save']['#theme'] = 'links';
    // Update styling of publish button.

    $build['discussion_answer_form']['actions']['publish']['#attributes']['class'] = $button_classes;
    // Remove unpublish button.
    unset($build['discussion_answer_form']['actions']['save']['#links']['unpublish']);

    // Move preview button to save actions.
    $build['discussion_answer_form']['actions']['preview']['#attributes']['class'] = $button_classes;
    $build['discussion_answer_form']['actions']['preview']['#dropbutton'] = 'save';
    $build['discussion_answer_form']['actions']['save']['#links']['preview']['title'] = render($build['discussion_answer_form']['actions']['preview']);

    return $build;
  }
}
