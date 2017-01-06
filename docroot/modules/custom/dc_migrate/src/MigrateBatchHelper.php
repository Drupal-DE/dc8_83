<?php

namespace Drupal\dc_migrate;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\dc_migrate\Plugin\DcMigrateProcessorInterface;

/**
 * Provides helper methods for indexing items using Drupal's Batch API.
 */
class MigrateBatchHelper {

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected static $translationManager;

  /**
   * Gets the translation manager.
   *
   * @return \Drupal\Core\StringTranslation\TranslationInterface
   *   The translation manager.
   */
  protected static function getStringTranslation() {
    if (!static::$translationManager) {
      static::$translationManager = \Drupal::service('string_translation');
    }
    return static::$translationManager;
  }

  /**
   * Sets the translation manager.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The new translation manager.
   */
  public static function setStringTranslation(TranslationInterface $translation_manager) {
    static::$translationManager = $translation_manager;
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * @see \Drupal\Core\StringTranslation\TranslationInterface::translate()
   */
  protected static function t($string, array $args = array(), array $options = array()) {
    return static::getStringTranslation()->translate($string, $args, $options);
  }

  /**
   * Formats a string containing a count of items.
   *
   * @see \Drupal\Core\StringTranslation\TranslationInterface::formatPlural()
   */
  protected static function formatPlural($count, $singular, $plural, array $args = array(), array $options = array()) {
    return static::getStringTranslation()->formatPlural($count, $singular, $plural, $args, $options);
  }

  /**
   * Creates a migration batch.
   *
   * @param string $processor_id
   *   The custom migration processor to run.
   * @param array $options
   *   (optional) Options to control the batch size, limit, etc..
   */
  public static function create($processor_id, $options = []) {
    /* @var $manager \Drupal\dc_migrate\DcMigrateManager */
    $manager = \Drupal::service('dc_migrate.manager');
    if (($processor = $manager->load($processor_id)) === NULL) {
      throw new \Exception("Failed to load processor with ID '{$processor_id}'.");
    }

    $options += [
      'batch_size' => 500,
      'limit' => -1,
      'refresh_source_data' => FALSE,
    ];

    if ($options['batch_size'] <= 0) {
      throw new \Exception("Failed to create a batch with batch size '{$options['batch_size']}' for processor '{$processor->getPluginId()}'.");
    }
    // Define the batch definition.
    $batch_definition = [
      'operations' => [
          [[__CLASS__, 'prepare'], [$processor, $options]],
          [[__CLASS__, 'process'], [$processor, $options]],
          [[__CLASS__, 'cleanup'], [$processor, $options]],
      ],
      'finished' => [__CLASS__, 'finish'],
      'progress_message' => static::t('Completed about @percentage% of the operation (@current of @total).'),
    ];
    // Schedule the batch.
    batch_set($batch_definition);
  }

  /**
   * Batch operation for preparation tasks.
   *
   * @param \Drupal\dc_migrate\DkrMigrateProcessorInterface $processor
   *   The custom migration processor to prepare.
   * @param array $options
   *   Additional options for the batch.
   * @param array|\ArrayAccess $context
   *   The current batch context, as defined in the
   *   @link batch Batch operations @endlink documentation.
   */
  public static function prepare(DcMigrateProcessorInterface $processor, $options, &$context) {
    try {
      $context['results']['start'] = time();
      // Do preparation tasks.
      $result = $processor->prepare($options);
      if ($result) {
        $items_remaining = $processor->getRemainingItemsQuery()->countQuery()->execute()->fetchField();
        $context['message'] = static::t("Successfully ran process preparation in @time.\nItems to process: @remaining", ['@time' => \Drupal::service('date.formatter')->formatInterval(time() - $context['results']['start']), '@remaining' => $items_remaining]);
      }
    }
    catch (\Exception $ex) {
      // Log exception to watchdog and abort the batch job.
      watchdog_exception('dc_migrate', $ex);
      $context['message'] = static::t('An error occurred during preparation: @message', ['@message' => $ex->getMessage()]);
    }
    $context['finished'] = 1;
  }

  /**
   * Processes a batch operation.
   *
   * @param \Drupal\dc_migrate\DkrMigrateProcessorInterface $processor
   *   The custom migration processor to run.
   * @param array $options
   *   Additional options for the batch.
   * @param array|\ArrayAccess $context
   *   The current batch context, as defined in the
   *   @link batch Batch operations @endlink documentation.
   */
  public static function process(DcMigrateProcessorInterface $processor, $options, &$context) {
    $batch_size = $options['batch_size'];
    $limit = $options['limit'];
    if ($limit < 0) {
      $limit = $processor->getRemainingItemsQuery()->countQuery()->execute()->fetchField();
    }

    // Check if the sandbox should be initialized.
    if (!isset($context['sandbox']['limit'])) {
      // Initialize the sandbox with data which is shared among the batch runs.
      $context['sandbox']['limit'] = $limit;
      $context['sandbox']['batch_size'] = $batch_size;
      $context['sandbox']['progress'] = 0;
    }
    // Check if the results should be initialized.
    if (!isset($context['results']['processed'])) {
      // Initialize the results with data which is shared among the batch runs.
      $context['results']['processed'] = 0;
    }
    // Get the remaining item count.
    $remaining_item_count = $limit; #$processor->getRemainingItemsCount();
    // Check if an explicit limit needs to be used.
    if ($context['sandbox']['limit'] > -1) {
      // Calculate the remaining amount of items that can be processed.
      $actual_limit = min($context['sandbox']['limit'] - $context['sandbox']['progress'], $remaining_item_count);
    }
    else {
      // Use the remaining item count as actual limit.
      $actual_limit = $remaining_item_count;
    }

    // Store original count of items to be indexed to show progress properly.
    if (empty($context['sandbox']['original_item_count'])) {
      $context['sandbox']['original_item_count'] = min($remaining_item_count, $actual_limit);
    }

    // Determine the number of items to index for this run.
    $to_process = min($actual_limit, $context['sandbox']['batch_size']);
    // Catch any exception that may occur during indexing.
    try {
      $context['sandbox']['process_start'] = time();
      // Process items limited by the given count.
      $processed = $processor->process($to_process, $options);
      $context['sandbox']['process_end'] = time();
      // Increment the indexed result and progress.
      $context['results']['processed'] += $processed;
      $context['sandbox']['progress'] += $to_process;
      // Display progress message.
      if ($processed > 0) {
        $context['message'] = static::formatPlural($processed, 'Successfully processed 1 item in @time.', 'Successfully processed @count items in @time.', ['@time' => \Drupal::service('date.formatter')->formatInterval($context['sandbox']['process_end'] - $context['sandbox']['process_start'])]);
      }
      // Everything has been indexed?
      if ($processed === 0 || $context['sandbox']['progress'] >= $context['sandbox']['original_item_count']) {
        $context['finished'] = 1;
      }
      else {
        $context['finished'] = ($context['sandbox']['progress'] / $context['sandbox']['original_item_count']);
      }
    }
    catch (\Exception $ex) {
      // Log exception to watchdog and abort the batch job.
      watchdog_exception('dc_migrate', $ex);
      $context['message'] = static::t('An error occurred during processing: @message', ['@message' => $ex->getMessage()]);
      $context['finished'] = 1;
    }
  }

  /**
   * Batch operation for cleanup tasks.
   *
   * @param \Drupal\dc_migrate\DkrMigrateProcessorInterface $processor
   *   The custom migration processor to run.
   * @param array $options
   *   Additional options for the batch.
   * @param array|\ArrayAccess $context
   *   The current batch context, as defined in the
   *   @link batch Batch operations @endlink documentation.
   */
  public static function cleanup(DcMigrateProcessorInterface $processor, $options, &$context) {
    try {
      $start = time();
      // Do cleanup tasks.
      $result = $processor->cleanup($options);
      if ($result) {
        $context['message'] = static::t("Successfully ran cleanup task in @time.\nTotally remaining items: @total", ['@time' => \Drupal::service('date.formatter')->formatInterval(time() - $start), '@total' => $processor->getRemainingItemsCount()]);
      }
    }
    catch (\Exception $ex) {
      // Log exception to watchdog and abort the batch job.
      watchdog_exception('dc_migrate', $ex);
      $context['message'] = static::t('An error occurred during cleanup: @message', ['@message' => $ex->getMessage()]);
    }
    $context['finished'] = 1;
  }

  /**
   * Finishes a batch.
   */
  public static function finish($success, $results, $operations) {
    // Check if the batch job was successful.
    if (!$success) {
      // Notify user about batch job failure.
      drupal_set_message(static::t('An error occurred while trying to import items. Check the logs for details.'), 'error');
      return;
    }
    // Display the number of items processed.
    if (empty($results['processed'])) {
      // Notify user about failure to index items.
      drupal_set_message(static::t("Couldn't process items. Check the logs for details."), 'error');
      return;
    }
    // Build the message.
    $args = [
      '@time' => \Drupal::service('date.formatter')->formatInterval(time() - $results['start']),
    ];
    $message = static::formatPlural($results['processed'], 'Processed 1 item in @time.', 'Processed @count items in @time.', $args);
    // Notify user about indexed items.
    drupal_set_message($message);
  }

}
