<?php

namespace Drupal\dc_ui\Breadcrumb;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dc_ui\Breadcrumb\DcBreadcrumbBuilderBase;

/**
 * Base breadcrumb builder for views.
 */
abstract class DcViewsBreadcrumbBuilder extends DcBreadcrumbBuilderBase {

  /**
   * List of route names the builder should apply to.
   *
   * @var array
   */
  protected $routes = [];

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return in_array($route_match->getRouteName(), $this->routes);
  }

}
