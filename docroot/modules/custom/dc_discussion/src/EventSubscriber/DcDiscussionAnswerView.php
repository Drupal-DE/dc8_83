<?php

namespace Drupal\dc_discussion\EventSubscriber;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DcDiscussionAnswerView.
 *
 * @package Drupal\dc_discussion
 */
class DcDiscussionAnswerView implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $route_match;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   */
  public function __construct(CurrentRouteMatch $route_match) {
    $this->route_match = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Add custom request event.
    $events[KernelEvents::REQUEST][] = array('redirectDiscussionAnswer', 31);
    return $events;
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function redirectDiscussionAnswer(GetResponseEvent $event) {
    // Is master request? - thx to path-auto module.
    if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
      return;
    }
    // Get taxonomy_term form route for further processing.
    $node = $this->route_match->getParameter('node');
    $base_route = $this->route_match->getParameter('base_route_name');

    if ((!empty($node) && $node->bundle() == 'discussion') && $base_route == 'entity.node.canonical') {
      // Redirect to topic of node if parent is available.
      if ($node->hasField('field_topic') && !empty($node->field_topic->target_id)) {
        $target_nid = $node->field_topic->target_id;
        $event->setResponse(new CacheableRedirectResponse('/node/' . $target_nid .'#' . $node->id()));
      }
    }
  }
}
