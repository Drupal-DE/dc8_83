<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\AccountFieldsBase;

/**
 * Process class to set field "real_name" in user accounts.
 *
 * @DcMigrateProcessor(
 *   id = "account_fields__name",
 *   description = "Set values of account field 'real_name'",
 *   weight = 5,
 *   field = {
 *     "alias" = "name",
 *     "name" = "field_real_name_value",
 *     "table" = "user__field_real_name",
 *     "fid" = 1
 *   }
 * )
 */
class AccountFieldsName extends AccountFieldsBase {

}
