<?php

/**
 * @file
 * Theme functions.
 */

/**
 * Prepares variables for answer count templates.
 *
 * Default template: answer-count.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_answer_count(&$vars) {
  $element = $vars['element'];
  /* @var $topic \Drupal\node\NodeInterface */
  $topic = $element['#entity'];
  $link = [
    '#title' => $element['#count'],
    '#type' => 'link',
    '#url' => $topic->toUrl(),
    '#attributes' => [
      'title' => \Drupal::service('string_translation')->formatPlural($element['#count'], '@count reply on \'@title\'', '@count replies on \'@title\'', ['@title' => $topic->getTitle()]),
    ],
  ];
  if (\Drupal::currentUser()->isAuthenticated()) {
    $link['#url'] = $topic->toUrl('canonical', ['fragment' => 'new']);
  }
  $vars['link'] = [
    'title' => $link['#attributes']['title'],
    'url' => $link['#url'],
    'attributes' => $link['#attributes'],
  ];
  $vars['link_rendered'] = render($link);
  $vars['count'] = $element['#count'];
}
