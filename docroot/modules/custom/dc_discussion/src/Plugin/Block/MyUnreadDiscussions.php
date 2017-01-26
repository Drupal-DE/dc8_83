<?php

namespace Drupal\dc_discussion\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
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
      $unread = $service->getUnreadForUser($user_current->id(), $this->configuration['num_results']);
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
      \Drupal::logger('dc_discussion')->warning('Failed to get unread discussions.');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'num_results' => 10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['num_results'] = [
      '#type' => 'number',
      '#title' => $this->t('Result count'),
      '#description' => $this->t('Number of displayed items.'),
      '#default_value' => $this->configuration['num_results'],
      '#min' => 1,
      '#max' => 25,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['num_results'] = $form_state->getValue('num_results');
  }

}
