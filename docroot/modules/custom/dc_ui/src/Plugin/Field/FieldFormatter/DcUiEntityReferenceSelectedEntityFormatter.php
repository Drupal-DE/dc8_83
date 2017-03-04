<?php

namespace Drupal\dc_ui\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "dc_ui_entity_reference_selected_entity_view",
 *   label = @Translation("Selected rendered entity"),
 *   description = @Translation("Display the selected referenced entity rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class DcUiEntityReferenceSelectedEntityFormatter extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'index' => -1,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['index'] = [
      '#type' => 'number',
      '#title' => t('Item index'),
      '#description' => t('Enter the index of the item to display or "-1" to display all items. 0 is the first item, 1 the second, ...'),
      '#default_value' => $this->getSetting('index'),
      '#required' => TRUE,
      '#min' => -1,
      '#max' => 100,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $index = $this->getSetting('index');
    if (-1 == $index) {
      $summary[] = t('Display all items');
    }
    else if (0 == $index) {
      $summary[] = t('Display first item');
    }
    else {
      $summary[] = t('Display item with index @index', ['@index' => $index]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    $index = $this->getSetting('index');
    $elements = [];

    if ((-1 === $index) || (($selected_item = $items->get($index)) === NULL)) {
      // Display all items.
      return parent::viewElements($items, $langcode);
    }

    $items_reduced = new EntityReferenceFieldItemList($items->getDataDefinition());
    $items_reduced->set(0, $selected_item);

    foreach ($this->getEntitiesToView($items_reduced, $langcode) as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory
          ->get('entity')
          ->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', ['@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()]);
        return $elements;
      }
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += ['resource' => $entity->toUrl()->toString()];
      }
      $depth = 0;
    }

    return $elements;
  }

}
