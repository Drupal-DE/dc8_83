<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\AccountFieldsBase;

/**
 * Process class to set field "website" in user accounts.
 *
 * @DcMigrateProcessor(
 *   id = "account_fields__url",
 *   description = "Set values of account field 'website'",
 *   weight = 5,
 *   field = {
 *     "alias" = "url",
 *     "name" = "field_website_uri",
 *     "table" = "user__field_website",
 *     "fid" = 5
 *   }
 * )
 */
class AccountFieldsUrl extends AccountFieldsBase {

  /**
   * {@inheritdoc}
   */
  public function cleanup($options = []) {
    if (!parent::cleanup($options)) {
      return FALSE;
    }

    $database = \Drupal::database();
    $database->query("UPDATE IGNORE {user__field_website} SET field_website_uri = CONCAT('http://', field_website_uri) WHERE field_website_uri NOT REGEXP('^http')");
    $database->query('UPDATE IGNORE {user__field_website} SET field_website_title = :title WHERE field_website_title IS NULL', [':title' => '']);
    $database->query('UPDATE IGNORE {user__field_website} SET field_website_options = :options WHERE field_website_options IS NULL', [':options' => 'a:0:{}']);

    return TRUE;
  }

}
