<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

require_once DRUPAL_ROOT . '/sites/default/default.settings.php';

$settings['hash_salt'] = 'dqT9cFU3&LAg8BFCOSaUoiy4';

// Fast 404 pages.
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$config['system.performance']['fast_404']['html'] = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';


// Set translations folder.
$config['locale.settings']['translation.path'] = $settings['file_public_path'] . '/translations';

// Config path.
$config_directories[CONFIG_SYNC_DIRECTORY] = '../config/sync';

/**
 * Load local development override configuration, if available.
 *
 * Use settings.local.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
$siteEnvironment = getenv('AH_SITE_ENVIRONMENT');
$siteEnvironment = !empty($siteEnvironment) ? $siteEnvironment : 'local';

$stageSettingsFilePath = DRUPAL_ROOT . '/sites/default/settings.' . $siteEnvironment . '.php';
if (file_exists($stageSettingsFilePath)) {
  include $stageSettingsFilePath;
}

// Force minimal as install profile.
$settings['install_profile'] = 'minimal';

// Make sure Drush keeps working.
// Modified from function drush_verify_cli()
$cli = (php_sapi_name() == 'cli');
