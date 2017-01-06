<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomParagraphsBase;

/**
 * Process class to attach paragraphs to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_paragraphs__development",
 *   description = "Add development paragraphs to showroom items",
 *   weight = 3,
 *   field = "development",
 *   delta = 1
 * )
 */
class ShowroomParagraphDevelopment extends ShowroomParagraphsBase {

}
