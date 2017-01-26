<?php

namespace Drupal\dc_discussion;

/**
 * Interface for discussion_information service.
 */
interface DiscussionInformationInterface {

  /**
   * Get list of unread discussions a user participated in.
   *
   * @param int $uid
   *   User ID.
   * @param int $limit
   *   Number of items to return.
   * @param string $sort
   *   Whether to sort the results by oldest first or newest first ('DESC').
   *
   * @return array
   *   Array of query results with updated discussions the given user
   *   participated in.
   */
  public function getUnreadForUser($uid = NULL, $limit = 10, $sort = 'DESC');

}
