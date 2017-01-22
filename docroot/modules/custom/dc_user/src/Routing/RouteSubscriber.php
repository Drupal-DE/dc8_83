<?php

namespace Drupal\dc_user\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\dc_user\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Remove _admin_route for user forms we use dc theme here all the time.
    $non_admin_routes = [
      'entity.user.edit_form',
      'user.admin_create'
    ];
    foreach ($non_admin_routes as $non_admin_route) {
      if ($route = $collection->get($non_admin_route)) {
        $route->setOption('_admin_route', FALSE);
      }
    }
  }
}
