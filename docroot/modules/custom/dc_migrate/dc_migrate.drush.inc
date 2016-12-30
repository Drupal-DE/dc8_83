<?php

use Drupal\Core\Config\ConfigInstaller;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;
use Drupal\dc_migrate\MigrateBatchHelper;
use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function dc_migrate_drush_command() {
  $commands = [];

  // Refresh default configuration of a module located in config/install.
  $commands['config-refresh'] = [
    'description' => 'Refresh default configuration of an extension without the need to reinstall.',
    'core' => ['8+'],
    'aliases' => ['cf5'],
    'arguments' => [
      'module' => 'The name of the extension needing a configuration refresh.',
      'type' => "The type of the extension (one of 'module', 'theme', 'profile'). Defaults to 'module'.",
    ],
    'examples' => [
      'drush config-refresh mymodule' => "Refresh default configuration of a module named 'mymodule'.",
      'drush config-refresh myprofile profile' => "Refresh default configuration of a profile named 'myprofile'.",
    ],
  ];
  $commands['dc-migrate-process'] = [
    'description' => 'Run a custom migration processor.',
    'core' => ['8+'],
    'aliases' => ['dc-mp'],
    'arguments' => [
      'processor' => dt('Plugin ID of custom migration processor'),
    ],
    'options' => [
      'limit' => dt('The number of items to process. Set to 0 to process all items. Defaults to 0 (all).'),
      'batch-size' => dt('The number of items to process per batch run. Set to 0 to process all items at once (not recommended). Defaults to 100.'),
      'refresh-data' => dt('Refresh mapped source data.'),
    ],
    'examples' => [
      'drush dc-migrate-process recipe_ingredients__ingredient' => 'Map ingredients to their corresponding lists.',
    ],
  ];
  $commands['dc-migrate-process-list'] = [
    'description' => 'List all dc migrate processors.',
    'examples' => [
      'drush dc-migrate-process-list' => dt('List all processors.'),
      'drush dc-mp-l' => dt('Alias to list all processors.'),
    ],
    'aliases' => ['dc-mp-l'],
  ];

  return $commands;
}

/**
 * Config refresh command callback.
 *
 * @param $name
 *   The extension name.
 * @param $type
 *   (optional) The extension type.
 *
 * @see ConfigInstaller::installDefaultConfig()
 */
function drush_dc_migrate_config_refresh($name, $type = 'module') {
  if (!in_array($type, ['module', 'theme', 'profile'])) {
    $type = 'module';
  }
  $config_installer = Drupal::service('config.installer');
  // Find default configuration of the extension.
  $default_install_path = drupal_get_path($type, $name) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
  if (is_dir($default_install_path)) {
    if (!$config_installer->isSyncing()) {
      $storage = new FileStorage($default_install_path, StorageInterface::DEFAULT_COLLECTION);
      $prefix = '';
    }
    else {
      $storage = $config_installer->getSourceStorage();
      $prefix = $name . '.';
    }

    // Gets profile storages to search for overrides if necessary.
    $profile = Settings::get('install_profile');
    $profile_storages = [];
    if ($profile && $profile != $name) {
      $profile_path = drupal_get_path('module', $profile);
      foreach ([InstallStorage::CONFIG_INSTALL_DIRECTORY, InstallStorage::CONFIG_OPTIONAL_DIRECTORY] as $directory) {
        if (is_dir($profile_path . '/' . $directory)) {
          $profile_storages[] = new FileStorage($profile_path . '/' . $directory, StorageInterface::DEFAULT_COLLECTION);
        }
      }
    }

    $config_factory = Drupal::service('config.factory');
    $collection_info = Drupal::service('config.manager')->getConfigCollectionInfo();
    foreach ($collection_info->getCollectionNames() as $collection) {
      $config_to_refresh = _dc_migrate_config_refresh_get_config($storage, $collection, $prefix, $profile_storages);
      // Remove existing configuration.
      foreach (array_keys($config_to_refresh) as $config_name) {
        $config_factory->getEditable($config_name)->delete();
      }
    }

    // Re-install default config.
    $config_installer->installDefaultConfig($type, $name);
    Drupal::service('router.builder')->setRebuildNeeded();
    if ('theme' === $type) {
      Drupal::moduleHandler()->invokeAll('themes_installed', [[$name]]);
    }
    else {
      Drupal::moduleHandler()->invokeAll('modules_installed', [[$name]]);
    }

    drush_log(sprintf('Default configuration refreshed for %s "%s".', $type, $name), LogLevel::OK);
  }
}

/**
 * Gets configuration data from the provided storage.
 *
 * @param StorageInterface $storage
 *   The configuration storage to read configuration from.
 * @param string $collection
 *  The configuration collection to use.
 * @param string $prefix
 *   (optional) Limit to configuration starting with the provided string.
 * @param StorageInterface[] $profile_storages
 *   An array of storage interfaces containing profile configuration to check
 *   for overrides.
 *
 * @return array
 *   An array of configuration data read from the source storage keyed by the
 *   configuration object name.
 */
function _dc_migrate_config_refresh_get_config(StorageInterface $storage, $collection, $prefix = '', array $profile_storages = []) {
  if ($storage->getCollectionName() != $collection) {
    $storage = $storage->createCollection($collection);
  }
  $data = $storage->readMultiple($storage->listAll($prefix));

  // Check to see if the corresponding override storage has any overrides.
  foreach ($profile_storages as $profile_storage) {
    if ($profile_storage->getCollectionName() != $collection) {
      $profile_storage = $profile_storage->createCollection($collection);
    }
    $data = $profile_storage->readMultiple(array_keys($data)) + $data;
  }
  return $data;
}

/**
 * Command callback for "dc-migrate-process".
 *
 * @param string $processor
 *   Plugin ID of custom migration processor.
 */
function drush_dc_migrate_process($processor) {
  $refresh_data = drush_get_option('refresh-data', FALSE);
  $limit = drush_get_option('limit', -1);
  $batch_size = drush_get_option('batch-size', 500);

  $options = [
    'batch_size' => $batch_size,
    'limit' => $limit,
    'refresh_source_data' => $refresh_data,
  ];

  try {
    MigrateBatchHelper::create($processor, $options);
    drush_backend_batch_process();
  }
  catch (Exception $ex) {
    drush_log(dt("Couldn't create a batch, please check the batch size and limit parameters."), 'error');
    drush_log($ex->getMessage(), 'error');
  }
}

/**
 * Command callback for "dc-migrate-process-list".
 */
function drush_dc_migrate_process_list() {
  /* @var $manager \Drupal\dc_migrate\DcMigrateProcessorPluginManager */
  $manager = \Drupal::service('plugin.manager.dc_migrate');
  $processors = $manager->getProcessors();
  if (empty($processors)) {
    drush_log('No processors found.');
    return;
  }
  $rows[] = [
    dt('ID'),
    dt('Description'),
  ];

  /* @var $processor \Drupal\dc_migrate\Plugin\DcMigrateProcessorInterface */
  foreach ($processors as $plugin_id => $processor) {
    $rows[] = [
      $plugin_id,
      $processor->getDescription(),
    ];
  }

  drush_print_table($rows);
}
