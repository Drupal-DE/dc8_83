<?php

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

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
  $vars['dropdowns'] = [];
  $vars['child_categories'] = [];
  $active_trail = array_reverse($element['#active']);
  /* @var $top_category \Drupal\taxonomy\TermInterface */
  $top_category = array_shift($active_trail);
  $level = 0;
  $categories = $element['#categories'];
  $parent = NULL;

  do {
    /* @var $active \Drupal\taxonomy\TermInterface */
    $active = reset($active_trail);

    $dropdown = [
      '#theme' => 'dc_category_filter_dropdown',
      '#active' => $active,
      '#parent' => $parent,
      '#items' => $categories,
      '#level' => $level,
    ];
    $vars['dropdowns'][$level] = render($dropdown);
    if ($top_category && ($level == 0)) {
      $categories = $categories[$top_category->id()]['children'];
    }
    $level++;
    if ($active && ($level > 0)) {
      $categories = $categories[$active->id()]['children'];
    }
    $parent = $active;
    array_shift($active_trail);
  }
  while (count($active_trail));

  if ($active && !empty($categories)) {
    $dropdown = [
      '#theme' => 'dc_category_filter_dropdown',
      '#active' => $active,
      '#items' => $categories,
      '#level' => $level,
    ];
    $vars['dropdowns'][$level] = render($dropdown);
  }
}

/**
 * Prepares variables for category filter dropdown templates.
 *
 * Default template: category-filter-dropdown.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_category_filter_dropdown(&$vars) {
  $element = $vars['element'];
  $vars['categories'] = [];
  $vars['overview_url'] = Url::fromRoute('page_manager.page_view_discussions_latest');
  $vars['overview_label'] = t('all categories', [], ['context' => 'dc_ui:filter']);
  $vars['label'] = t('all categories', [], ['context' => 'dc_ui:filter']);
  if ($element['#active']) {
    $vars['label'] = Unicode::truncate($element['#active']->getName(), 22, TRUE, TRUE);

    if (($element['#level'] > 0) && !in_array($element['#active']->id(), array_keys($element['#items']))) {
      // Must be parent.
      $vars['label'] = t('all', [], ['context' => 'dc_ui:filter']);
      $vars['overview_label'] = t('all', [], ['context' => 'dc_ui:filter']);
      $vars['overview_url'] = Url::fromRoute('page_manager.page_view_latest_discussions_filtered', ['tid' => $element['#active']->id()]);
    }
    if (!empty($element['#parent'])) {
      $vars['overview_label'] = t('all', [], ['context' => 'dc_ui:filter']);
      $vars['overview_url'] = Url::fromRoute('page_manager.page_view_latest_discussions_filtered', ['tid' => $element['#parent']->id()]);
    }
  }

  foreach ($element['#items'] as $tid => $category) {
    /* @var $term \Drupal\taxonomy\TermInterface */
    $term = $category['term'];
    $item = [
      'name' => $term->getName(),
      'url' => Url::fromRoute('page_manager.page_view_latest_discussions_filtered', ['tid' => $tid]),
      'attributes' => new Attribute(),
      'children' => [],
    ];
    if ($element['#active'] && ($tid == $element['#active']->id())) {
      $item['attributes']->addClass('uk-active');
    }
    foreach ($category['children'] as $child_id => $child) {
      /* @var $child_term \Drupal\taxonomy\TermInterface */
      $child_term = $child['term'];
      $item['children'][$child_id] = [
        'name' => $child_term->getName(),
        'url' => Url::fromRoute('page_manager.page_view_latest_discussions_filtered', ['tid' => $child_id]),
        'attributes' => new Attribute(),
      ];
      if (!empty($child['active'])) {
        $item['children'][$child_id]['attributes']->addClass('uk-active');
      }
      if (!empty($child['children'])) {
//        $item['children'][$child_id]['attributes']->setAttribute('uk-icon', 'icon: arrow-right');
      }
    }
    $vars['categories'][$tid] = $item;
  }
}
