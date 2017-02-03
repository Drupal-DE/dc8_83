<?php

use Drupal\Core\Url;

/**
 * @file
 * Theme functions.
 */

/**
 * Prepares variables for filter-api-link templates.
 *
 * Default template: filter-api-link.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_filter_api_link(&$vars) {
  $element = $vars['element'];
  $uri = sprintf('https://api.drupal.org/api/function/%s', $element['#function']);
  if (!empty($element['#version'])) {
    $uri .= '/' . $element['#version'];
  }
  $vars['link'] = [
    'url' => Url::fromUri($uri, ['absolute' => TRUE]),
    'caption' => $element['#title'],
    'title' => t('api.drupal.org: @name()', ['@name' => $element['#name']]),
  ];
}

/**
 * Prepares variables for filter-do-link templates.
 *
 * Default template: filter-do-link.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_filter_project_link(&$vars) {
  $element = $vars['element'];
  $uri = sprintf('https://drupal.org/project/%s', $element['#name']);
  $vars['link'] = [
    'url' => Url::fromUri($uri, ['absolute' => TRUE]),
    'caption' => $element['#title'],
    'title' => t('drupal.org: @name', ['@name' => $element['#name']]),
  ];
}
