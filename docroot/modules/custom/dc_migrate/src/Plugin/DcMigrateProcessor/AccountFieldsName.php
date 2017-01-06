<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\AccountFieldsBase;

/**
 * Process class to set field "name" in user accounts.
 *
 * @DcMigrateProcessor(
 *   id = "account_fields__name",
 *   description = "Set values of account field 'name'",
 *   weight = 5,
 *   field = {
 *     "alias" = "name",
 *     "name" = "field_name_value",
 *     "table" = "user__field_name",
 *     "fid" = 1
 *   }
 * )
 */
class AccountFieldsName extends AccountFieldsBase {

}
