<?php

namespace Drupal\dc_discussion\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * Provides a block listing updated discussions of the current user.
 *
 * @Block(
 *   id = "discussion_my_unread",
 *   admin_label = @Translation("My unread discussions")
 * )
 */
class MyUnreadDiscussions extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $empty_text = $this->t('You have no unread discussions.');

    $build = [
      '#markup' => $empty_text,
    ];

    try {
      $user_current = \Drupal::currentUser();

      /* @var $service \Drupal\dc_discussion\DiscussionInformationInterface */
      $service = \Drupal::service('dc_discussion.discussion_information');
      // Get unread discussions for current user.
      $unread = $service->getUnreadForUser($user_current->id());
      if (empty($unread)) {
        return $build;
      }
      $items = [];

      foreach ($unread as $row) {
        $items[] = [
          '#title' => $row->title,
          '#url' => Url::fromRoute('entity.node.canonical', ['node' => $row->nid], ['fragment' => 'new']),
          '#type' => 'link',
        ];
      }
      $build = [
        '#theme' => 'item_list',
        '#items' => $items,
        '#empty' => $empty_text,
        '#cache' => [
          'tags' => [
            'user__' . $user_current->id(),
          ],
        ]
      ];
    }
    catch (Exception $exc) {

    }

    return $build;
  }

}
