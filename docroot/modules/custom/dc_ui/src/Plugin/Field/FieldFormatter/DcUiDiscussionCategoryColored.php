<?php

namespace Drupal\dc_ui\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'discussion category colored' formatter.
 *
 * @FieldFormatter(
 *   id = "dc_ui_discussion_category_colored",
 *   label = @Translation("Colored discussion category"),
 *   description = @Translation("Display discussion category with color indicator."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class DcUiDiscussionCategoryColored extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $output_as_link = $this->getSetting('link');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if (!$entity->hasField('field_color') || empty($elements[$delta])) {
        continue;
      }
      if (!$output_as_link) {
        // Rewrite element.
        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $elements[$delta]['#plain_text'],
        ];
        unset($elements[$delta]['#plain_text']);
      }

      $color = $entity->field_color->getValue();
      if (empty($color[0]['value'])) {
        // Try to load color from parent term.
        /* @var $storage \Drupal\taxonomy\TermStorage */
        $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $parents = $storage->loadParents($entity->id());
        if (!empty($parents)) {
          $parent = reset($parents);
          $color = $parent->field_color->getValue();
        }
      }
      $color_code = isset($color[0]['value']) ? '#' . $color[0]['value'] : 'transparent';
      $marker = [
        '#type' => 'html_tag',
        '#tag' => 'i',
        '#value' => '',
        '#attributes' => [
          'class' => [
            'uk-label',
            'marker--color',
          ],
          'style' => sprintf('background: %s;', $color_code),
        ],
      ];
      $elements[$delta]['#prefix'] = render($marker);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for taxonomy terms.
    return $field_definition->getFieldStorageDefinition()->getSetting('target_type') == 'taxonomy_term';
  }

}
