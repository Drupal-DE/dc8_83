<?php

/**
 * @file
 * Theme functions.
 */

/**
 * Prepares variables for quote templates.
 *
 * Default template: quote.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the element.
 *
 */
function template_preprocess_dc_quote(&$vars) {
  $element = $vars['element'];
  $vars['author'] = empty($element['#author']) ? '' : $element['#author'];
  $vars['author_linked'] = NULL;
  $vars['quote'] = $element['#quote'];
}
