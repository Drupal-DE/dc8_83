<?php

namespace Drupal\dc_discussion\Plugin\views\field;

use Drupal\history\Plugin\views\field\HistoryUserTimestamp;
use Drupal\views\ResultRow;

/**
 * Field handler to display the marker for new/updated discussions.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dc_discussion_history_timestamp")
 */
class DcDiscussionHistoryTimestamp extends HistoryUserTimestamp {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $mark = MARK_READ;
    if (\Drupal::currentUser()->isAuthenticated()) {
      $last_read = $this->getValue($values);
      $changed = isset($values->discussion_relation_data_changed) ? $values->discussion_relation_data_changed : NULL;

      if (!$last_read && $changed > HISTORY_READ_LIMIT) {
        $mark = MARK_NEW;
      }
      elseif ($changed > $last_read && $changed > HISTORY_READ_LIMIT) {
        $mark = MARK_UPDATED;
      }
      $build = array(
        '#theme' => 'mark',
        '#status' => $mark,
      );
      return $this->renderLink(drupal_render($build), $values);
    }
  }

}
