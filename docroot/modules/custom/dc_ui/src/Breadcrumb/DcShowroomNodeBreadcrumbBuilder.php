<?php

namespace Drupal\dc_ui\Breadcrumb;

use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;

/**
 * Breadcrumb builder for nodes of type "showroom".
 */
class DcShowroomNodeBreadcrumbBuilder extends DcNodeBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->bundles = ['showroom'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = parent::build($route_match);

    // Add link to discussion overview.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Showroom'), 'page_manager.page_view_showroom'));

    /** @var \Drupal\node\Entity\NodeInterface $node */
    if (($node = $route_match->getParameter('node')) === NULL) {
      return $breadcrumb;
    }

    // Check if node has been loaded or is only the node id.
    if (is_numeric($node)) {
      $node = Node::load($node);
    }
    $breadcrumb->addCacheableDependency($node);
    // Add node title.
    $breadcrumb->addLink(Link::createFromRoute($node->getTitle(), '<none>'));
    return $breadcrumb;
  }
}
