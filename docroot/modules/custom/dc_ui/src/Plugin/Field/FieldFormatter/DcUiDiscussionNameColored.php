<?php

namespace Drupal\dc_ui\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'string' formatter.
 *
 * @FieldFormatter(
 *   id = "dc_ui_discussion_name_colored",
 *   label = @Translation("Colored discussion category name"),
 *   description = @Translation("Display discussion category name with color indicator."),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class DcUiDiscussionNameColored extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $output_as_link = $this->getSetting('link_to_entity');

    $color = $items->getEntity()->field_color->getValue();
    if (empty($color[0]['value'])) {
      // Try to load color from parent term.
      /* @var $storage \Drupal\taxonomy\TermStorage */
      $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $parents = $storage->loadParents($items->getEntity()->id());
      if (!empty($parents)) {
        $parent = reset($parents);
        $color = $parent->field_color->getValue();
      }
    }
    $color_code = isset($color[0]['value']) ? '#' . $color[0]['value'] : 'transparent';

    foreach ($items as $delta => $item) {
      if (!$output_as_link) {
        // Rewrite element.
        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $elements[$delta]['#context']['value'],
        ];
        unset($elements[$delta]['#context']);
        unset($elements[$delta]['#template']);
      }

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
    return $field_definition->getFieldStorageDefinition()->getTargetEntityTypeId() === 'taxonomy_term';
  }

}
