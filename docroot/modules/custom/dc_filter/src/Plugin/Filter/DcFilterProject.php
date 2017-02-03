<?php

namespace Drupal\dc_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to create links to Drupal projects.
 *
 * @Filter(
 *   id = "dc_filter_project",
 *   title = @Translation("Drupal project"),
 *   description = @Translation("Creates links to Drupal projects."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class DcFilterProject extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if (stristr($text, '[do:') === FALSE) {
      return new FilterProcessResult($text);
    }

    foreach ($this->getMatches($text) as $key => $match) {
      $link = [
        '#name' => $match['name'],
        '#title' => $match['title'],
        '#theme' => 'dc_filter_project_link',
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
        <p>You can add links to a Drupal project. Examples:</p>
        <ul>
          <li><code>[do:views]</code> for a simple link to the Views module</li>
          <li><code>[do:views Views Module]</code> for a link with a custom title</li>
        </ul>');
    }
    return $this->t('You can add links to a Drupal project by using <code>[do:views]</code>.');
  }

  /**
   * Find all matches for the filter.
   */
  public function getMatches($text) {
    $regex = '/\[do:(\w*[^\s|^\]])(\s([\s\wäüöß^\]]+))?\]/i';
    $matches = $results = [];
    preg_match_all($regex, $text, $matches);
    foreach ($matches[0] as $key => $match) {
      $results[] = [
        'orig' => $matches[0][$key],
        'name' => $matches[1][$key],
        'title' => empty($matches[2][$key]) ? $matches[1][$key] : trim($matches[2][$key]),
      ];
    }
    return $results;
  }

}
