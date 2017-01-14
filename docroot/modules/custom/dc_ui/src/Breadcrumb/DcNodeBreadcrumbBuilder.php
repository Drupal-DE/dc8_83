<?php

namespace Drupal\dc_ui\Breadcrumb;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dc_ui\Breadcrumb\DcBreadcrumbBuilderBase;
use Drupal\node\Entity\Node;

/**
 * Base breadcrumb builder for nodes.
 */
abstract class DcNodeBreadcrumbBuilder extends DcBreadcrumbBuilderBase {

  /**
   * List of bundles the builder should apply to.
   *
   * @var array
   */
  protected $bundles = [];

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if (($node = $route_match->getParameter('node')) === NULL) {
      return FALSE;
    }
    // Check if node has been loaded or is only the node id.
    if (is_numeric($node)) {
      $node = Node::load($node);
    }
    return in_array($node->getType(), $this->bundles);
  }

}
