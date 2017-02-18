<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\CommentUpload;

/**
 * Source plugin for uploads on discussion answers.
 *
 * @MigrateSource(
 *   id = "dc_upload__discussion_answers",
 *   bundle = "forum"
 * )
 */
class DiscussionAnswerUpload extends CommentUpload {

}
