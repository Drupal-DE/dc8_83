<?php

namespace Drupal\dc_ui\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * Provides a block listing updated discussions of the current user.
 *
 * @Block(
 *   id = "discussion_category_filter",
 *   admin_label = @Translation("Filter: discussion category")
 * )
 */
class DiscussionCategoryFilter extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new DiscussionCategoryFilter block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /* @var $storage \Drupal\taxonomy\TermStorageInterface */
    $storage = $this->entityManager->getStorage('taxonomy_term');
    $categories = [];
    $top_level_terms = $storage->loadTree('discussion_category', 0, 1, TRUE);
    /* @var $term \Drupal\taxonomy\TermInterface */
    foreach ($top_level_terms as $term) {
      $tid = $term->id();
      $children = $storage->loadChildren($tid);
      $categories[$tid] = [
        'term' => $term,
        'children' => $children,
      ];
    }

    $build = [
      '#theme' => 'dc_category_filter',
      '#categories' => $categories,
    ];

    return $build;
  }

}
