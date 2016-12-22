<?php

namespace Drupal\dc_ui;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom path processor to alter paths for node add, edit, delete.
 *
 * @package Drupal\dc_ui
 */
class DcUiPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $settings = \Drupal::config('dc_ui.settings');
    if (empty($settings->get('rewrites'))) {
      return $path;
    }

    foreach ($settings->get('rewrites') as $bundle => $info) {
      foreach ($info as $op => $value) {
        if (strpos($value, '[nid]') === FALSE) {
          // Plain string.
          if ($path === $value) {
            return "/node/{$op}/{$bundle}";
          }
        }
        else {
          // Regular expression needed.
          $regex = strtr($value, ['[nid]' => '(?P<nid>[\d+])']);
          if (preg_match('#' . $regex . '#', $path, $matches) && !empty($matches['nid'])) {
            return "/node/{$matches['nid']}/{$op}";
          }
        }
      }
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    $settings = \Drupal::config('dc_ui.settings');
    if (empty($settings->get('rewrites'))) {
      return $path;
    }

    foreach ($settings->get('rewrites') as $bundle => $info) {
      if ("/node/add/{$bundle}" === $path && !empty($info['add'])) {
        return $info['add'];
      }
      else if (preg_match('#^/node/(?P<nid>[\d+])/edit($|/.*)#', $path, $matches) && !empty($matches['nid']) && !empty($info['edit'])) {
        return strtr($info['edit'], ['[nid]' => $matches['nid']]);
      }
      else if (preg_match('#^/node/(?P<nid>[\d+])/delete($|/.*)#', $path, $matches) && !empty($matches['nid']) && !empty($info['delete'])) {
        return strtr($info['delete'], ['[nid]' => $matches['nid']]);
      }
    }

    return $path;
  }

}
