<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlBaseFile;

/**
 * SQL-based source plugin for node images.
 *
 * @MigrateSource(
 *   id = "dc_image__base",
 *   mime_type = "image"
 * )
 */
class DcSqlBaseImage extends DcSqlBaseFile {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    // Join to tables providing title/alt/description of images.
    $query->leftJoin('field_data_field_file_image_alt_text', 'fiat', 'fm.fid = fiat.entity_id');
    $query->addField('fiat', 'field_file_image_alt_text_value', 'image_alt');
    $query->leftJoin('field_data_field_file_image_title_text', 'fitt', 'fm.fid = fitt.entity_id');
    $query->addField('fitt', 'field_file_image_title_text_value', 'image_title');
    $query->leftJoin('field_data_field_file_image_description', 'fid', 'fm.fid = fid.entity_id');
    $query->addField('fid', 'field_file_image_description_value', 'image_description');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array $fields = []) {
    $fields['image_alt'] = $this->t('Alternative text of image');
    $fields['image_title'] = $this->t('Image title');
    $fields['image_description'] = $this->t('Image description');
  }

}
