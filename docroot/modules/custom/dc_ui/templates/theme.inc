<?php

/**
 * @file
 * Theme functions.
 */

/**
 * Prepares variables for category filter templates.
 *
 * Default template: category-filter.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_category_filter(&$vars) {
  $element = $vars['element'];
  $vars['categories'] = [];
  foreach ($element['#categories'] as $tid => $category) {
    /* @var $term \Drupal\taxonomy\TermInterface */
    $term = $category['term'];
    $item = [
      'name' => $term->getName(),
      'url' => $term->toUrl(),
      'children' => [],
    ];
    /* @var $child \Drupal\taxonomy\TermInterface */
    foreach ($category['children'] as $child_id => $child) {
      $item['children'][$child_id] = [
        'name' => $child->getName(),
        'url' => $child->toUrl(),
      ];
    }
    $vars['categories'][$tid] = $item;
  }
}
