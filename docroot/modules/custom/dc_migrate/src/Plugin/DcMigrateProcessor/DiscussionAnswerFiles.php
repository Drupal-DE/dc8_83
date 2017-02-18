<?php

namespace Drupal\dc_migrate\Plugin\DcMigrateProcessor;

use Drupal\dc_migrate\Plugin\DcMigrateProcessor\DiscussionFiles;

/**
 * Process class to set files on discussion answers.
 *
 * @DcMigrateProcessor(
 *   id = "discussion_fields__answer_files",
 *   description = "Attach uploaded files to discussions",
 *   weight = 10
 * )
 */
class DiscussionAnswerFiles extends DiscussionFiles {

  /**
   * {@inheritdoc}
   */
  public function init() {
    parent::init();
    $this->sourceDataTableName = 'dcmigrate_source__node__discussion_answer_files';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceQuery() {
    $state = \Drupal::state()->get('dc_migrate.database');
    if (empty($state['database'])) {
      throw new Exception('No source database defined!');
    }
    $database = \Drupal::database();

    $query = $database->select('migrate_map_media__upload_discussion_answer', 'mud');
    $query->join("{$state['database']}.comment_upload", 'u', 'u.fid = mud.sourceid1');
    $query->join('migrate_map_node__discussion', 'md', 'md.sourceid1 = u.nid');
    $query->join('migrate_map_node__discussion_answers', 'mda', 'mda.sourceid1 = mud.sourceid2');
    // Join to node table for entity revision.
    $query->join('node', 'n', 'n.nid = mda.destid1');

    $query->isNotNull('mud.destid1');

    // Add fixed value.
    $query->addExpression("'discussion'", 'bundle');
    $query->addField('n', 'nid', 'entity_id');
    $query->addField('n', 'vid', 'revision_id');
    // Since old uploads didn't have a delta we have to fake it.
    $query->addExpression('u.fid', 'delta');
    $query->addField('mud', 'destid1', 'field_files_target_id');


    // Sort results.
    $query->orderBy('n.nid');

    return $query;
  }

}
