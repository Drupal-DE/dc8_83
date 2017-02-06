<?php

namespace Drupal\dc_quote\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to create links to Drupal projects.
 *
 * @Filter(
 *   id = "dc_quote_quote",
 *   title = @Translation("DC quote"),
 *   description = @Translation("Creates quotes."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class DcFilterQuote extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    if (stristr($text, '[quote') === FALSE) {
      return new FilterProcessResult($text);
    }

    $regex = '/\[quote(=(?P<author>[^\]]*))?\](?P<quote>.*)\[\/quote\]/isU';
    $text = preg_replace_callback($regex, [$this, 'quoteReplaceTags'], $text);

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('
        <p>You can add quotes from other posts. Examples:</p>
        <ul>
          <li><code>[quote]In my opinion ...[/quote]</code> for a simple quote</li>
          <li><code>[quote=admin]...[/quote]</code> for a quote with author reference</li>
        </ul>');
    }
    return $this->t('You can add quotes by using <code>[quote][/quote]</code>.');
  }

  /**
   * Helper function to replace code-tags with proper HTML.
   *
   * @param array $matches
   *   Matches found by regular expression.
   *
   * @return string
   *   Original string with replaced tags.
   */
  public function quoteReplaceTags($matches) {
    if (!isset($matches['quote'])) {
      return $matches[0];
    }

    $build = [
      '#author' => isset($matches['author']) ? $matches['author'] : NULL,
      '#quote' => html_entity_decode($matches['quote']),
      '#theme' => 'dc_quote',
    ];

    return render($build);
  }

}
