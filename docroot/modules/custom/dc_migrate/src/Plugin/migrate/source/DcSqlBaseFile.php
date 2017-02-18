<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBase;
use Drupal\migrate\Row;

/**
 * General SQL-based source plugin for managed files.
 *
 * @MigrateSource(
 *   id = "dc_file__base"
 * )
 */
class DcSqlBaseFile extends DcSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $source_config = $this->migration->getSourceConfiguration();
    $mime_type = NULL;
    if (!empty($this->pluginDefinition['mime_type'])) {
      $mime_type = $this->pluginDefinition['mime_type'];
    }
    if (!empty($source_config['mime_type'])) {
      // Migration overrides plugin definition.
      $mime_type = $source_config['mime_type'];
    }

    $query = $this->select('files', 'f')
      ->fields('f', []);
    $query->addField('f', 'filepath', 'uri');

    if (!empty($mime_type)) {
      $query->condition('f.filemime', '%' . $this->database->escapeLike($mime_type) . '%', 'LIKE');
    }

    $this->alterQuery($query);

    return $query->distinct()->orderBy('f.fid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Define base fields for files.
    $fields = [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('File owner'),
      'uri' => $this->t('File uri'),
      'filename' => $this->t('Name of file'),
      'filepath' => $this->t('File path'),
      'directory' => $this->t('File directory'),
      'filemime' => $this->t('File MIME Type'),
      'filesize' => $this->t('Size of file'),
      'status' => $this->t('File status (permanent/temporary)'),
      'timestamp' => $this->t('File creation date'),
    ];

    $this->alterFields($fields);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Compute the filepath property, which is a physical representation of the
    // URI relative to the configured source path.
    $path = str_replace(['public:/', 'private:/', 'temporary:/'], ['public', 'private', 'temporary'], $row->getSourceProperty('uri'));
    $row->setSourceProperty('filepath', $path);
    // Extract directory.
    $row->setSourceProperty('directory', substr($path, 0, strrpos($path, '/') + 1));
    $row->setSourceProperty('filename', substr($path, strrpos($path, '/') + 1));

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'f',
      ],
    ];
  }

}
