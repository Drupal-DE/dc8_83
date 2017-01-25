<?php

namespace Drupal\dc_discussion\Plugin\views\filter;

use Drupal\history\Plugin\views\filter\HistoryUserTimestamp;

/**
 * Filter for new/updated discussions.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("dc_discussion_history_timestamp")
 */
class DcDiscussionHistoryTimestamp extends HistoryUserTimestamp {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This can only work if we're authenticated in.
    if (!\Drupal::currentUser()->isAuthenticated()) {
      return;
    }

    // Don't filter if we're exposed and the checkbox isn't selected.
    if ((!empty($this->options['exposed'])) && empty($this->value)) {
      return;
    }

    // Hey, Drupal kills old history, so nodes that haven't been updated
    // since HISTORY_READ_LIMIT are bzzzzzzzt outta here!

    $limit = REQUEST_TIME - HISTORY_READ_LIMIT;

    $this->ensureMyTable();
    $field = "$this->tableAlias.$this->realField";

    // NULL means a history record doesn't exist. That's clearly new content.
    // Unless it's very very old content. Everything in the query is already
    // type safe cause none of it is coming from outside here.
    $this->query->addWhereExpression($this->options['group'], "($field IS NULL AND (discussion_relation_data.changed > (***CURRENT_TIME*** - $limit))) OR $field < discussion_relation_data.changed");
  }

}
