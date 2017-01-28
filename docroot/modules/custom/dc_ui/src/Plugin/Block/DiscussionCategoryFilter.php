<?php

namespace Drupal\dc_ui\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermInterface;
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
    $active_tid = \Drupal::routeMatch()->getParameter('tid');
    $active_trail = $active_tid ? $storage->loadAllParents($active_tid) : [];

    $categories = [];
    $top_level_terms = $storage->loadTree('discussion_category', 0, 1, TRUE);
    /* @var $term TermInterface */
    foreach ($top_level_terms as $term) {
      $categories[$term->id()] = [
        'term' => $term,
        'children' => $this->buildChildren($term, $active_trail),
      ];
    }

    $build = [
      '#theme' => 'dc_category_filter',
      '#categories' => $categories,
      '#active' => $active_trail,
    ];

    return $build;
  }

  /**
   * Build child list of category.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term to build child list for.
   * @param array $active_tree
   *   List of active terms.
   *
   * @return array
   *   List of children to render.
   */
  protected function buildChildren(TermInterface $term, array $active_tree) {
    /* @var $storage \Drupal\taxonomy\TermStorageInterface */
    $storage = $this->entityManager->getStorage('taxonomy_term');
    $terms = $storage->loadChildren($term->id());
    if (empty($terms)) {
      return [];
    }

    $children = [];
    /* @var $child_term \Drupal\taxonomy\TermInterface */
    foreach ($terms as $tid => $child_term) {
      $children[$tid] = [
        'term' => $child_term,
        'active' => in_array($tid, array_keys($active_tree)),
        'children' => $this->buildChildren($child_term, $active_tree),
      ];
    }

    return $children;
  }

}
