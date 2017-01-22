<?php

namespace Drupal\dc_ui\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StreamWrapper\LocalReadOnlyStream;

/**
 * Simple read-only stream wrapper class for demo content.
 */
class ActiveTheme extends LocalReadOnlyStream {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('Active theme');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Active theme of dc.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    $config = \Drupal::config('system.theme');
    $default_theme = $config->get('default');
    // Get path to theme.
    return drupal_get_path('theme', $default_theme);
  }

  /**
   * Implements Drupal\Core\StreamWrapper\StreamWrapperInterface::getExternalUrl().
   *
   * @throws \LogicException PO files URL should not be public.
   */
  function getExternalUrl() {
    global $base_url;
    $path = str_replace('\\', '/', $this->getTarget());
    return $base_url . '/' . $this->getDirectoryPath() . '/' . UrlHelper::encodePath($path);
  }
}
