<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\ShowroomParagraphsBase;

/**
 * Process class to attach paragraphs to showroom nodes.
 *
 * @DcMigrateProcessor(
 *   id = "showroom_paragraphs__contrib",
 *   description = "Add modules_contrib paragraphs to showroom items",
 *   weight = 3,
 *   field = "modules__contrib",
 *   delta = 3
 * )
 */
class ShowroomParagraphModulesContrib extends ShowroomParagraphsBase {

}
