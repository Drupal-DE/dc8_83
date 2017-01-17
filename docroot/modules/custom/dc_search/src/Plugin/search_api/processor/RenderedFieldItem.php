<?php

namespace Drupal\dc_search\Plugin\search_api\processor;

use Drupal\Core\Session\UserSession;
use Drupal\dc_search\Plugin\search_api\processor\Property\RenderedFieldItemProperty;
use Drupal\field\Entity\FieldConfig;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\search_api\processor\RenderedItem;

/**
 * Adds an additional field containing the rendered item.
 *
 * @SearchApiProcessor(
 *   id = "rendered_field_item",
 *   label = @Translation("Rendered field item"),
 *   description = @Translation("Adds an additional field containing rendered field items for entity references."),
 *   stages = {
 *     "add_properties" = 0,
 *     "pre_index_save" = -10,
 *   }
 * )
 */
class RenderedFieldItem extends RenderedItem {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      return [];
    }
    foreach ($datasource->getPropertyDefinitions() as $item_name => $field_definition) {
      if (!$field_definition instanceof FieldConfig) {
        continue;
      }
      if (strpos($field_definition->getType(), 'entity_reference') === FALSE) {
        continue;
      }
      $definition = [
        'type' => 'text',
        'label' => $this->t('@label: rendered', ['@label' => $field_definition->label()]),
        'description' => $this->t('The complete HTML which would be displayed when viewing the field'),
        'processor_id' => $this->getPluginId(),
        'base_field' => $item_name,
        'base_field_definition' => $field_definition,
      ];
      $properties[$item_name . '_rendered'] = new RenderedFieldItemProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $original_user = $this->currentUser->getAccount();

    // Switch to the default theme in case the admin theme is enabled.
    $active_theme = $this->getThemeManager()->getActiveTheme();
    $default_theme = $this->getConfigFactory()
      ->get('system.theme')
      ->get('default');
    $default_theme = $this->getThemeInitializer()
      ->getActiveThemeByName($default_theme);
    $this->getThemeManager()->setActiveTheme($default_theme);

    // Count of items that don't have a view mode.
    $unset_view_modes = 0;

    foreach ($item->getFields() as $field_name => $field) {
      if (stripos(strrev($field_name), 'deredner_') !== 0) {
        // Field name does not end with "_rendered".
        continue;
      }

      $configuration = $field->getConfiguration();
      $definition = $field->getDataDefinition()->getDefinition();
      if (empty($configuration['view_mode'])) {
        // No view mode configured so skip this field.
        continue;
      }
      // Change the current user to our dummy implementation to ensure we are
      // using the configured roles.
      $this->currentUser->setAccount(new UserSession(['roles' => $configuration['roles']]));

      $entity = $item->getOriginalObject()->getValue();
      $bundle = '';
      $view_builder = NULL;
      $entities = [];
      if ($entity->__isset($definition['base_field'])) {
        try {
          $field_items = $entity->{$definition['base_field']};
          // Load all referenced entities.
          $entities = $field_items->referencedEntities();
          if (empty($entities)) {
            continue;
          }
          // Load bundle and view builder from first item.
          $first_entity = reset($entities);
          $bundle = $first_entity->bundle();
          $view_builder = \Drupal::entityTypeManager()->getViewBuilder($first_entity->getEntityTypeId());
        }
        catch (Exception $exc) {
          \Drupal::logger('RenderedFieldItem')->warning('Failed to load bundle from entity in field "@field".', ['@field' => $field_name]);
        }
        if (empty($view_builder)) {
          \Drupal::logger('RenderedFieldItem')->notice('Failed to get view builder for entity in field "@field".', ['@field' => $field_name]);
          continue;
        }
      }
      if (empty($bundle) || empty($entities)) {
        continue;
      }

      // When no view mode has been set for the bundle, or it has been set to
      // "Don't include the rendered item", skip this item.
      if (empty($configuration['view_mode'][$bundle])) {
        // If it was really not set, also notify the user through the log.
        if (!isset($configuration['view_mode'][$bundle])) {
          ++$unset_view_modes;
        }
        continue;
      }
      else {
        $view_mode = (string) $configuration['view_mode'][$bundle];
      }

      $build = [];
      try {
        $build = $view_builder->viewMultiple($entities, $view_mode);
      }
      catch (Exception $exc) {
        \Drupal::logger('RenderedFieldItem')->warning('Failed to render entity in field "@field" with view mode "@view_mode".', ['@field' => $field_name, '@view_mode' => $view_mode]);
      }

      $value = (string) $this->getRenderer()->renderPlain($build);
      if ($value) {
        $field->addValue($value);
      }
    }

    // Restore the original user.
    $this->currentUser->setAccount($original_user);
    // Restore the original theme.
    $this->getThemeManager()->setActiveTheme($active_theme);

    if ($unset_view_modes > 0) {
      $context = [
        '%index' => $this->index->label(),
        '%processor' => $this->label(),
        '@count' => $unset_view_modes,
      ];
      $this->getLogger()->warning('Warning: While indexing items on search index %index, @count item(s) did not have a view mode configured for one or more "Rendered field item" fields.', $context);
    }
  }

}
