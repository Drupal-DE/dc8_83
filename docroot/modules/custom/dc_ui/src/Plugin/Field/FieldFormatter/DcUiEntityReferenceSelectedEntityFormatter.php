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
    $index = $this->getSetting('index');

    if ((-1 === $index) || (($selected_item = $items->get($index)) === NULL)) {
      // Display all items.
      return parent::viewElements($items, $langcode);
    }

    // Limit list of items to selected.
    $items_reduced = new EntityReferenceFieldItemList($items->getDataDefinition());
    $items_reduced->set(0, $selected_item);

    return parent::viewElements($items_reduced, $langcode);
  }

}
