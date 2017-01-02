<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomParagraphsBase;

/**
 * Process class to attach paragraphs to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_paragraphs__core",
 *   description = "Add modules_core paragraphs to showroom items",
 *   weight = 3,
 *   field = "modules__core",
 *   delta = 2
 * )
 */
class ShowroomParagraphModulesCore extends ShowroomParagraphsBase {

}
