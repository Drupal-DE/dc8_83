<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomParagraphsBase;

/**
 * Process class to attach paragraphs to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_paragraphs__description",
 *   description = "Add description paragraphs to showroom items",
 *   weight = 3,
 *   field = "description",
 *   delta = 0
 * )
 */
class ShowroomParagraphDescription extends ShowroomParagraphsBase {

}
