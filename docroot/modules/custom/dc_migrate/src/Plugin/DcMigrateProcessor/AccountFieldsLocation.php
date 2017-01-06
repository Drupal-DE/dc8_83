<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\AccountFieldsBase;

/**
 * Process class to set field "location" in user accounts.
 *
 * @DcMigrateProcessor(
 *   id = "account_fields__location",
 *   description = "Set values of account field 'location'",
 *   weight = 5,
 *   field = {
 *     "alias" = "location",
 *     "name" = "field_location_value",
 *     "table" = "user__field_location",
 *     "fid" = 2
 *   }
 * )
 */
class AccountFieldsLocation extends AccountFieldsBase {

}
