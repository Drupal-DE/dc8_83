<?php

namespace Drupal\dc_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to create links to the Drupal API.
 *
 * @Filter(
 *   id = "dc_filter_api",
 *   title = @Translation("Drupal API"),
 *   description = @Translation("Creates links to the Drupal API."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class DcFilterApi extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if (stristr($text, '[api:') === FALSE) {
      return new FilterProcessResult($text);
    }

    foreach ($this->getMatches($text) as $key => $match) {
      $link = [
        '#function' => $match['name'],
        '#name' => $match['name'],
        '#title' => $match['title'],
        '#version' => $match['version'],
        '#theme' => 'dc_filter_api_link',
      ];
      $text = str_replace($match['orig'], render($link), $text);
    }

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('
        <p>You can add links to the Drupal API. Examples:</p>
        <ul>
            <li><code>[api:hook_entity_update]</code> for a simple link</li>
            <li><code>[api:hook_entity_update hook_entity_update()]</code> for a link with a custom title</li>
            <li><code>[api:hook_menu:7]</code> for specific versions</li>
        </ul>');
    }
    return $this->t('You can add links to the Drupal API by using <code>[api:hook_entity_update]</code>.');
  }

  /**
   * Find all matches for the filter.
   */
  public function getMatches($text) {
    $default_version = \Drupal::config('dc_filter.settings')->get('api.version');
    $regex = '/\[api:(\w*[^\s|^\]])(:([\d]))?(\s([-\s\w\(\)äüöß^\]]+))?\]/i';
    $matches = $results = [];
    preg_match_all($regex, $text, $matches);
    foreach ($matches[0] as $key => $match) {
      $results[] = [
        'orig' => $matches[0][$key],
        'name' => $matches[1][$key],
        'version' => empty($matches[3][$key]) ? $default_version : trim($matches[3][$key]),
        'title' => empty($matches[4][$key]) ? $matches[1][$key] . '()' : trim($matches[4][$key]),
      ];
    }
    return $results;
  }

}
