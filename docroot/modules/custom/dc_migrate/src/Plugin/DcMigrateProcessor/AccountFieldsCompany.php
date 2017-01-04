<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\AccountFieldsBase;

/**
 * Process class to set field "company" in user accounts.
 *
 * @DcMigrateProcessor(
 *   id = "account_fields__company",
 *   description = "Set values of account field 'company'",
 *   weight = 5,
 *   field = {
 *     "alias" = "company",
 *     "name" = "field_company_value",
 *     "table" = "user__field_company",
 *     "fid" = 6
 *   }
 * )
 */
class AccountFieldsCompany extends AccountFieldsBase {

}
