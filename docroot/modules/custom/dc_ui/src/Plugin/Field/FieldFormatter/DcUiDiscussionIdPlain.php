<?php

namespace Drupal\dc_ui\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Plugin implementation of the 'string' formatter.
 *
 * @FieldFormatter(
 *   id = "dc_ui_discussion_id_plain",
 *   label = @Translation("Plain ID"),
 *   description = @Translation("Display the plain entity ID."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class DcUiDiscussionIdPlain extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /* @var $item \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem */
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#plain_text' => $item->target_id,
        '#cache' => [
          'tags' => $item->entity->getCacheTags(),
        ],
      ];
    }

    return $elements;
  }

}
