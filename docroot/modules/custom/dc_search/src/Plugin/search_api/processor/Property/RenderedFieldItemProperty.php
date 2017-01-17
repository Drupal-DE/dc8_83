<?php

namespace Drupal\dc_search\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Plugin\search_api\processor\Property\RenderedItemProperty;

/**
 * Defines a "rendered item" property.
 */
class RenderedFieldItemProperty extends RenderedItemProperty {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'roles' => [AccountInterface::ANONYMOUS_ROLE],
      'view_mode' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration = $field->getConfiguration();
    $form['#tree'] = TRUE;

    $roles = user_role_names();
    $form['roles'] = [
      '#type' => 'select',
      '#title' => $this->t('User roles'),
      '#description' => $this->t('Your item will be rendered as seen by a user with the selected roles. We recommend to just use "@anonymous" here to prevent data leaking out to unauthorized roles.', ['@anonymous' => $roles[AccountInterface::ANONYMOUS_ROLE]]),
      '#options' => $roles,
      '#multiple' => TRUE,
      '#default_value' => $configuration['roles'],
      '#required' => TRUE,
    ];

    $form['view_mode'] = [
      '#type' => 'item',
      '#description' => $this->t('You can choose the view modes to use for rendering the items of different datasources and bundles. We recommend using a dedicated view mode (for example, the "Search index" view mode available by default for content) to make sure that only relevant data (especially no field labels) will be included in the index.'),
    ];

    $options_present = FALSE;
    $bundles = [];
    $field_definition = $field->getDataDefinition()->getDefinition();
    $base_field_definition = isset($field_definition['base_field_definition']) ? $field_definition['base_field_definition'] : NULL;
    if ($base_field_definition) {
      $handler_settings = $base_field_definition->getSetting('handler_settings');
      $target_type = $base_field_definition->getFieldStorageDefinition()->getSetting('target_type');
      $bundles = isset($handler_settings['target_bundles']) ? $handler_settings['target_bundles'] : [];
    }
    $entity_display_repository = \Drupal::getContainer()->get('entity_display.repository');
    foreach ($bundles as $bundle_id => $target_bundle) {
      $view_modes = $entity_display_repository->getViewModeOptionsByBundle($target_type, $target_bundle);
      $view_modes[''] = $this->t("Don't include the rendered item.");
      if (count($view_modes) > 1) {
        $form['view_mode'][$bundle_id] = [
          '#type' => 'select',
          '#title' => $this->t('View mode for %bundle', ['%bundle' => $bundle_id]),
          '#options' => $view_modes,
        ];
        if (isset($configuration['view_mode'][$bundle_id])) {
          $form['view_mode'][$bundle_id]['#default_value'] = $configuration['view_mode'][$bundle_id];
        }
        $options_present = TRUE;
      }
      else {
        $form['view_mode'][$bundle_id] = [
          '#type' => 'value',
          '#value' => $view_modes ? key($view_modes) : FALSE,
        ];
      }
    }
    // If there are no datasources/bundles with more than one view mode, don't
    // display the description either.
    if (!$options_present) {
      unset($form['view_mode']['#type']);
      unset($form['view_mode']['#description']);
    }

    return $form;
  }

  /**
   * Get the property definition.
   *
   * @return array
   */
  public function getDefinition() {
    return $this->definition;
  }

}
