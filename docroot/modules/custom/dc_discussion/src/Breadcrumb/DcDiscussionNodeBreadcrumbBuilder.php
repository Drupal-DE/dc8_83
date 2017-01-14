<?php

namespace Drupal\dc_discussion\Breadcrumb;

use Drupal;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dc_ui\Breadcrumb\DcNodeBreadcrumbBuilder;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Exception;

/**
 * Breadcrumb builder for nodes of type "discussion".
 */
class DcDiscussionNodeBreadcrumbBuilder extends DcNodeBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->bundles = ['discussion'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = parent::build($route_match);

    // Add link to discussion overview.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Discussions'), 'page_manager.page_view_diskussion_overview'));

    /** @var \Drupal\node\Entity\NodeInterface $node */
    if (($node = $route_match->getParameter('node')) === NULL) {
      return $breadcrumb;
    }

    // Check if node has been loaded or is only the node id.
    if (is_numeric($node)) {
      $node = Node::load($node);
    }
    $breadcrumb->addCacheableDependency($node);

    self::createBreadcrumb($node, $breadcrumb);

    return $breadcrumb;
  }

  /**
   * Helper function to create breadcrumbs for topic nodes.
   *
   * @param Node $node
   *   Node object of bundle "msg_topic".
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb to alter.
   *
   * @return Breadcrumb
   *   Altered breadcrumb object.
   */
  public static function createBreadcrumb(Node $node, Breadcrumb $breadcrumb) {
    try {
      if (!$node->hasField('field_discussion_category')) {
        // Strange things happen ...
        return $breadcrumb;
      }
      /* @var category \Drupal\taxonomy\TermInterface */
      $category = Term::load($node->field_discussion_category->target_id);
      if (empty($category) || ('discussion_category' !== $category->getVocabularyId())) {
        return $breadcrumb;
      }
      $term_storage = Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $parents = $term_storage->loadAllParents($category->id());
      // Remove first level from hierarchy.
      array_shift($parents);

      // Add term hierarchy.
      foreach (array_reverse($parents) as $parent) {
        $breadcrumb->addLink(Link::createFromRoute($parent->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $parent->id()]));
      }
    }
    catch (Exception $exc) {
      return $breadcrumb;
    }
  }

}
