<?php

/**
 * Implements hook_drush_command().
 */
function dc_filter_drush_command() {
  $commands = [];

  // Update php-tags in code-tags to codesnippet.
  $commands['code-update-codephp'] = [
    'description' => 'Update <code><?php ?></code> to codesnippet tags.',
    'core' => ['8+'],
    'options' => [
      'batch-size' => dt('The number of items to process per batch run. Defaults to 100.'),
      'limit' => dt('Limit the number of items to process. Defaults to -1 (all).'),
    ],
    'examples' => [
      'drush code-update-codephp --limit=10' => 'Update php-tags of 10 nodes.',
    ],
  ];

  return $commands;
}

/**
 * Command callback for "dc-filter-code-update-codephp".
 */
function drush_dc_filter_code_update_codephp() {
  $batch_size = drush_get_option('batch-size', 100);
  if ($batch_size <= 0) {
    drush_print('batch-size must be greater than 0!');
    return;
  }
  $options = [
    'batch-size' => $batch_size,
    'limit' => drush_get_option('limit', -1),
  ];
  $batch_definition = [
    'init_message' => 'Update php tags',
    'operations' => [
      ['_dc_filter_code_update_codephp_process', [$options]],
    ],
    'progress_message' => 'Processed @current out of @total.',
    'finished' => '_dc_filter_batch_finished',
  ];
  batch_set($batch_definition);
  $batch = &batch_get();
  $batch['progressive'] = TRUE;
  drush_backend_batch_process();
}

/**
 * Batch operation to update <code><?php ?></code> tags.
 *
 * @param array $options
 *   Additional batch options (i.e. 'batch-size').
 * @param array $context
 *   Context information used for the batch.
 */
function _dc_filter_code_update_codephp_process($options, &$context) {
  $database = \Drupal::database();

  $query = $database->select('node__body', 'n');
  $query->fields('n', ['revision_id', 'body_value']);
  $query->orderBy('revision_id');
  $query->condition('bundle', 'discussion');
  $query->condition('body_value', '%' . $database->escapeLike('<?php') . '%', 'LIKE');

  $batch_size = $options['batch-size'];
  $limit = $options['limit'];
  if ($limit > 0 && $limit < $batch_size) {
    $batch_size = $limit;
  }
  $max = $query->countQuery()->execute()->fetchField();
  if ($limit > -1 && $limit > $max) {
    $max = $limit;
  }

  if (empty($context['sandbox']['max'])) {
    $context['finished'] = 0;
    $context['sandbox']['max'] = $max;
    $context['sandbox']['progress'] = 0;
    $context['message'] = 'Update code: php ...';
    $context['results']['updated'] = 0;
    $context['results']['failures'] = 0;
    $context['results']['processed'] = 0;
  }

  // Limit resultset.
  $query->range($context['results']['processed'], $batch_size);

  $results = $query->execute()->fetchAllAssoc('revision_id');

  foreach ($results as $row) {
    $count = 0;

    $value = preg_replace_callback('/(<code>\s*\n*\s*<\?php)(?P<content>.*)(\?>\s*\n*\s*<\/code>)/Us', '_dc_filter_code_replace_codephp', $row->body_value, -1, $count);
    $needs_update = ($count > 0);
    $value = preg_replace_callback('/(<\?php)(?P<content>.*)(\?>)/', '_dc_filter_code_replace_php', $value, -1, $count);
    $needs_update |= ($count > 0);

    if ($needs_update) {
      $database->update('node__body')
        ->fields([
          'body_value' => $value,
        ])
        ->condition('revision_id', $row->revision_id)
        ->execute();
      $database->update('node_revision__body')
        ->fields([
          'body_value' => $value,
        ])
        ->condition('revision_id', $row->revision_id)
        ->execute();
      $context['results']['updated'] ++;
    }

    $context['sandbox']['progress'] ++;
    $context['results']['processed'] ++;
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
  $context['message'] = sprintf('Processed %d out of %d items', $context['results']['processed'], $context['sandbox']['max']);
}

/**
 * Finish callback for batch operation.
 *
 * @param type $success
 * @param type $results
 * @param type $operations
 */
function _dc_filter_batch_finished($success, $results, $operations) {
  if ($success) {
    $message = sprintf('Processed %d and updated %d items.', $results['processed'], $results['updated']);
  }
  else {
    $message = t('Finished with an error.');
  }

  drush_print($message);
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
function _dc_filter_code_replace_codephp($matches) {
  if (!isset($matches['content'])) {
    return $matches[0];
  }

  return '<pre><code>&lt;?php' . $matches['content'] . ' ?&gt;</code></pre>';
}

/**
 * Helper function to replace php-tags with proper HTML.
 *
 * @param array $matches
 *   Matches found by regular expression.
 *
 * @return string
 *   Original string with replaced tags.
 */
function _dc_filter_code_replace_php($matches) {
  if (!isset($matches['content'])) {
    return $matches[0];
  }

  return '<code>&lt;?php' . $matches['content'] . ' ?&gt;</code>';
}
